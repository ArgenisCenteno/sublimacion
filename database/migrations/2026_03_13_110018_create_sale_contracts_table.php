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
        Schema::create('sale_contracts', function (Blueprint $table) {
            $table->id();

            // ── Vehículo vendido ──────────────────────────────────────
            // NO es unique — decisión B: un vehículo puede tener historial
            // de múltiples contratos (vendido → reabierto → vendido)
            $table->foreignId('vehicle_id')
                  ->constrained('vehicles')
                  ->restrictOnDelete();

            // ── Cliente comprador ─────────────────────────────────────
            $table->foreignId('client_id')
                  ->constrained('clients')
                  ->restrictOnDelete();

            // ── Comercial que gestionó la venta ───────────────────────
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // ── Datos económicos de la venta ──────────────────────────
            $table->decimal('pvp', 10, 2);                // Precio de venta final
            $table->enum('payment_method', [
                'contado',
                'financiado',
                'mixto',
            ])->default('contado');

            // ── Estado del expediente ─────────────────────────────────
            $table->enum('status', [
                'reservado',   // Señal pagada, pendiente de firma
                'vendido',     // Venta cerrada y firmada
                'reabierto',   // Venta se cayó — vehículo vuelve a disponible
                'cancelado',   // Cancelado sin llegar a venta
            ])->default('reservado');

            // ── Trazabilidad de la venta ──────────────────────────────
            $table->timestamp('signed_at')->nullable();       // Fecha de firma del contrato

            // ── Trazabilidad del rollback (Gestión de Reversión) ──────
            $table->timestamp('reopened_at')->nullable();
            $table->foreignId('reopened_by_user_id')          // Solo Gerencia puede reabrir
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->text('reopen_reason')->nullable();         // Motivo del rollback

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ── Índices ───────────────────────────────────────────────
            $table->index('vehicle_id');
            $table->index('client_id');
            $table->index('status');
            $table->index('signed_at');

            // Índice compuesto — consulta más frecuente: contrato activo de un vehículo
            $table->index(['vehicle_id', 'status'], 'sc_vehicle_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_contracts');
    }
};
