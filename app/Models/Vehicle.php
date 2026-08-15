<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        // Multitenancy
        'company_id',
        'provider_id',
        // Identificación
        'plate',
        'vin',
        'brand',
        'model',
        'version',
        'color',
        'registration_date',
        'mileage_km',
        // Campos en bruto del CSV
        'raw_location',
        'raw_physical_status',
        'salesperson_name',
        // Estados
        'physical_status',
        'commercial_status',
        // Financiero
        'taxable_base',
        'pvi',
        'price_financed',
        'price_cash',
        // Flags operativos
        'num_keys',
        'has_ficha_tecnica',
        'has_permiso_circulacion',
        // Seguro
        'insurance_active',
        'insurance_activated_at',
        // Motorflash
        'exported_to_motorflash',
        'last_export_at',
        // GPS
        'latitude',
        'longitude',
        'location_updated_at',
        // Notas
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'registration_date'       => 'date',
            'mileage_km'              => 'integer',
            'taxable_base'            => 'decimal:2',
            'pvi'                     => 'decimal:2',
            'price_financed'          => 'decimal:2',
            'price_cash'              => 'decimal:2',
            'num_keys'                => 'integer',
            'has_ficha_tecnica'       => 'boolean',
            'has_permiso_circulacion' => 'boolean',
            'insurance_active'        => 'boolean',
            'insurance_activated_at'  => 'datetime',
            'exported_to_motorflash'  => 'boolean',
            'last_export_at'          => 'datetime',
            'latitude'                => 'float',
            'longitude'               => 'float',
            'location_updated_at'     => 'datetime',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    /** Historial completo de ubicaciones GPS */
    public function locations(): HasMany
    {
        return $this->hasMany(Location::class)->orderByDesc('recorded_at');
    }

    /** Última ubicación registrada */
    public function latestLocation(): HasOne
    {
        return $this->hasOne(Location::class)->latestOfMany('recorded_at');
    }

    /** Fotos del vehículo */
    public function photos(): HasMany
    {
        return $this->hasMany(VehiclePhoto::class);
    }

    /** Solo las 5 fotos obligatorias de recepción */
    public function mandatoryPhotos(): HasMany
    {
        return $this->hasMany(VehiclePhoto::class)->where('is_mandatory', true);
    }

    /** Fotos aprobadas para publicación comercial */
    public function commercialPhotos(): HasMany
    {
        return $this->hasMany(VehiclePhoto::class)->where('is_approved', true);
    }

    /** Bóveda documental */
    public function documents(): HasMany
    {
        return $this->hasMany(VehicleDocument::class);
    }

    /** Daños registrados sobre la silueta */
    public function damages(): HasMany
    {
        return $this->hasMany(Damage::class);
    }

    /** Daños pendientes de reparación */
    public function pendingDamages(): HasMany
    {
        return $this->hasMany(Damage::class)->where('status', 'pendiente');
    }

    /** Órdenes de taller — historial completo */
    public function workshopOrders(): HasMany
    {
        return $this->hasMany(WorkshopOrder::class)->orderByDesc('created_at');
    }

    /** Plan de etapas definido en campa */
    public function workflowPlan(): HasOne
    {
        return $this->hasOne(VehicleWorkflowPlan::class);
    }

    /** Orden de taller activa (en_proceso) */
    public function activeWorkshopOrder(): HasOne
    {
        return $this->hasOne(WorkshopOrder::class)->where('status', 'en_proceso')->latestOfMany();
    }

    /** ¿Cuántos días lleva en el taller activo? → trigger alerta 15 días */
    public function getDaysInWorkshopAttribute(): int
    {
        return $this->activeWorkshopOrder?->days_in_workshop ?? 0;
    }

    /** Historial completo de contratos de venta (decisión B) */
    public function saleContracts(): HasMany
    {
        return $this->hasMany(SaleContract::class)->orderByDesc('created_at');
    }

    /** Contrato de venta activo (reservado o vendido) */
    public function activeSaleContract(): HasOne
    {
        return $this->hasOne(SaleContract::class)
                    ->whereIn('status', ['reservado', 'vendido'])
                    ->latestOfMany();
    }

    /** Contrato de renting activo — exclusivo By Renting */
    public function rentingContract(): HasOne
    {
        return $this->hasOne(RentingContract::class)->where('status', 'activo')->latestOfMany();
    }

    /** Historial completo de contratos de renting */
    public function rentingContracts(): HasMany
    {
        return $this->hasMany(RentingContract::class)->orderByDesc('created_at');
    }

    /** Facturas imputadas a este vehículo (N:M) */
    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class, 'invoice_vehicles')
                    ->withPivot('assigned_amount', 'assigned_by', 'notes')
                    ->withTimestamps();
    }

    /** Líneas de coste — waterfall financiero */
    public function costEntries(): HasMany
    {
        return $this->hasMany(CostEntry::class)->orderBy('entry_date');
    }

    /** Coste Total Real = suma de todas las líneas de coste */
    public function getTotalCostAttribute(): float
    {
        return (float) $this->costEntries()->sum('amount');
    }

    /** Póliza de seguro Occident */
    public function insurancePolicy(): HasOne
    {
        return $this->hasOne(InsurancePolicy::class);
    }

    /** Alertas activas del vehículo */
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class)->orderByDesc('created_at');
    }

    /** Solo alertas pendientes */
    public function pendingAlerts(): HasMany
    {
        return $this->hasMany(Alert::class)->where('status', 'pendiente');
    }

    // ─── Helpers de Estado Logístico ─────────────────────────────────────────

    public function isEnTransito(): bool        { return $this->physical_status === 'en_transito'; }
    public function isEnCampa(): bool           { return $this->physical_status === 'en_campa'; }
    public function isEnTaller(): bool
    {
        return in_array($this->physical_status, ['taller_chapa', 'taller_dab', 'taller_limpieza']);
    }
    public function isPendienteRecogida(): bool { return $this->physical_status === 'pendiente_recogida'; }
    public function isDisponible(): bool        { return $this->physical_status === 'disponible'; }
    public function isEntregado(): bool         { return $this->physical_status === 'entregado'; }

    // ─── Helpers de Estado Comercial ─────────────────────────────────────────

    public function isVendido(): bool           { return $this->commercial_status === 'vendido'; }
    public function isReservado(): bool         { return $this->commercial_status === 'reservado'; }
    public function isPublicable(): bool        { return $this->commercial_status === 'disponible'; }

    // ─── Reglas de Negocio ────────────────────────────────────────────────────

    /**
     * Regla Motorflash: un vehículo puede exportarse si tiene
     * precio en contado y al menos 5 fotos (se validará con photos() en Bloque 4).
     */
    public function isExportable(): bool
    {
        return $this->commercial_status === 'disponible'
            && $this->price_cash > 0;
    }

    /**
     * Regla crítica de alertas: vehículo vendido con seguro aún activo.
     */
    public function hasInsuranceAlert(): bool
    {
        return $this->isVendido() && $this->insurance_active;
    }

    /**
     * Regla crítica de alertas: vehículo vendido con solo 1 llave.
     */
    public function hasKeyAlert(): bool
    {
        return $this->isVendido() && $this->num_keys === 1;
    }

    /**
     * Regla de seguimiento: falta documentacion obligatoria en un vehiculo
     * que ya esta en una fase comercial relevante.
     */
    public function hasDocumentationAlert(): bool
    {
        if (! in_array($this->commercial_status, ['disponible', 'reservado', 'vendido'], true)) {
            return false;
        }

        return $this->missingRequiredDocuments() !== [];
    }

    /**
     * Documentacion minima requerida para comercializar o entregar.
     *
     * @return array<int, string>
     */
    public function missingRequiredDocuments(): array
    {
        $missing = [];

        if (! $this->has_ficha_tecnica) {
            $missing[] = 'ficha_tecnica';
        }

        if (! $this->has_permiso_circulacion) {
            $missing[] = 'permiso_circulacion';
        }

        return $missing;
    }

    /**
     * Calcula el margen bruto en euros: PVP - Coste Total Real.
     */
    public function getGrossMarginAttribute(): float
    {
        if (! $this->price_cash) return 0;
        return $this->price_cash - $this->total_cost;
    }

    /**
     * Calcula el margen en porcentaje.
     */
    public function getMarginPercentAttribute(): float
    {
        if (! $this->price_cash) return 0;
        return round(($this->gross_margin / $this->price_cash) * 100, 2);
    }
}
