<?php

namespace App\Services;

use App\Models\AnalisisIa;
use App\Models\Postulacion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GeminiCvAnalyzer
{
    public function analyze(Postulacion $postulacion, UploadedFile $cv): AnalisisIa
    {
        $fallback = [
            'habilidades' => [],
            'estudios' => [],
            'experiencia' => [],
            'fortalezas' => [],
            'debilidades' => [],
            'compatibilidad' => 'No evaluada',
            'puntaje_cv' => 0,
            'puntaje_test' => 0,
            'puntaje_final' => 0,
            'recomendacion' => 'Considerar',
            'analisis_test' => 'Sin analisis de test.',
            'observaciones' => 'Analisis pendiente: Gemini no devolvio una respuesta valida. Revise la API key, conectividad o formato del PDF.',
        ];

        $result = $this->callGemini($postulacion, $cv) ?? $fallback;

        $analysis = AnalisisIa::updateOrCreate(
            ['id_postulacion' => $postulacion->id_postulacion],
            [
                'modelo_utilizado' => config('services.gemini.model'),
                'version_modelo' => 'v1beta',
                'puntuacion_general' => $this->score($result, 'puntaje_final', $result['puntaje'] ?? 0),
                'puntaje_cv' => $this->score($result, 'puntaje_cv'),
                'puntaje_test' => $this->score($result, 'puntaje_test'),
                'compatibilidad' => $this->compatibilityScore($result['compatibilidad'] ?? null),
                'experiencia_score' => 0,
                'habilidades_score' => 0,
                'educacion_score' => 0,
                'idiomas_score' => 0,
                'habilidades_detectadas' => $this->text($result, 'habilidades'),
                'estudios_detectados' => $this->text($result, 'estudios'),
                'experiencia_detectada' => $this->text($result, 'experiencia'),
                'fortalezas' => $this->text($result, 'fortalezas'),
                'debilidades' => $this->text($result, 'debilidades'),
                'recomendacion' => $this->recommendation($result['recomendacion'] ?? 'Considerar'),
                'analisis_test' => $this->text($result, 'analisis_test'),
                'observaciones' => $this->text($result, 'observaciones'),
                'resultado_json' => json_encode($result, JSON_UNESCAPED_UNICODE),
                'fecha_analisis' => now(),
            ]
        );

        $this->updateTestResponses($postulacion, $result);

        return $analysis;
    }

    private function callGemini(Postulacion $postulacion, UploadedFile $cv): ?array
    {
        $key = config('services.gemini.key');

        if (! $key) {
            return ['observaciones' => 'No existe GEMINI_API_KEY configurada.'] + [
                'habilidades' => [], 'estudios' => [], 'experiencia' => [], 'fortalezas' => [], 'debilidades' => [],
                'compatibilidad' => 'No evaluada', 'puntaje_cv' => 0, 'puntaje_test' => 0, 'puntaje_final' => 0,
                'recomendacion' => 'Considerar', 'analisis_test' => 'Sin analisis de test.',
            ];
        }

        $vacante = $postulacion->vacante;
        $testJson = json_encode($this->testPayload($postulacion), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $prompt = <<<PROMPT
Analiza la siguiente hoja de vida y responde unicamente en formato JSON valido.

Debes evaluar al candidato profesionalmente para un sistema de Recursos Humanos.

Vacante postulada: "{$vacante?->titulo}"
Descripcion de la vacante: {$vacante?->descripcion}
Requisitos de la vacante: {$vacante?->requisitos}

Test aplicado al postulante en JSON compacto:
{$testJson}

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

Si un dato no esta escrito literalmente pero puede inferirse razonablemente del CV, indicalo con lenguaje profesional.
Si no existe evidencia suficiente, usa un arreglo vacio para esa categoria.
Si no hay test, puntaje_test debe ser 0 y el puntaje final debe basarse solo en el CV.
Si hay test, evalua cada respuesta y devuelve su puntaje segun el id_pregunta recibido.

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
}
PROMPT;

        $model = config('services.gemini.model', 'gemini-1.5-flash');
        $caBundle = base_path(config('services.gemini.ca_bundle', 'certs/cacert.pem'));

        try {
            $response = Http::timeout(60)
                ->connectTimeout(20)
                ->retry(2, 800)
                ->withOptions([
                    'verify' => file_exists($caBundle) ? $caBundle : true,
                    'curl' => [
                        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                    ],
                ])
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}", [
                    'contents' => [[
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => 'application/pdf',
                                    'data' => base64_encode(file_get_contents($cv->getRealPath())),
                                ],
                            ],
                        ],
                    ]],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'response_mime_type' => 'application/json',
                    ],
                ]);
        } catch (\Throwable $exception) {
            return [
                'habilidades' => [],
                'estudios' => [],
                'experiencia' => [],
                'fortalezas' => [],
                'debilidades' => [],
                'compatibilidad' => 'No evaluada',
                'puntaje_cv' => 0,
                'puntaje_test' => 0,
                'puntaje_final' => 0,
                'recomendacion' => 'Considerar',
                'analisis_test' => 'Sin analisis de test.',
                'observaciones' => 'Error conectando con Gemini desde PHP/cURL. Diagnostico: '.$this->safeError($exception),
            ];
        }

        if (! $response->successful()) {
            return [
                'habilidades' => [],
                'estudios' => [],
                'experiencia' => [],
                'fortalezas' => [],
                'debilidades' => [],
                'compatibilidad' => 'No evaluada',
                'puntaje_cv' => 0,
                'puntaje_test' => 0,
                'puntaje_final' => 0,
                'recomendacion' => 'Considerar',
                'analisis_test' => 'Sin analisis de test.',
                'observaciones' => 'Gemini rechazo la solicitud: HTTP '.$response->status().' - '.Str::limit($response->body(), 300),
            ];
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');

        if (! is_string($text)) {
            return null;
        }

        $decoded = json_decode($this->cleanJson($text), true);

        return is_array($decoded) ? $decoded : null;
    }

    private function cleanJson(string $text): string
    {
        return trim(Str::of($text)->replace('```json', '')->replace('```', '')->toString());
    }

    private function score(array $result, string $key, mixed $default = 0): float
    {
        return max(0, min(100, (float) ($result[$key] ?? $default)));
    }

    private function text(array $result, string $key): string
    {
        $value = $result[$key] ?? '';

        return is_array($value) ? implode(', ', $value) : (string) $value;
    }

    private function recommendation(string $value): string
    {
        $value = trim($value);

        return in_array($value, ['Muy recomendado', 'Recomendado', 'Considerar', 'No recomendado'], true) ? $value : 'Considerar';
    }

    private function compatibilityScore(mixed $value): float
    {
        if (is_numeric($value)) {
            return max(0, min(100, (float) $value));
        }

        $value = strtolower((string) $value);

        return match (true) {
            str_contains($value, 'muy') || str_contains($value, 'alta') => 90,
            str_contains($value, 'media') || str_contains($value, 'moderada') => 60,
            str_contains($value, 'baja') => 30,
            default => 0,
        };
    }

    private function testPayload(Postulacion $postulacion): array
    {
        $postulacion->loadMissing('respuestasTest.pregunta');

        return $postulacion->respuestasTest
            ->map(fn ($respuesta) => [
                'id_pregunta' => $respuesta->id_pregunta,
                'pregunta' => $respuesta->pregunta?->pregunta,
                'tipo' => $respuesta->pregunta?->tipo,
                'puntaje_maximo' => (float) ($respuesta->pregunta?->puntaje_maximo ?? 100),
                'respuesta' => $respuesta->respuesta,
            ])
            ->values()
            ->all();
    }

    private function updateTestResponses(Postulacion $postulacion, array $result): void
    {
        $evaluations = $result['respuestas_test'] ?? [];

        if (! is_array($evaluations)) {
            return;
        }

        foreach ($evaluations as $evaluation) {
            if (! is_array($evaluation) || empty($evaluation['id_pregunta'])) {
                continue;
            }

            $postulacion->respuestasTest()
                ->where('id_pregunta', $evaluation['id_pregunta'])
                ->update([
                    'puntaje_ia' => $this->score($evaluation, 'puntaje'),
                    'observacion_ia' => Str::limit((string) ($evaluation['observacion'] ?? ''), 65000),
                ]);
        }
    }

    private function safeError(\Throwable $exception): string
    {
        $message = preg_replace('/key=([^&\s]+)/', 'key=[REDACTED]', $exception->getMessage());
        $message = str_replace((string) config('services.gemini.key'), '[REDACTED]', $message);

        return Str::limit($exception::class.' - '.$message, 260);
    }
}
