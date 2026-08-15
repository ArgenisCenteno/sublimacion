<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();

            // ── Multitenancy ──────────────────────────────────────────
            $table->foreignId('company_id')
                  ->constrained('companies')
                  ->restrictOnDelete();

            $table->foreignId('provider_id')
                  ->nullable()
                  ->constrained('providers')
                  ->nullOnDelete();            // Proveedor origen del vehículo

            // ── Identificación del vehículo ───────────────────────────
            $table->string('plate')->unique();              // MATRÍCULA — clave maestra del negocio
            $table->string('vin')->nullable()->unique();    // Bastidor — se completa en recepción campa
            $table->string('brand');                        // Marca  (CSV: Marca)
            $table->string('model');                        // Modelo (CSV: Modelo col.1)
            $table->string('version')->nullable();          // Versión (CSV: Modelo col.2)
            $table->string('color')->nullable();            // Color  (CSV: COLOR)
            $table->date('registration_date')->nullable();  // Fecha matriculación (CSV: F.MAT)
            $table->unsignedInteger('mileage_km')->default(0); // Kilómetros (CSV: KM)

            // ── Datos del CSV — campos en bruto del proveedor ─────────
            $table->string('raw_location')->nullable();         // UBICACIÓN (texto libre del proveedor)
            $table->string('raw_physical_status')->nullable();  // ESTADO    (texto libre del proveedor)
            $table->string('salesperson_name')->nullable();     // Comercial (nombre del vendedor)

            // ── Estado logístico (controlado por el sistema) ──────────
            $table->enum('physical_status', [
                'en_transito',          // Cargado por CSV, no recepcionado aún
                'en_campa',             // Recepcionado, pendiente de taller
                'taller_chapa',         // En taller de chapa y pintura
                'taller_dab',           // En revisión técnica interna (DAB)
                'taller_limpieza',      // En limpieza pre-entrega
                'pendiente_recogida',   // Limpieza finalizada, logística pendiente
                'disponible',           // Listo para venta/entrega
                'entregado',            // Entregado al cliente final
            ])->default('en_transito');

            // ── Estado comercial ──────────────────────────────────────
            $table->enum('commercial_status', [
                'no_publicable',    // Sin datos suficientes (fotos/precio)
                'disponible',       // Publicado o listo para publicar
                'reservado',        // Reservado por cliente (bloquea edición)
                'vendido',          // Venta cerrada
            ])->default('no_publicable');

            // ── Financiero ────────────────────────────────────────────
            $table->decimal('taxable_base', 10, 2)->nullable();  // B.IMPONIBLE — base sin IVA
            $table->decimal('pvi', 10, 2)->nullable();           // P.COMPRA    — coste real de compra
            $table->decimal('price_financed', 10, 2)->nullable();// PV FINANCIADO
            $table->decimal('price_cash', 10, 2)->nullable();    // PVP CONTADO

            // ── Flags operativos (recepción en campa) ─────────────────
            $table->unsignedTinyInteger('num_keys')->default(0); // Nº llaves: 0 / 1 / 2
            $table->boolean('has_ficha_tecnica')->default(false);
            $table->boolean('has_permiso_circulacion')->default(false);

            // ── Seguro de flota ───────────────────────────────────────
            $table->boolean('insurance_active')->default(false);
            $table->timestamp('insurance_activated_at')->nullable();

            // ── Publicación Motorflash ────────────────────────────────
            $table->boolean('exported_to_motorflash')->default(false);
            $table->timestamp('last_export_at')->nullable();

            // ── GPS — última posición conocida ────────────────────────
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamp('location_updated_at')->nullable();

            // ── Notas internas ────────────────────────────────────────
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ── Índices para rendimiento ──────────────────────────────
            $table->index('company_id');
            $table->index('provider_id');
            $table->index('physical_status');
            $table->index('commercial_status');
            $table->index('brand');
            $table->index('registration_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
