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
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();

            // Identificación
            $table->string('tipo_documento', 2)->default('V'); // V, E, P, J
            $table->string('cedula', 20)->unique();
            $table->string('nombres');
            $table->string('apellidos');
            $table->enum('sexo', ['M', 'F']);
            $table->date('fecha_nacimiento');

            $table->boolean('es_menor')->boolean();

            // Contacto
            $table->string('telefono', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('direccion')->nullable();

            // Representante (obligatorio si es menor)
            $table->string('rep_nombres')->nullable();
            $table->string('rep_apellidos')->nullable();
            $table->string('rep_cedula', 20)->nullable();
            $table->string('rep_telefono', 20)->nullable();
            $table->enum('rep_parentesco', [
                'madre',
                'padre',
                'abuelo',
                'abuela',
                'tutor_legal',
                'otro'
            ])->nullable();

            $table->string('medico_referente')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
