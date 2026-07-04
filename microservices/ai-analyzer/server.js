import { createServer } from 'node:http';
import { existsSync, readFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const serviceDir = dirname(fileURLToPath(import.meta.url));
const rootDir = resolve(serviceDir, '../..');

loadEnv(resolve(rootDir, '.env'));
loadEnv(resolve(serviceDir, '.env'), true);

const port = numberEnv('AI_ANALYZER_PORT', 4101);
const host = process.env.AI_ANALYZER_HOST || '127.0.0.1';
const authToken = process.env.AI_ANALYZER_TOKEN || process.env.AI_SERVICE_TOKEN || '';
const groqApiKey = process.env.GROQ_API_KEY || '';
const groqModel = process.env.GROQ_MODEL || 'llama-3.3-70b-versatile';
const groqTimeoutMs = numberEnv('GROQ_TIMEOUT_MS', numberEnv('GROQ_TIMEOUT', 90) * 1000);
const maxCompletionTokens = numberEnv('GROQ_MAX_COMPLETION_TOKENS', 1800);
const maxBodyBytes = numberEnv('AI_ANALYZER_MAX_BODY_BYTES', 900_000);

const server = createServer(async (req, res) => {
    try {
        if (req.method === 'GET' && req.url === '/health') {
            return sendJson(res, 200, {
                status: 'ok',
                service: 'ai-analyzer',
                provider: 'groq',
                model: groqModel,
            });
        }

        if (req.method === 'POST' && req.url === '/analyze') {
            if (! authorized(req)) {
                return sendJson(res, 401, { message: 'No autorizado.' });
            }

            const payload = await readJson(req);
            const result = await analyzeCandidate(payload);

            return sendJson(res, 200, result);
        }

        return sendJson(res, 404, { message: 'Ruta no encontrada.' });
    } catch (error) {
        const status = Number.isInteger(error.status) ? error.status : 500;

        return sendJson(res, status, {
            message: safeError(error),
        });
    }
});

server.listen(port, host, () => {
    console.log(`AI analyzer microservice listening on http://${host}:${port}`);
});

async function analyzeCandidate(payload) {
    if (!groqApiKey) {
        throw httpError(500, 'No existe GROQ_API_KEY configurada para el microservicio IA.');
    }

    const cvText = String(payload?.cv_text || '').trim();

    if (!cvText) {
        throw httpError(422, 'El texto del CV es obligatorio para analizar la postulacion.');
    }

    const vacancy = payload?.vacante || {};
    const test = Array.isArray(payload?.test) ? payload.test : [];
    const prompt = buildPrompt({ vacancy, cvText, test });
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), groqTimeoutMs);

    try {
        const response = await fetch('https://api.groq.com/openai/v1/chat/completions', {
            method: 'POST',
            signal: controller.signal,
            headers: {
                Authorization: `Bearer ${groqApiKey}`,
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify({
                model: groqModel,
                messages: [
                    {
                        role: 'system',
                        content: 'Eres un analista senior de Recursos Humanos. Responde solo JSON valido, sin markdown ni texto adicional.',
                    },
                    {
                        role: 'user',
                        content: prompt,
                    },
                ],
                temperature: 0.2,
                max_completion_tokens: maxCompletionTokens,
                response_format: { type: 'json_object' },
            }),
        });

        const rawBody = await response.text();

        if (!response.ok) {
            throw httpError(502, `Groq rechazo la solicitud: HTTP ${response.status} - ${limit(rawBody, 360)}`);
        }

        const body = JSON.parse(rawBody);
        const content = body?.choices?.[0]?.message?.content;

        if (typeof content !== 'string' || !content.trim()) {
            throw httpError(502, 'Groq no devolvio contenido para analizar.');
        }

        const analysis = JSON.parse(cleanJson(content));

        return normalizeAnalysis(analysis);
    } catch (error) {
        if (error?.name === 'AbortError') {
            throw httpError(504, 'Tiempo de espera agotado al conectar con Groq.');
        }

        throw error;
    } finally {
        clearTimeout(timeout);
    }
}

function buildPrompt({ vacancy, cvText, test }) {
    const testJson = JSON.stringify(test);

    return `Analiza la siguiente hoja de vida y responde unicamente en formato JSON valido.

Debes evaluar al candidato profesionalmente para un sistema de Recursos Humanos.

Vacante postulada: "${vacancy?.titulo || ''}"
Descripcion de la vacante: ${vacancy?.descripcion || ''}
Requisitos de la vacante: ${vacancy?.requisitos || ''}

Texto extraido del CV PDF:
${cvText}

Test aplicado al postulante en JSON compacto:
${testJson}

Analiza:
- Habilidades tecnicas.
- Estudios realizados.
- Experiencia laboral.
- Calidad, coherencia y pertinencia de las respuestas del test.
- Fortalezas del candidato.
- Debilidades del candidato.
- Nivel de compatibilidad laboral.
- Puntaje del CV de 0 a 100.
- Puntaje del test de 0 a 100.
- Puntaje final de 0 a 100 combinando CV y test segun la vacante.
- Recomendacion final.

La recomendacion solo puede ser exactamente una de estas:
"Muy recomendado"
"Recomendado"
"Considerar"
"No recomendado"

Reglas de evaluacion:
- Si un dato no esta escrito literalmente pero puede inferirse razonablemente del CV, indicalo con lenguaje profesional.
- Si no existe evidencia suficiente, usa un arreglo vacio para esa categoria.
- Si no hay test, puntaje_test debe ser 0 y el puntaje final debe basarse solo en el CV.
- Si hay test, evalua cada respuesta y devuelve su puntaje segun el id_pregunta recibido.
- No inventes titulos, empresas ni fechas que no aparezcan o no puedan inferirse del CV.
- La observacion final debe explicar por que el candidato es o no apto para la vacante postulada.

Devuelve EXACTAMENTE esta estructura JSON:

{
  "puntaje_cv": 0,
  "puntaje_test": 0,
  "puntaje_final": 0,
  "habilidades": [],
  "estudios": [],
  "experiencia": [],
  "fortalezas": [],
  "debilidades": [],
  "compatibilidad": "",
  "analisis_test": "",
  "recomendacion": "",
  "observaciones": "",
  "respuestas_test": [
    {
      "id_pregunta": 0,
      "puntaje": 0,
      "observacion": ""
    }
  ]
}`;
}

function normalizeAnalysis(analysis) {
    const recommendations = ['Muy recomendado', 'Recomendado', 'Considerar', 'No recomendado'];
    const recommendation = recommendations.includes(analysis?.recomendacion) ? analysis.recomendacion : 'Considerar';

    return {
        puntaje_cv: score(analysis?.puntaje_cv),
        puntaje_test: score(analysis?.puntaje_test),
        puntaje_final: score(analysis?.puntaje_final ?? analysis?.puntaje),
        habilidades: asArray(analysis?.habilidades),
        estudios: asArray(analysis?.estudios),
        experiencia: asArray(analysis?.experiencia),
        fortalezas: asArray(analysis?.fortalezas),
        debilidades: asArray(analysis?.debilidades),
        compatibilidad: analysis?.compatibilidad || '',
        analisis_test: analysis?.analisis_test || '',
        recomendacion: recommendation,
        observaciones: analysis?.observaciones || '',
        respuestas_test: Array.isArray(analysis?.respuestas_test)
            ? analysis.respuestas_test.map((answer) => ({
                id_pregunta: Number(answer?.id_pregunta || 0),
                puntaje: score(answer?.puntaje),
                observacion: String(answer?.observacion || ''),
            }))
            : [],
        proveedor_ia: 'groq',
        modelo_utilizado: groqModel,
    };
}

function authorized(req) {
    if (!authToken) {
        return true;
    }

    return req.headers.authorization === `Bearer ${authToken}`;
}

function readJson(req) {
    return new Promise((resolvePromise, reject) => {
        let received = 0;
        const chunks = [];

        req.on('data', (chunk) => {
            received += chunk.length;

            if (received > maxBodyBytes) {
                req.destroy();
                reject(httpError(413, 'La solicitud supera el limite permitido.'));
                return;
            }

            chunks.push(chunk);
        });

        req.on('end', () => {
            try {
                const raw = Buffer.concat(chunks).toString('utf8') || '{}';
                resolvePromise(JSON.parse(raw));
            } catch {
                reject(httpError(400, 'El cuerpo de la solicitud debe ser JSON valido.'));
            }
        });

        req.on('error', reject);
    });
}

function sendJson(res, status, payload) {
    const body = JSON.stringify(payload);
    res.writeHead(status, {
        'Content-Type': 'application/json; charset=utf-8',
        'Content-Length': Buffer.byteLength(body),
    });
    res.end(body);
}

function loadEnv(path, override = false) {
    if (!existsSync(path)) {
        return;
    }

    const content = readFileSync(path, 'utf8');

    for (const line of content.split(/\r?\n/)) {
        const trimmed = line.trim();

        if (!trimmed || trimmed.startsWith('#') || !trimmed.includes('=')) {
            continue;
        }

        const [key, ...valueParts] = trimmed.split('=');
        const name = key.trim();
        let value = valueParts.join('=').trim();

        if ((value.startsWith('"') && value.endsWith('"')) || (value.startsWith("'") && value.endsWith("'"))) {
            value = value.slice(1, -1);
        }

        if (override || process.env[name] === undefined) {
            process.env[name] = value;
        }
    }
}

function numberEnv(name, fallback) {
    const value = Number(process.env[name]);

    return Number.isFinite(value) && value > 0 ? value : fallback;
}

function score(value) {
    const numeric = Number(value);

    if (!Number.isFinite(numeric)) {
        return 0;
    }

    return Math.max(0, Math.min(100, numeric));
}

function asArray(value) {
    if (Array.isArray(value)) {
        return value.map((item) => String(item)).filter(Boolean);
    }

    if (typeof value === 'string' && value.trim()) {
        return [value.trim()];
    }

    return [];
}

function cleanJson(text) {
    return text.replace(/```json/gi, '').replace(/```/g, '').trim();
}

function safeError(error) {
    return limit(String(error?.message || 'Error interno del microservicio IA.').replace(groqApiKey, '[REDACTED]'), 500);
}

function limit(text, max) {
    return text.length > max ? `${text.slice(0, max)}...` : text;
}

function httpError(status, message) {
    const error = new Error(message);
    error.status = status;

    return error;
}
