<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('renting_contracts', function (Blueprint $table) {
            $table->id();

            // ── Solo vehículos de By Renting ──────────────────────────
            // Un vehículo solo puede tener 1 contrato de renting ACTIVO
            $table->foreignId('vehicle_id')
                  ->constrained('vehicles')
                  ->restrictOnDelete();

            $table->foreignId('client_id')
                  ->constrained('clients')
                  ->restrictOnDelete();

            $table->foreignId('created_by_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // ── Condiciones económicas del contrato ───────────────────
            $table->decimal('monthly_fee', 10, 2);         // Cuota mensual total
            $table->json('concepts')->nullable();
            // Ejemplo: {"cuota_renting": 450, "seguro": 80, "mantenimiento": 50}

            // ── Vigencia del contrato ─────────────────────────────────
            $table->date('start_date');
            $table->date('end_date');

            // ── Estado del contrato ───────────────────────────────────
            $table->enum('status', [
                'activo',     // Contrato vigente — genera facturas mensuales
                'vencido',    // Fecha fin superada sin renovar
                'renovado',   // Se creó un nuevo contrato sustituto
                'cancelado',  // Rescisión anticipada
            ])->default('activo');

            // ── Control del motor de facturación (Cron Job) ───────────
            // Día del mes en que se genera la factura (ej: 1 = día 1 de cada mes)
            $table->unsignedTinyInteger('billing_day')->default(1);
            $table->date('last_billed_at')->nullable();     // Última factura generada

            // ── Alertas de vencimiento ────────────────────────────────
            // Trigger: 30 días antes del end_date → alerta al Dashboard
            $table->boolean('alert_sent_30_days')->default(false);

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ── Índices ───────────────────────────────────────────────
            $table->index('client_id');
            $table->index('status');
            $table->index('end_date');       // Para el Cron de alertas de vencimiento
            $table->index('last_billed_at'); // Para el Cron de facturación

            // Índice compuesto — contrato activo por vehículo
            $table->index(['vehicle_id', 'status'], 'rc_vehicle_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('renting_contracts');
    }
};
