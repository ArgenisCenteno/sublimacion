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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vehicle_id')
                  ->constrained('vehicles')
                  ->cascadeOnDelete();

            // ── Coordenadas GPS capturadas por el navegador del móvil ──
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->float('accuracy_meters')->nullable(); // Precisión reportada por el dispositivo

            // ── Tipo de evento que generó este registro de ubicación ───
            $table->enum('location_type', [
                'recepcion_campa',  // PIN 4XXX — bautizo digital, primer escaneo
                'entrada_taller',   // PIN 1XXX/2XXX/3XXX — mecánico confirma recepción
                'salida_taller',    // PIN 1XXX/2XXX/3XXX — mecánico finaliza trabajo
                'entrega_cliente',  // Entrega final al comprador
                'ping_manual',      // Escaneo manual sin acción específica
            ])->default('ping_manual');

            // ── Proveedor que generó el ping (todos acceden con PIN) ───
            $table->foreignId('provider_id')
                  ->nullable()
                  ->constrained('providers')
                  ->nullOnDelete();

            // ── PIN range que se usó (para trazabilidad) ──────────────
            // 1XXX=chapa, 2XXX=dab, 3XXX=limpieza, 4XXX=recepcion
            $table->string('pin_prefix', 10)->nullable();

            // ── Metadatos del dispositivo ─────────────────────────────
            $table->string('device_info')->nullable();  // User-Agent del móvil
            $table->string('ip_address', 45)->nullable();

            // ── Notas opcionales del operario ─────────────────────────
            $table->text('notes')->nullable();

            $table->timestamp('recorded_at');           // Momento exacto del ping GPS

            $table->timestamps();

            // ── Índices para el Radar / Mapa de calor del Dashboard ───
            $table->index('vehicle_id');
            $table->index('location_type');
            $table->index('recorded_at');
            $table->index('provider_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
