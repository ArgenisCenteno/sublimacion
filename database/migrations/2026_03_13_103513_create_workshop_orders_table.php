<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workshop_orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vehicle_id')
                  ->constrained('vehicles')
                  ->cascadeOnDelete();

            // FIX #2: era restrictOnDelete() → bloqueaba soft-deletes en PostgreSQL.
            // nullOnDelete() permite que si un Provider se elimina físicamente,
            // la orden histórica sobrevive con provider_id = NULL.
            $table->foreignId('provider_id')
                  ->nullable()
                  ->constrained('providers')
                  ->nullOnDelete();

            // ── Tipo de orden — determina el PIN y la vista ───────────
            $table->enum('type', [
                'chapa',     // PIN 1XXX — taller externo chapa y pintura
                'dab',       // PIN 2XXX — revisión técnica interna DAB
                'limpieza',  // PIN 3XXX — limpieza pre-entrega
            ]);

            // ── Estado de la orden ────────────────────────────────────
            $table->enum('status', [
                'pendiente',     // Orden creada, vehículo aún no ha llegado
                'en_proceso',    // Taller confirmó recepción del vehículo
                'incidencia',    // Fallo detectado en checklist DAB o daño inesperado
                'finalizado',    // Trabajo completado con foto de salida
            ])->default('pendiente');

            // ── Fechas del ciclo en taller ────────────────────────────
            $table->timestamp('entry_at')->nullable();   // Cuando el taller confirma recepción
            $table->timestamp('exit_at')->nullable();    // Cuando el taller finaliza el trabajo

            // ── Foto de salida obligatoria ────────────────────────────
            // Evidencia del trabajo realizado — obligatoria para finalizar
            $table->string('exit_photo_url')->nullable();
            $table->string('exit_photo_disk')->default('local');

            // ── Quién abrió la orden (operario interno o automático) ──
            $table->foreignId('created_by_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ── Índices simples ───────────────────────────────────────
            $table->index('type');
            $table->index('entry_at'); // Alerta "Días en taller > 15"

            // ── FIX #5: Índices compuestos para queries del Dashboard ─
            $table->index(['vehicle_id', 'status'],  'wo_vehicle_status_idx');
            $table->index(['status', 'type'],         'wo_status_type_idx');
            $table->index(['provider_id', 'status'],  'wo_provider_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workshop_orders');
    }
};
