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
        Schema::create('orden_examen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orden_trabajo_id');
            $table->unsignedBigInteger('examen_id');

            // Precio guardado al momento de crear (snapshot)
            $table->decimal('precio', 10, 2);

            // Estado individual del examen dentro de la orden
            $table->enum('estado', [
                'pendiente',
                'muestra_tomada',
                'en_proceso',
                'listo',
            ])->default('pendiente');

            // Resultado cargado por el analista
            $table->decimal('resultado_valor', 10, 4)->nullable();
            $table->string('resultado_texto', 100)->nullable();
            $table->string('resultado_unidad', 30)->nullable();

            // Flags del resultado
            $table->boolean('fuera_de_rango')->default(false);
            $table->enum('indicador', ['normal', 'alto', 'bajo', 'critico'])->nullable();

            // Quién procesó y cuándo
            $table->unsignedBigInteger('procesado_por')->nullable();
            $table->timestamp('procesado_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_examen');
    }
};
