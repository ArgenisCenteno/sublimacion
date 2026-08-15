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
        Schema::create('recurring_invoices', function (Blueprint $table) {
            $table->id();

            // ── Contrato de renting que la generó ────────────────────
            $table->foreignId('renting_contract_id')
                  ->constrained('renting_contracts')
                  ->restrictOnDelete();

            $table->foreignId('vehicle_id')
                  ->constrained('vehicles')
                  ->restrictOnDelete();

            $table->foreignId('client_id')
                  ->constrained('clients')
                  ->restrictOnDelete();

            // ── Datos de la factura generada ──────────────────────────
            $table->string('invoice_number')->unique();   // Serie By Renting (BYR-0001)
            $table->decimal('amount', 10, 2);            // Cuota mensual facturada
            $table->json('concepts')->nullable();         // Desglose de conceptos

            // ── Periodo facturado ─────────────────────────────────────
            $table->date('period_start');
            $table->date('period_end');

            // ── Archivo PDF generado ──────────────────────────────────
            $table->string('pdf_url')->nullable();
            $table->string('disk')->default('local');

            // ── Estado ───────────────────────────────────────────────
            $table->enum('status', [
                'generada',     // Cron Job la creó, pendiente de cobro
                'descargada',   // Gestoría la descargó
                'cobrada',      // Cobro confirmado
                'anulada',      // Anulada manualmente
            ])->default('generada');

            $table->timestamp('generated_at');           // Momento exacto del Cron
            $table->timestamp('downloaded_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ── Índices ───────────────────────────────────────────────
            $table->index('renting_contract_id');
            $table->index('vehicle_id');
            $table->index('status');
            $table->index('period_start');               // Para el historial de facturación
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_invoices');
    }
};
