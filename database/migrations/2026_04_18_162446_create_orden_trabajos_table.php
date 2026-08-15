<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orden_trabajos', function (Blueprint $table) {
            $table->id();

            // Código único generado automáticamente
            $table->string('codigo', 20)->unique();    // LAB-2025-00001

            // Relaciones principales
            $table->foreignId('paciente_id')->constrained()->restrictOnDelete();
            $table->foreignId('medico_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete(); // recepcionista

            // Estado del flujo
            $table->enum('estado', [
                'pendiente',    // recién creada, sin procesar
                'en_proceso',   // muestras tomadas, análisis en curso
                'listo',        // resultados listos, pendiente de entrega
                'entregado',    // resultados entregados al paciente
                'anulado',      // cancelada
            ])->default('pendiente');

            // Estado del pago
            $table->enum('estado_pago', [
                'pendiente',
                'pagado',
                'exonerado',
                'parcial'
            ])->default('pendiente');

            // Montos
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            // Metadatos
            $table->text('observaciones')->nullable();
            $table->timestamp('fecha_entrega_estimada')->nullable();
            $table->timestamp('fecha_entregado')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_trabajos');
    }
};
