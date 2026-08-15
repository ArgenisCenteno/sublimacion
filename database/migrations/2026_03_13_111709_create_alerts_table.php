<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vehicle_id')
                ->nullable()
                ->constrained('vehicles')
                ->cascadeOnDelete();

            $table->enum('type', [
                'insurance_active',
                'key_missing',
                'documentation_incomplete',
                'bottleneck',
                'taller_exit',
                'renting_expiry',
                'sale_reopened',
                'motorflash_blocked',
                'geofence_breach',
            ]);

            $table->enum('priority', ['alta', 'media', 'baja'])->default('media');
            $table->enum('status', ['pendiente', 'resuelta', 'descartada'])->default('pendiente');

            $table->json('payload')->nullable();

            $table->foreignId('resolved_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            $table->index('vehicle_id');
            $table->index('type');
            $table->index('priority');
            $table->index('status');
            $table->index(['status', 'priority'], 'al_status_priority_idx');
            $table->index(['vehicle_id', 'status'], 'al_vehicle_status_idx');
            $table->index(['vehicle_id', 'type', 'status'], 'al_dedup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
