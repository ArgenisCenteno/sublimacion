<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workshop_checklist_templates', function (Blueprint $table) {
            $table->id();

            // Tipo de checklist al que pertenece esta plantilla.
            $table->enum('type', [
                'dab',
                'chapa',
                'limpieza',
            ])->default('dab');

            // Pregunta o punto de revision maestro.
            $table->string('label');
            $table->unsignedTinyInteger('sort_order')->default(0);

            // Permite activar/desactivar items sin perder historico.
            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->index(['type', 'active'], 'wct_type_active_idx');
            $table->unique(['type', 'label'], 'wct_type_label_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workshop_checklist_templates');
    }
};
