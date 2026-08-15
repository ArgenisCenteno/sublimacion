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
        // database/migrations/create_examenes_table.php
        Schema::create('examenes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();        // HEM-001, GLU-001
            $table->string('nombre');                       // Hemograma completo
            $table->string('nombre_corto')->nullable();     // HGB, GLU
            $table->string('categoria');                    // Hematología, Bioquímica...
            $table->string('muestra');                      // Sangre, Orina, Heces...
            $table->string('tubo')->nullable();             // Rojo, Lila, Verde...
            $table->unsignedSmallInteger('tiempo_resultado')->default(24); // horas
            $table->string('unidad')->nullable();           // g/dL, mg/dL, %...
            $table->string('metodo')->nullable();           // Colorimétrico, Enzimático...
            $table->decimal('precio', 10, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->text('instrucciones')->nullable();      // Ayuno 8 horas...
            $table->string('subcategoria')->nullable();
            $table->unsignedTinyInteger('orden_col')->default(1);
            $table->unsignedTinyInteger('orden_fila')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examenes');
    }
};
