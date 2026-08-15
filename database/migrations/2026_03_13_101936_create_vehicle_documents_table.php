<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vehicle_id')
                ->constrained('vehicles')
                ->cascadeOnDelete();

            $table->string('type', 80); // ficha_tecnica, permiso_circulacion, factura_compra, informe_itv, mandato_gestoria, seguro, otro

            $table->string('url');
            $table->string('disk')->default('public');
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('mime_type', 50)->nullable();
            $table->string('original_filename')->nullable();

            $table->enum('status', [
                'pendiente',
                'descargado',
                'procesado',
            ])->default('pendiente');

            $table->foreignId('uploaded_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('downloaded_at')->nullable();
            $table->foreignId('downloaded_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('vehicle_id');
            $table->index(['vehicle_id', 'type']);
            $table->index('status');
            $table->unique(['vehicle_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_documents');
    }
};
