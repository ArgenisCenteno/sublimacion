<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('provider_id')
                ->nullable()
                ->constrained('providers')
                ->nullOnDelete();

            $table->foreignId('uploaded_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('invoice_number')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->decimal('subtotal_amount', 10, 2)->nullable();
            $table->decimal('vat_amount', 10, 2)->nullable();
            $table->date('issued_at')->nullable();

            $table->string('pdf_url')->nullable();
            $table->string('albaran_url')->nullable();
            $table->string('disk')->default('local');
            $table->string('original_filename')->nullable();

            $table->text('ocr_raw_text')->nullable();
            $table->enum('ocr_status', ['pendiente', 'procesado', 'error'])->default('pendiente');

            $table->enum('status', ['pendiente', 'procesada', 'descargada'])->default('pendiente');

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('provider_id');
            $table->index('ocr_status');
            $table->index('status');
            $table->index('issued_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
