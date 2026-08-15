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
        // esto es para registrar las empresas clientes del sistema, como DKM0, By Renting o DAB. a cada empresa de forma organizada.
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // DKM0 / By Renting / DAB
            $table->string('tax_id')->unique();           // CIF/NIF de la empresa
            $table->string('invoice_series')->nullable(); // Serie de facturación (BYR-, DKM-, DAB-)
            $table->string('logo_url')->nullable();       // Logo para el Design System
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
        Schema::dropIfExists('companies');
    }
};
