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
        Schema::create('providers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                  ->constrained('companies')
                  ->restrictOnDelete();

            $table->string('name');                         // "Talleres Pepe", "Limpiezas García"

            // Tipo de proveedor — determina el PIN prefix y la vista que se abre
            $table->enum('type', [
                'chapa',               // Taller chapa y pintura    → PIN 1XXX
                'dab',                 // Revisión técnica interna  → PIN 2XXX
                'limpieza',            // Limpieza pre-entrega      → PIN 3XXX
                'recepcion',           // Recepción campa           → PIN 4XXX
                'proveedor_vehiculos', // Suministra coches vía CSV
                'otro',
            ])->default('otro');

            // Credencial de acceso sin login (mecánicos externos)
            $table->string('pin')->nullable();              // PIN hasheado en BD
            $table->string('pin_prefix', 10)->nullable();   // 1XXX / 2XXX / 3XXX / 4XXX

            // Geofencing — coordenadas del taller para validar acceso
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('geofence_radius_meters')->default(750); // Regla: 750m máximo

            // Datos de contacto y fiscales
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('tax_id')->nullable();           // NIF/CIF del proveedor

            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
