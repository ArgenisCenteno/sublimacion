<?php

namespace App\Services\Operations;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class PlateRecognitionService
{
    public function recognizePlate(UploadedFile $image): array
    {
        $rawText = $this->requestVisionJson(
            $image,
            'Extrae unicamente la matricula de la imagen del vehiculo. Devuelve JSON estricto con las claves plate y confidence. plate debe ser texto plano sin espacios ni guiones. confidence debe ser un numero entre 0 y 1. Si no puedes leerla, devuelve plate vacio y confidence 0.'
        );

        $jsonText = $this->extractJsonObject($rawText);
        $decoded = $jsonText ? json_decode($jsonText, true) : null;

        if (! is_array($decoded)) {
            return [
                'plate' => $this->normalizePlate($rawText),
                'confidence' => 0.0,
                'raw' => $rawText,
            ];
        }

        return [
            'plate' => $this->normalizePlate((string) ($decoded['plate'] ?? '')),
            'confidence' => (float) ($decoded['confidence'] ?? 0),
            'raw' => $rawText,
        ];
    }

    public function suggestVehicleData(UploadedFile $image, ?string $fallbackPlate = null): array
    {
        try {
            $rawText = $this->requestVisionJson(
                $image,
                'Analiza la foto del vehiculo y devuelve JSON estricto con las claves plate, brand, model, version, color y confidence. plate debe ir sin espacios ni guiones. Si no puedes inferir un campo, devuelvelo vacio. confidence debe ser un numero entre 0 y 1. No inventes informacion si la imagen no la muestra con claridad.'
            );
        } catch (Throwable) {
            return [
                'plate' => $this->normalizePlate((string) $fallbackPlate),
                'brand' => '',
                'model' => '',
                'version' => '',
                'color' => '',
                'confidence' => 0.0,
            ];
        }

        $jsonText = $this->extractJsonObject($rawText);
        $decoded = $jsonText ? json_decode($jsonText, true) : null;

        if (! is_array($decoded)) {
            return [
                'plate' => $this->normalizePlate((string) $fallbackPlate),
                'brand' => '',
                'model' => '',
                'version' => '',
                'color' => '',
                'confidence' => 0.0,
            ];
        }

        $normalizedPlate = $this->normalizePlate((string) ($decoded['plate'] ?? ''));

        return [
            'plate' => $normalizedPlate !== '' ? $normalizedPlate : $this->normalizePlate((string) $fallbackPlate),
            'brand' => trim((string) ($decoded['brand'] ?? '')),
            'model' => trim((string) ($decoded['model'] ?? '')),
            'version' => trim((string) ($decoded['version'] ?? '')),
            'color' => trim((string) ($decoded['color'] ?? '')),
            'confidence' => (float) ($decoded['confidence'] ?? 0),
        ];
    }

    private function requestVisionJson(UploadedFile $image, string $prompt): string
    {
        $apiKey = config('services.openai.api_key');

        if (! $apiKey) {
            throw new RuntimeException('OpenAI API key is not configured.');
        }

        $base64Image = base64_encode(file_get_contents($image->getRealPath()));
        $mimeType = $image->getMimeType() ?: 'image/jpeg';
        $dataUrl = sprintf('data:%s;base64,%s', $mimeType, $base64Image);

        $response = Http::baseUrl(rtrim((string) config('services.openai.base_url'), '/'))
            ->withToken($apiKey)
            ->acceptJson()
            ->timeout(60)
            ->post('/responses', [
                'model' => config('services.openai.ocr_model', 'gpt-4.1-mini'),
                'input' => [[
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'input_text',
                            'text' => $prompt,
                        ],
                        [
                            'type' => 'input_image',
                            'image_url' => $dataUrl,
                        ],
                    ],
                ]],
            ]);

        $response->throw();

        $payload = $response->json();

        return (string) (data_get($payload, 'output_text')
            ?? data_get($payload, 'output.0.content.0.text')
            ?? '');
    }

    private function extractJsonObject(string $text): ?string
    {
        if (preg_match('/\{.*\}/s', $text, $matches) !== 1) {
            return null;
        }

        return $matches[0];
    }

    private function normalizePlate(string $plate): string
    {
        return strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $plate));
    }
}
