<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workshop_checklist_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('workshop_order_id')
                  ->constrained('workshop_orders')
                  ->cascadeOnDelete();

            // ── Punto de revisión ─────────────────────────────────────
            // Exclusivo de órdenes tipo DAB (revisión técnica)
            $table->string('label');                    // Ej: "Niveles", "Luces", "Neumáticos"
            $table->unsignedTinyInteger('sort_order')->default(0); // Orden visual del checklist

            // ── Resultado de la revisión ──────────────────────────────
            $table->boolean('passed')->nullable();      // NULL = sin revisar, TRUE = OK, FALSE = fallo
            $table->boolean('has_incident')->default(false); // Si falla → abre incidencia obligatoria

            // ── Detalle del fallo ─────────────────────────────────────
            $table->text('notes')->nullable();          // Descripción del fallo si has_incident = true
            $table->string('photo_url')->nullable();    // Foto evidencia del fallo
            $table->string('photo_disk')->default('local');

            $table->timestamp('checked_at')->nullable(); // Cuándo se revisó este ítem

            $table->timestamps();

            // ── Índices ───────────────────────────────────────────────
            $table->index('workshop_order_id');
            $table->index('has_incident');              // Para detectar órdenes con fallos rápido
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workshop_checklist_items');
    }
};
