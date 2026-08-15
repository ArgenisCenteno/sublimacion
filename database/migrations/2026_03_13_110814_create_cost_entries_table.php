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
        Schema::create('cost_entries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vehicle_id')
                  ->constrained('vehicles')
                  ->cascadeOnDelete();

            // ── Origen del coste ──────────────────────────────────────
            // Puede venir del PVI del CSV, de una factura asignada o de un fee manual
            $table->foreignId('invoice_vehicle_id')
                  ->nullable()
                  ->constrained('invoice_vehicles')
                  ->nullOnDelete();

            // ── Tipo de coste — construye el waterfall de costing ─────
            $table->enum('type', [
                'pvi',              // Coste de compra inicial — viene del CSV
                'taller_chapa',     // Factura de chapa y pintura
                'taller_mecanica',  // Factura de mecánica / piezas
                'taller_dab',       // Revisión técnica interna
                'limpieza',         // Limpieza pre-entrega
                'transporte',       // Coste de logística
                'fee_gestion',      // Fee imputado por la empresa (% o fijo)
                'seguro',           // Prima de seguro de flota
                'otro',
            ]);

            // ── Importe ───────────────────────────────────────────────
            $table->decimal('amount', 10, 2);
            $table->string('concept')->nullable();   // Descripción libre del coste

            // ── Quién lo registró ─────────────────────────────────────
            $table->foreignId('created_by_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->date('entry_date')->nullable();  // Fecha del coste
            $table->text('notes')->nullable();

            $table->timestamps();

            // ── Índices ───────────────────────────────────────────────
            $table->index('vehicle_id');
            $table->index('type');

            // Índice compuesto — calculadora de margen: suma todos los costes de un vehículo
            $table->index(['vehicle_id', 'type'], 'ce_vehicle_type_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_entries');
    }
};
