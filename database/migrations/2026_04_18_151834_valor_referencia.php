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
        // database/migrations/create_valores_referencia_table.php
        Schema::create('valores_referencia', function (Blueprint $table) {
            $table->id();
          $table->foreignId('examen_id')
      ->constrained('examenes')
      ->cascadeOnDelete();

            // A quién aplica
            $table->enum('sexo', ['M', 'F', 'ambos'])->default('ambos');
            $table->unsignedSmallInteger('edad_min')->default(0);   // en años
            $table->unsignedSmallInteger('edad_max')->default(999); // en años

            // Rangos
            $table->decimal('valor_min', 10, 4)->nullable();
            $table->decimal('valor_max', 10, 4)->nullable();
            $table->string('valor_texto')->nullable(); // para resultados cualitativos: "Negativo"

            $table->string('etiqueta')->nullable(); // "Adulto", "Niño", "Recién nacido"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('valores_referencia');
    }
};
