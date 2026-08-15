<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_photos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vehicle_id')
                  ->constrained('vehicles')
                  ->cascadeOnDelete();

            // ── Tipo de foto ──────────────────────────────────────────
            $table->enum('type', [
                'frontal',          // Obligatoria en recepción
                'trasera',          // Obligatoria en recepción
                'lateral_izq',      // Obligatoria en recepción
                'lateral_der',      // Obligatoria en recepción
                'interior_km',      // Obligatoria en recepción (+ KM)
                'salida_taller',    // Obligatoria al finalizar WorkshopOrder
                'comercial',        // Fotos de calidad post-limpieza para Motorflash
                'dano',             // Foto detalle de un daño específico
            ]);

            // ── Etapa del ciclo en que se tomó ────────────────────────
            $table->enum('stage', [
                'recepcion',        // Bautizo digital — PIN 4XXX
                'taller_chapa',     // Salida de chapa — PIN 1XXX
                'taller_dab',       // Salida de DAB   — PIN 2XXX
                'taller_limpieza',  // Fotos comerciales finales — PIN 3XXX
            ])->default('recepcion');

            // ── Archivo ───────────────────────────────────────────────
            $table->string('url');                          // Ruta en storage
            $table->string('disk')->default('local');       // local / s3
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('mime_type', 50)->nullable();    // image/jpeg, image/png...

            // ── Quién subió la foto (siempre un proveedor con PIN) ────
            $table->foreignId('provider_id')
                  ->nullable()
                  ->constrained('providers')
                  ->nullOnDelete();

            // ── Foto obligatoria — controla el semáforo de recepción ──
            $table->boolean('is_mandatory')->default(false);

            // ── Foto aprobada para publicación comercial ──────────────
            $table->boolean('is_approved')->default(false);

            $table->timestamps();

            // ── Índices ───────────────────────────────────────────────
            $table->index('vehicle_id');
            $table->index(['vehicle_id', 'type']);
            $table->index(['vehicle_id', 'stage']);
            $table->index('is_mandatory');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_photos');
    }
};
