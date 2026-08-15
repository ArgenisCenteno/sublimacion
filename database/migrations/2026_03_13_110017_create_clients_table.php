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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            // ── Tipo de cliente ───────────────────────────────────────
            $table->enum('type', [
                'fisica',    // Particular
                'juridica',  // Empresa / Autónomo
            ])->default('fisica');

            // ── Datos de identificación ───────────────────────────────
            $table->string('name');
            $table->string('nif')->unique();              // NIF / CIF / NIE
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_secondary')->nullable();

            // ── Dirección ─────────────────────────────────────────────
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('province')->nullable();
            $table->string('country', 2)->default('ES');  // ISO 3166-1 alpha-2

            // ── Datos adicionales persona jurídica ────────────────────
            $table->string('company_name')->nullable();   // Razón social si type=juridica
            $table->string('contact_person')->nullable(); // Persona de contacto

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ── Índices ───────────────────────────────────────────────
            $table->index('nif');
            $table->index('type');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
