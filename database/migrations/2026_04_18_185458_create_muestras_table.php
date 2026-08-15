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
        Schema::create('muestras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_trabajo_id')->constrained()->cascadeOnDelete();

            // Tipo de muestra (sangre, orina, heces, etc.)
            $table->string('tipo');            // Sangre venosa, Orina, Heces...
            $table->string('tubo')->nullable(); // Lila (EDTA), Rojo, Verde...

            // Estado de la muestra
            $table->enum('estado', [
                'pendiente',    // aún no tomada
                'tomada',       // muestra física recolectada
                'en_proceso',   // en el analizador
                'procesada',    // análisis completado
                'rechazada',    // muestra no apta (hemolizada, insuficiente, etc.)
            ])->default('pendiente');

            // Quién tomó y cuándo
            $table->foreignId('tomada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('tomada_at')->nullable();

            // Motivo de rechazo si aplica
            $table->string('motivo_rechazo')->nullable();

            // Observaciones del proceso
            $table->text('observaciones')->nullable();

            $table->timestamps();
        });

        // Pivot muestra orden_examen
// Una muestra puede cubrir varios exámenes del mismo tipo
        Schema::create('muestra_orden_examen', function (Blueprint $table) {
            $table->foreignId('muestra_id')->constrained()->cascadeOnDelete();
            $table->foreignId('orden_examen_id')->constrained('orden_examen')->cascadeOnDelete();
            $table->primary(['muestra_id', 'orden_examen_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('muestra_orden_examen');
        Schema::dropIfExists('muestras');
    }
};
