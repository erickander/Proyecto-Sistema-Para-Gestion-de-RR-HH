<?php

namespace App\Services;

use App\Exceptions\AiAnalysisException;
use App\Models\AnalisisIa;
use App\Models\Postulacion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser as PdfParser;

class AiCvAnalyzer
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
            'observaciones' => 'Analisis pendiente: el microservicio IA no devolvio una respuesta valida.',
        ];

        $result = $this->callAnalyzerService($postulacion, $cv);

        if (! $result) {
            throw new AiAnalysisException($fallback['observaciones']);
        }

        if (! empty($result['_error'])) {
            throw new AiAnalysisException((string) ($result['observaciones'] ?? $fallback['observaciones']));
        }

        $analysis = AnalisisIa::updateOrCreate(
            ['id_postulacion' => $postulacion->id_postulacion],
            [
                'modelo_utilizado' => $result['modelo_utilizado'] ?? 'ai-analyzer-service',
                'version_modelo' => 'microservice-http',
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

    private function callAnalyzerService(Postulacion $postulacion, UploadedFile $cv): ?array
    {
        $phpTimeLimit = max(30, (int) config('services.ai_analyzer.php_time_limit', 120));
        $timeout = max(10, (int) config('services.ai_analyzer.timeout', 45));
        $connectTimeout = max(3, min((int) config('services.ai_analyzer.connect_timeout', 10), $timeout));
        $serviceUrl = rtrim((string) config('services.ai_analyzer.url'), '/');

        if (! @set_time_limit($phpTimeLimit)) {
            $timeout = min($timeout, 25);
            $connectTimeout = min($connectTimeout, 8);
        }

        if (! $serviceUrl) {
            return $this->errorResult('No existe AI_ANALYZER_URL configurada.');
        }

        $cvText = $this->extractCvText($cv);
        $postulacion->loadMissing('vacante', 'respuestasTest.pregunta');

        $payload = [
            'vacante' => [
                'titulo' => $postulacion->vacante?->titulo,
                'descripcion' => $postulacion->vacante?->descripcion,
                'requisitos' => $postulacion->vacante?->requisitos,
            ],
            'cv_text' => $cvText,
            'test' => $this->testPayload($postulacion),
        ];

        try {
            $client = Http::timeout($timeout)
                ->connectTimeout($connectTimeout)
                ->acceptJson()
                ->asJson();

            if ($token = config('services.ai_analyzer.token')) {
                $client = $client->withToken($token);
            }

            $response = $client->post($serviceUrl.'/analyze', $payload);
        } catch (\Throwable $exception) {
            return $this->errorResult('No se pudo conectar con el microservicio IA. Diagnostico PHP/cURL: '.$this->safeError($exception));
        }

        if (! $response->successful()) {
            return $this->errorResult('Microservicio IA rechazo la solicitud: HTTP '.$response->status().' - '.Str::limit($response->body(), 360));
        }

        $decoded = $response->json();

        return is_array($decoded) ? $decoded : null;
    }

    private function extractCvText(UploadedFile $cv): string
    {
        try {
            $text = (new PdfParser())->parseFile($cv->getRealPath())->getText();
        } catch (\Throwable) {
            throw new AiAnalysisException('No se pudo leer el texto del PDF del CV. Verifique que el archivo no este danado o escaneado como imagen.');
        }

        $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');

        if ($text === '') {
            throw new AiAnalysisException('No se pudo extraer texto del CV PDF. Si el CV es escaneado, conviertalo con OCR antes de analizarlo.');
        }

        return Str::limit($text, (int) config('services.ai_analyzer.max_cv_chars', 18000), '');
    }

    private function errorResult(string $message): array
    {
        return [
            '_error' => true,
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
            'observaciones' => $message,
        ];
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
                'es_correcta' => (bool) $respuesta->es_correcta,
                'puntaje_obtenido' => (float) ($respuesta->puntaje_test ?? 0),
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
        $message = preg_replace('/Bearer\s+[A-Za-z0-9_\-.]+/', 'Bearer [REDACTED]', $exception->getMessage());
        $message = str_replace((string) config('services.ai_analyzer.token'), '[REDACTED]', $message);

        return Str::limit($exception::class.' - '.$message, 260);
    }
}
