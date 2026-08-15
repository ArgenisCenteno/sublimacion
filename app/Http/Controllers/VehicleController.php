<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Company;
use App\Models\Provider;
use App\Models\Vehicle;
use App\Models\VehicleDocument;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VehicleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:vehicles.view')->only(['index', 'show']);
        $this->middleware('can:vehicles.create')->only(['create', 'store']);
        $this->middleware('can:vehicles.edit')->only(['edit', 'update', 'storeDocument']);
        $this->middleware('can:vehicles.delete')->only(['destroy']);
        $this->middleware('can:vehicles.import')->only(['import']);
        $this->middleware('can:vehicles.view')->only(['downloadDocument']);
    }
    private const IMPORT_COLUMN_MAP = [
        0 => 'brand',
        1 => 'model',
        2 => 'version',
        3 => 'plate',
        4 => 'raw_location',
        5 => 'raw_physical_status',
        6 => 'commercial_status',
        7 => 'registration_date',
        8 => 'mileage_km',
        9 => 'color',
        10 => 'taxable_base',
        11 => 'pvi',
        12 => 'price_financed',
        13 => 'price_cash',
        14 => 'company_name',
        15 => 'provider_name',
    ];
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Vehicle::with(['company', 'provider']);

        // Filtro de busqueda
        if ($request->has('search') && !empty($request->search)) {
            $search = trim((string) $request->search);
            $normalizedIdentifierSearch = Str::upper($search);
            $query->where(function ($q) use ($search, $normalizedIdentifierSearch) {
                $q->where('plate', 'like', "%{$normalizedIdentifierSearch}%")
                    ->orWhere('vin', 'like', "%{$normalizedIdentifierSearch}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('version', 'like', "%{$search}%")
                    ->orWhere('color', 'like', "%{$search}%");
            });
        }

        // filtrar por status fisico
        if ($request->filled('physical_status')) {
            $query->where('physical_status', $request->string('physical_status')->toString());
        }

        $vehicles = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends($request->query());

        return Inertia::render('Vehicles/Index', [
            'vehicles' => $vehicles,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'physical_status' => $request->string('physical_status')->toString(),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Vehicles/Create', [
            'companies' => Company::select('id', 'name')->get(),
            'providers' => Provider::select('id', 'name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->vehicleRules());

        $validated['mileage_km'] = $validated['mileage_km'] ?? 0;
        Vehicle::create($validated);

        return redirect()->route('vehicles.index')->with('success', 'Vehicle created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Vehicle $vehicle): Response
    {
        $vehicle->load([
            'company',
            'provider',
            'locations.provider',
            'photos',
            'documents',
            'damages',
            'workshopOrders.provider',
            'saleContracts',
            'rentingContracts',
            'invoices',
            'costEntries',
            'insurancePolicy',
            'alerts'
        ]);

        return Inertia::render('Vehicles/Show', [
            'vehicle' => $this->serializeVehicle($vehicle),
            'initialTab' => $request->string('tab')->toString() ?: 'general',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vehicle $vehicle): Response
    {
        return Inertia::render('Vehicles/Edit', [
            'vehicle' => $vehicle,
            'companies' => Company::select('id', 'name')->get(),
            'providers' => Provider::select('id', 'name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $validated = $request->validate($this->vehicleRules($vehicle));
        $validated['mileage_km'] = $validated['mileage_km'] ?? 0;

        $vehicle->update($validated);

        return redirect()->route('vehicles.index')->with('success', 'Vehicle updated successfully.');
    }

    public function storeDocument(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(VehicleDocument::SLOT_TYPES)],
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:15360',
            'notes' => 'nullable|string|max:1000',
        ]);

        $file = $request->file('file');

        if (! $file instanceof UploadedFile) {
            throw ValidationException::withMessages([
                'file' => 'No pudimos leer el archivo seleccionado.',
            ]);
        }

        $documentType = (string) $validated['type'];
        $existingDocument = $vehicle->documents()->where('type', $documentType)->first();

        if ($existingDocument && $existingDocument->disk === 'public' && Storage::disk('public')->exists($existingDocument->url)) {
            Storage::disk('public')->delete($existingDocument->url);
        }

        $relativePath = $this->storeVehicleDocumentFile($vehicle, $documentType, $file);

        $vehicle->documents()->updateOrCreate(
            ['type' => $documentType],
            [
                'url' => $relativePath,
                'disk' => 'public',
                'size_bytes' => $file->getSize(),
                'mime_type' => $file->getClientMimeType(),
                'original_filename' => $file->getClientOriginalName(),
                'status' => 'pendiente',
                'uploaded_by_user_id' => $request->user()?->id,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        $this->syncVehicleDocumentFlags($vehicle, $documentType);

        return redirect()
            ->route('vehicles.show', ['vehicle' => $vehicle, 'tab' => 'documents'])
            ->with('success', 'El documento se guardo correctamente.');
    }

    public function downloadDocument(Vehicle $vehicle, VehicleDocument $document): StreamedResponse
    {
        abort_unless($document->vehicle_id === $vehicle->id, 404);

        $disk = $document->disk ?: 'public';
        abort_unless(Storage::disk($disk)->exists($document->url), 404);

        return Storage::disk($disk)->download(
            $document->url,
            $document->original_filename ?: basename($document->url)
        );
    }

    /**
     * Import vehicles from an Excel file using updateOrCreate semantics by plate.
     */
    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'provider_id' => 'nullable|exists:providers,id',
            'file' => 'required|file|mimes:xlsx|max:10240',
        ]);

        $rows = $this->parseXlsxFile($request->file('file'));

        $rows = $this->removeHeadingRow($rows);

        if ($rows === []) {
            throw ValidationException::withMessages([
                'file' => 'El archivo no contiene filas validas para importar.',
            ]);
        }

        $created = 0;
        $updated = 0;
        $skipped = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 1;
            $payload = $this->buildImportPayload($row, $validated);

            if (blank($payload['plate'] ?? null)) {
                $skipped[] = "Fila {$rowNumber}: falta la placa.";
                continue;
            }

            $vehicle = Vehicle::withTrashed()->firstOrNew([
                'plate' => $payload['plate'],
            ]);

            $validator = Validator::make($payload, $this->vehicleRules($vehicle->exists ? $vehicle : null));

            if ($validator->fails()) {
                $skipped[] = "Fila {$rowNumber}: " . collect($validator->errors()->all())->take(2)->implode(' ');
                continue;
            }

            $wasExisting = $vehicle->exists;
            $vehicle->fill($validator->validated());

            if ($vehicle->trashed()) {
                $vehicle->restore();
            }

            $vehicle->save();

            if ($wasExisting) {
                $updated++;
            } else {
                $created++;
            }
        }

        if ($created === 0 && $updated === 0) {
            throw ValidationException::withMessages([
                'file' => $skipped[0] ?? 'No se pudo importar ningun vehiculo.',
            ]);
        }

        $message = "Importacion completada: {$created} creados y {$updated} actualizados.";

        if ($skipped !== []) {
            $message .= ' ' . count($skipped) . ' filas fueron omitidas.';
        }

        return redirect()
            ->route('vehicles.create')
            ->with('success', $message)
            ->with('warning', $skipped !== [] ? collect($skipped)->take(3)->implode(' | ') : null);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->delete();

        return redirect()->route('vehicles.index')->with('success', 'Vehicle deleted successfully.');
    }

    private function serializeVehicle(Vehicle $vehicle): array
    {
        $payload = $vehicle->toArray();
        $payload['documents'] = $vehicle->documents
            ->sortBy('type')
            ->values()
            ->map(fn (VehicleDocument $document) => $this->serializeVehicleDocument($document))
            ->all();

        return $payload;
    }

    private function serializeVehicleDocument(VehicleDocument $document): array
    {
        return [
            'id' => $document->id,
            'name' => $document->original_filename,
            'document_type' => $document->type,
            'created_at' => optional($document->created_at)?->toISOString(),
            'updated_at' => optional($document->updated_at)?->toISOString(),
            'file_url' => $document->disk === 'public' ? Storage::disk('public')->url($document->url) : null,
            'download_url' => route('vehicles.documents.download', [$document->vehicle_id, $document]),
            'mime_type' => $document->mime_type,
            'size_kb' => $document->size_bytes ? (int) ceil($document->size_bytes / 1024) : null,
            'status' => $document->status,
            'notes' => $document->notes,
        ];
    }

    private function storeVehicleDocumentFile(Vehicle $vehicle, string $documentType, UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension() ?: $file->extension() ?: 'bin';
        $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeBaseName = Str::slug($baseName) ?: $documentType;
        $fileName = now()->format('Ymd_His') . '_' . Str::random(8) . '_' . $safeBaseName . '.' . Str::lower($extension);
        $directory = "vehicles/{$vehicle->id}/documents/{$documentType}";

        return $file->storeAs($directory, $fileName, 'public');
    }

    private function syncVehicleDocumentFlags(Vehicle $vehicle, string $documentType): void
    {
        $updates = [];

        if ($documentType === 'ficha_tecnica' && ! $vehicle->has_ficha_tecnica) {
            $updates['has_ficha_tecnica'] = true;
        }

        if ($documentType === 'permiso_circulacion' && ! $vehicle->has_permiso_circulacion) {
            $updates['has_permiso_circulacion'] = true;
        }

        if ($updates !== []) {
            $vehicle->update($updates);
        }
    }

    private function vehicleRules(?Vehicle $vehicle = null): array
    {
        return [
            'company_id' => 'required|exists:companies,id',
            'provider_id' => 'nullable|exists:providers,id',
            'plate' => ['required', 'string', Rule::unique('vehicles')->ignore($vehicle?->id)],
            'vin' => ['nullable', 'string', Rule::unique('vehicles')->ignore($vehicle?->id)],
            'brand' => 'required|string',
            'model' => 'required|string',
            'version' => 'nullable|string',
            'color' => 'nullable|string',
            'registration_date' => 'nullable|date',
            'mileage_km' => 'nullable|integer',
            'raw_location' => 'nullable|string',
            'raw_physical_status' => 'nullable|string',
            'salesperson_name' => 'nullable|string',
            'physical_status' => [
                'required',
                Rule::in([
                    'en_transito',
                    'en_campa',
                    'taller_chapa',
                    'taller_dab',
                    'taller_limpieza',
                    'pendiente_recogida',
                    'disponible',
                    'entregado',
                ])
            ],
            'commercial_status' => [
                'required',
                Rule::in([
                    'no_publicable',
                    'disponible',
                    'reservado',
                    'vendido',
                ])
            ],
            'taxable_base' => 'nullable|numeric|min:0',
            'pvi' => 'nullable|numeric|min:0',
            'price_financed' => 'nullable|numeric|min:0',
            'price_cash' => 'nullable|numeric|min:0',
            'num_keys' => 'nullable|integer|min:0|max:2',
            'has_ficha_tecnica' => 'boolean',
            'has_permiso_circulacion' => 'boolean',
            'insurance_active' => 'boolean',
            'insurance_activated_at' => 'nullable|date',
            'exported_to_motorflash' => 'boolean',
            'last_export_at' => 'nullable|date',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_updated_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ];
    }

    private function removeHeadingRow(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        return array_values(array_slice($rows, 1));
    }

    private function parseXlsxFile(UploadedFile $file): array
    {
        $path = $file->getRealPath();

        if ($path === false) {
            throw ValidationException::withMessages([
                'file' => 'No pudimos leer el archivo Excel seleccionado.',
            ]);
        }

        $zip = new \ZipArchive();
        $openResult = $zip->open($path);

        if ($openResult !== true) {
            throw ValidationException::withMessages([
                'file' => 'No pudimos abrir el archivo Excel seleccionado.',
            ]);
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');

        if ($sheetXml === false) {
            $zip->close();

            throw ValidationException::withMessages([
                'file' => 'El archivo Excel no contiene una hoja principal valida para importar.',
            ]);
        }

        $sharedStrings = $this->readSharedStrings($zip);
        $dateStyles = $this->readDateStyleIndexes($zip);

        $sheet = simplexml_load_string($sheetXml);

        if ($sheet === false || !isset($sheet->sheetData->row)) {
            $zip->close();

            throw ValidationException::withMessages([
                'file' => 'No pudimos leer las filas del archivo Excel.',
            ]);
        }

        $rows = [];

        foreach ($sheet->sheetData->row as $row) {
            $cells = [];

            foreach ($row->c as $cell) {
                $reference = (string) ($cell['r'] ?? '');
                $index = $this->columnReferenceToIndex($reference);

                if ($index < 0) {
                    continue;
                }

                $cells[$index] = $this->extractXlsxCellValue($cell, $sharedStrings, $dateStyles);
            }

            if ($cells === []) {
                continue;
            }

            ksort($cells);

            $normalizedRow = [];
            $maxIndex = max(array_keys($cells));

            for ($index = 0; $index <= $maxIndex; $index++) {
                $value = $cells[$index] ?? null;
                $normalizedRow[$index] = is_string($value) ? trim($value) : $value;
            }

            if (collect($normalizedRow)->filter(fn($value) => $value !== null && $value !== '')->isEmpty()) {
                continue;
            }

            $rows[] = $normalizedRow;
        }

        $zip->close();

        return $rows;
    }

    private function readSharedStrings(\ZipArchive $zip): array
    {
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');

        if ($sharedStringsXml === false) {
            return [];
        }

        $sharedStrings = simplexml_load_string($sharedStringsXml);

        if ($sharedStrings === false) {
            return [];
        }

        $values = [];

        foreach ($sharedStrings->si as $item) {
            if (isset($item->t)) {
                $values[] = (string) $item->t;
                continue;
            }

            $text = '';

            foreach ($item->r as $run) {
                $text .= (string) ($run->t ?? '');
            }

            $values[] = $text;
        }

        return $values;
    }

    private function readDateStyleIndexes(\ZipArchive $zip): array
    {
        $stylesXml = $zip->getFromName('xl/styles.xml');

        if ($stylesXml === false) {
            return [];
        }

        $styles = simplexml_load_string($stylesXml);

        if ($styles === false) {
            return [];
        }

        $customDateFormats = [];

        if (isset($styles->numFmts->numFmt)) {
            foreach ($styles->numFmts->numFmt as $format) {
                $numFmtId = (int) ($format['numFmtId'] ?? 0);
                $formatCode = Str::lower((string) ($format['formatCode'] ?? ''));

                if ($this->looksLikeExcelDateFormat($formatCode)) {
                    $customDateFormats[$numFmtId] = true;
                }
            }
        }

        $builtInDateFormats = [14, 15, 16, 17, 18, 19, 20, 21, 22, 45, 46, 47];
        $dateStyles = [];

        if (isset($styles->cellXfs->xf)) {
            foreach ($styles->cellXfs->xf as $styleIndex => $xf) {
                $numFmtId = (int) ($xf['numFmtId'] ?? 0);

                if (in_array($numFmtId, $builtInDateFormats, true) || isset($customDateFormats[$numFmtId])) {
                    $dateStyles[(int) $styleIndex] = true;
                }
            }
        }

        return $dateStyles;
    }

    private function looksLikeExcelDateFormat(string $formatCode): bool
    {
        if ($formatCode === '') {
            return false;
        }

        return Str::contains($formatCode, ['yy', 'yyyy', 'dd', 'mm', 'm/', 'd/', 'h:', 'ss']);
    }

    private function extractXlsxCellValue(\SimpleXMLElement $cell, array $sharedStrings, array $dateStyles): mixed
    {
        $type = (string) ($cell['t'] ?? '');
        $styleIndex = isset($cell['s']) ? (int) $cell['s'] : null;

        if ($type === 'inlineStr') {
            if (isset($cell->is->t)) {
                return (string) $cell->is->t;
            }

            $text = '';

            foreach ($cell->is->r as $run) {
                $text .= (string) ($run->t ?? '');
            }

            return $text;
        }

        $value = isset($cell->v) ? (string) $cell->v : null;

        if ($value === null || $value === '') {
            return null;
        }

        if ($type === 's') {
            return $sharedStrings[(int) $value] ?? null;
        }

        if ($type === 'b') {
            return $value === '1' ? '1' : '0';
        }

        if ($type === 'str') {
            return $value;
        }

        if ($styleIndex !== null && isset($dateStyles[$styleIndex]) && is_numeric($value)) {
            $days = (float) $value;
            $wholeDays = (int) floor($days);
            $seconds = (int) round(($days - $wholeDays) * 86400);

            return Carbon::create(1899, 12, 30, 0, 0, 0, config('app.timezone'))
                ->addDays($wholeDays)
                ->addSeconds($seconds)
                ->format('Y-m-d');
        }

        return $value;
    }

    private function columnReferenceToIndex(string $cellReference): int
    {
        if (!preg_match('/^[A-Z]+/i', $cellReference, $matches)) {
            return -1;
        }

        $letters = Str::upper($matches[0]);
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return $index - 1;
    }

    private function buildImportPayload(array $row, array $defaults): array
    {
        $data = [];

        foreach (self::IMPORT_COLUMN_MAP as $index => $field) {
            $data[$field] = $row[$index] ?? null;
        }

        return [
            'company_id' => $this->resolveCompanyId($data['company_name'] ?? null, (int) $defaults['company_id']),
            'provider_id' => $this->resolveProviderId($data['provider_name'] ?? null, $defaults['provider_id'] ? (int) $defaults['provider_id'] : null),
            'plate' => $this->normalizeNullableString($data['plate'] ?? null, true),
            'vin' => null,
            'brand' => $this->normalizeNullableString($data['brand'] ?? null),
            'model' => $this->normalizeNullableString($data['model'] ?? null),
            'version' => $this->normalizeNullableString($data['version'] ?? null),
            'color' => $this->normalizeNullableString($data['color'] ?? null),
            'registration_date' => $this->parseDate($data['registration_date'] ?? null),
            'mileage_km' => $this->parseInteger($data['mileage_km'] ?? null),
            'raw_location' => $this->normalizeNullableString($data['raw_location'] ?? null),
            'raw_physical_status' => $this->normalizeNullableString($data['raw_physical_status'] ?? null),
            'salesperson_name' => null,
            'physical_status' => $this->parsePhysicalStatus($data['raw_physical_status'] ?? null) ?? 'en_transito',
            'commercial_status' => $this->parseCommercialStatus($data['commercial_status'] ?? null) ?? 'no_publicable',
            'taxable_base' => $this->parseDecimal($data['taxable_base'] ?? null),
            'pvi' => $this->parseDecimal($data['pvi'] ?? null),
            'price_financed' => $this->parseDecimal($data['price_financed'] ?? null),
            'price_cash' => $this->parseDecimal($data['price_cash'] ?? null),
            'num_keys' => 0,
            'has_ficha_tecnica' => false,
            'has_permiso_circulacion' => false,
            'insurance_active' => false,
            'insurance_activated_at' => null,
            'exported_to_motorflash' => false,
            'last_export_at' => null,
            'latitude' => null,
            'longitude' => null,
            'location_updated_at' => now()->format('Y-m-d'),
            'notes' => null,
        ];
    }

    private function resolveCompanyId(mixed $value, int $fallback): int
    {
        $name = $this->normalizeNullableString($value);

        if ($name === null) {
            return $fallback;
        }

        $company = Company::query()
            ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
            ->first();

        return $company?->id ?? $fallback;
    }

    private function resolveProviderId(mixed $value, ?int $fallback): ?int
    {
        $name = $this->normalizeNullableString($value);

        if ($name === null) {
            return $fallback;
        }

        $provider = Provider::query()
            ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
            ->first();

        return $provider?->id ?? $fallback;
    }

    private function normalizeNullableString(mixed $value, bool $uppercase = false): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        if ($normalized === '') {
            return null;
        }

        return $uppercase ? Str::upper($normalized) : $normalized;
    }

    private function parseInteger(mixed $value): ?int
    {
        $normalized = $this->normalizeNullableString($value);

        if ($normalized === null) {
            return null;
        }

        return is_numeric($normalized) ? (int) $normalized : null;
    }

    private function parseDecimal(mixed $value): ?float
    {
        $normalized = $this->normalizeNullableString($value);

        if ($normalized === null) {
            return null;
        }

        $normalized = preg_replace('/[^0-9,.\-]/', '', $normalized) ?? '';
        $lastComma = strrpos($normalized, ',');
        $lastDot = strrpos($normalized, '.');

        if ($lastComma !== false && $lastDot !== false) {
            if ($lastComma > $lastDot) {
                $normalized = str_replace('.', '', $normalized);
                $normalized = str_replace(',', '.', $normalized);
            } else {
                $normalized = str_replace(',', '', $normalized);
            }
        } elseif ($lastComma !== false) {
            $normalized = str_replace(',', '.', $normalized);
        }

        return is_numeric($normalized) ? (float) $normalized : null;
    }

    private function parseDate(mixed $value): ?string
    {
        $normalized = $this->normalizeNullableString($value);

        if ($normalized === null) {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'd.m.Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $normalized)->format('Y-m-d');
            } catch (\Throwable) {
            }
        }

        try {
            return Carbon::parse($normalized)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function parsePhysicalStatus(mixed $value): ?string
    {
        $normalized = Str::of((string) ($value ?? ''))
            ->trim()
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->toString();

        return match ($normalized) {
            'en_transito', 'transito' => 'en_transito',
            'en_campa', 'campa' => 'en_campa',
            'taller_chapa', 'chapa' => 'taller_chapa',
            'taller_dab', 'dab' => 'taller_dab',
            'taller_limpieza', 'limpieza' => 'taller_limpieza',
            'pendiente_recogida' => 'pendiente_recogida',
            'disponible' => 'disponible',
            'entregado' => 'entregado',
            default => null,
        };
    }

    private function parseCommercialStatus(mixed $value): ?string
    {
        $normalized = Str::of((string) ($value ?? ''))
            ->trim()
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->toString();

        return match ($normalized) {
            'no_publicable', 'no_publicado' => 'no_publicable',
            'disponible' => 'disponible',
            'reservado' => 'reservado',
            'vendido' => 'vendido',
            default => null,
        };
    }
}
