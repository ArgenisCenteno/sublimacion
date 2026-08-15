<?php

namespace App\Services\Invoices;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use thiagoalessio\TesseractOCR\TesseractOCR;

class InvoiceExtractor
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
    }

    public function extract(string $filePath): array
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($extension === 'pdf') {
            $text = $this->extractTextFromPdf($filePath);
        } else {
            $text = (new TesseractOCR($filePath))
                ->lang('spa')
                ->run();
        }

        $text = $this->sanitizeEncoding($text);

        if (empty(trim($text))) {
            Log::error('InvoiceExtractor: no se pudo extraer texto de ' . $filePath);
            return ['raw_text' => ''];
        }

        $prompt = "Extrae los datos de esta factura:\n" . $text . "\nDevuelve UNICAMENTE JSON valido con esta estructura, sin texto adicional:\n{\"invoice_number\":\"\",\"total_amount\":0.0,\"issued_at\":\"YYYY-MM-DD\",\"raw_text\":\"\",\"subtotal_amount\":0.0,\"vat_amount\":0.0,\"items\":[{\"description\":\"\",\"quantity\":0,\"unit_price\":0.0,\"total_price\":0.0}]}";

        $response = Http::withToken($this->apiKey)
            ->post('https://api.openai.com/v1/responses', [
                'model' => 'gpt-4.1-mini',
                'input' => $prompt,
            ]);

        if ($response->failed()) {
            Log::error('OpenAI API failed: ' . $response->body());
            return ['raw_text' => $text];
        }

        $result     = $response->json();
        $outputText = $result['output'][0]['content'][0]['text'] ?? '';
        $clean      = trim(preg_replace('/```json|```/', '', $outputText));
        $json       = json_decode($clean, true);

        if ($json === null) {
            Log::error('InvoiceExtractor: JSON decode fallo. Respuesta: ' . $clean);
            return ['raw_text' => $text];
        }

        return $this->sanitizeArrayEncoding($json);
    }

    private function extractTextFromPdf(string $filePath): string
    {
        $outputFile    = tempnam(sys_get_temp_dir(), 'pdf_') . '.txt';
        $escapedInput  = escapeshellarg($filePath);
        $escapedOutput = escapeshellarg($outputFile);

        exec("pdftotext $escapedInput $escapedOutput 2>&1", $res, $code);

        if ($code !== 0) {
            Log::error('pdftotext failed: ' . implode("\n", $res));
            return $this->extractTextFromPdfWithOcr($filePath);
        }

        $text = file_get_contents($outputFile);
        @unlink($outputFile);

        return $text ?: '';
    }

    private function extractTextFromPdfWithOcr(string $filePath): string
    {
        $tmpBase  = tempnam(sys_get_temp_dir(), 'ocr_pdf_');
        $tmpImage = $tmpBase . '.png';

        exec("pdftoppm -png -r 300 -singlefile " . escapeshellarg($filePath) . " " . escapeshellarg($tmpBase) . " 2>&1", $out, $code);

        if ($code !== 0 || !file_exists($tmpImage)) {
            Log::error('pdftoppm failed: ' . implode("\n", $out));
            return '';
        }

        try {
            $text = (new TesseractOCR($tmpImage))->lang('spa')->run();
        } finally {
            @unlink($tmpImage);
            @unlink($tmpBase);
        }

        return $text ?? '';
    }

    private function sanitizeEncoding(string $text): string
    {
        if (mb_check_encoding($text, 'UTF-8')) {
            return $text;
        }

        $converted = mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1');

        if (mb_check_encoding($converted, 'UTF-8')) {
            return $converted;
        }

        return mb_convert_encoding($text, 'UTF-8', 'UTF-8');
    }

    private function sanitizeArrayEncoding(array $data): array
    {
        array_walk_recursive($data, function (&$value) {
            if (is_string($value)) {
                $value = $this->sanitizeEncoding($value);
            }
        });

        return $data;
    }

    private function parseInvoiceText(string $text): array
    {
        $lines = explode("\n", $text);

        $data = [
            'invoice_number' => null,
            'issued_at'      => null,
            'total_amount'   => null,
            'items'          => [],
            'raw_text'       => $text,
        ];

        foreach ($lines as $line) {
            $line = trim($line);

            if (preg_match('/FEC\s*(\d+)/i', $line, $m)) {
                $data['invoice_number'] = $m[1];
            }

            if (preg_match('/(\d{2}\/\d{2}\/\d{4})/', $line, $m)) {
                $data['issued_at'] = $m[1];
            }

            if (preg_match('/Total\s*(\d+[\.,]?\d*)\xe2\x82\xac/i', $line, $m)) {
                $data['total_amount'] = floatval(str_replace(',', '.', $m[1]));
            }

            if (preg_match('/(.+)\s+(\d+)\s+[\w\.]*\s+(\d+[\.,]?\d*)\xe2\x82\xac/i', $line, $m)) {
                $data['items'][] = [
                    'description' => trim($m[1]),
                    'quantity'    => intval($m[2]),
                    'unit_price'  => floatval(str_replace(',', '.', $m[3])),
                ];
            }
        }

        return $data;
    }
}