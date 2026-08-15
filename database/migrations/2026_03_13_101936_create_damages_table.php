<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('damages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vehicle_id')
                ->constrained('vehicles')
                ->cascadeOnDelete();

            $table->decimal('x_coord', 5, 4);
            $table->decimal('y_coord', 5, 4);
            $table->string('body_part', 80)->nullable();

            $table->string('photo_url')->nullable();
            $table->string('disk')->default('local');

            $table->string('description');
            $table->enum('severity', ['leve', 'moderado', 'grave'])->default('leve');

            $table->foreignId('provider_id')
                ->nullable()
                ->constrained('providers')
                ->nullOnDelete();

            $table->enum('stage', [
                'recepcion',
                'taller_chapa',
                'taller_dab',
                'taller_limpieza',
            ])->default('recepcion');

            $table->enum('status', ['pendiente', 'en_reparacion', 'resuelto'])->default('pendiente');

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('vehicle_id');
            $table->index('status');
            $table->index('stage');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damages');
    }
};
