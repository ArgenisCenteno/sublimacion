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
        Schema::create('invoice_vehicles', function (Blueprint $table) {
            $table->id();

            // ── Pivote N:M — Factura ↔ Vehículo ──────────────────────
            $table->foreignId('invoice_id')
                  ->constrained('invoices')
                  ->cascadeOnDelete();

            $table->foreignId('vehicle_id')
                  ->constrained('vehicles')
                  ->restrictOnDelete();

            // ── Importe asignado a este vehículo ──────────────────────
            // Si la factura tiene 10 matrículas → total_amount / 10 por coche
            $table->decimal('assigned_amount', 10, 2);

            // ── Cómo se asignó ────────────────────────────────────────
            $table->enum('assigned_by', [
                'ocr',      // El motor OCR detectó la matrícula automáticamente
                'manual',   // El usuario ajustó el importe manualmente
            ])->default('ocr');

            $table->text('notes')->nullable();
            $table->timestamps();

            // ── Un vehículo no puede aparecer dos veces en la misma factura
            $table->unique(['invoice_id', 'vehicle_id'], 'iv_invoice_vehicle_unique');

            // ── Índices ───────────────────────────────────────────────
            $table->index('invoice_id');
            $table->index('vehicle_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_vehicles');
    }
};
