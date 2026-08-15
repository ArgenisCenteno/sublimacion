<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_workflow_plans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vehicle_id')
                ->unique()
                ->constrained('vehicles')
                ->cascadeOnDelete();

            $table->boolean('requires_chapa')->default(false);
            $table->boolean('requires_dab')->default(false);
            $table->boolean('requires_limpieza')->default(false);

            $table->foreignId('created_by_provider_id')
                ->nullable()
                ->constrained('providers')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('vehicle_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_workflow_plans');
    }
};
