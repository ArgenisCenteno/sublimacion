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
        Schema::create('insurance_policies', function (Blueprint $table) {
            $table->id();

            // ── Vehículo asegurado ────────────────────────────────────
            $table->foreignId('vehicle_id')
                  ->unique()                          // 1:1 — una póliza activa por vehículo
                  ->constrained('vehicles')
                  ->cascadeOnDelete();

            // ── Datos de la póliza Occident ───────────────────────────
            $table->string('policy_number')->nullable(); // Nº póliza provisional de Occident
            $table->string('occident_policy_id')->nullable(); // ID interno de la API Occident

            // ── Estado de la póliza ───────────────────────────────────
            $table->enum('status', [
                'pendiente_alta',   // Alta solicitada a Occident, esperando respuesta
                'activa',           // Póliza activa y confirmada
                'pendiente_baja',   // Baja solicitada (vehículo vendido)
                'baja',             // Póliza cancelada
            ])->default('pendiente_alta');

            // ── Fechas ────────────────────────────────────────────────
            $table->timestamp('activated_at')->nullable();    // Alta en Occident
            $table->timestamp('deactivated_at')->nullable();  // Baja en Occident

            // ── Quién solicitó el alta (al marcar check en recepción) ─
            $table->foreignId('activated_by_provider_id')
                  ->nullable()
                  ->constrained('providers')
                  ->nullOnDelete();

            // ── Respuesta bruta de la API Occident ───────────────────
            $table->json('api_response')->nullable();         // Para auditoría/debug

            $table->text('notes')->nullable();
            $table->timestamps();

            // ── Índices ───────────────────────────────────────────────
            $table->index('status');
            $table->index('policy_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurance_policies');
    }
};
