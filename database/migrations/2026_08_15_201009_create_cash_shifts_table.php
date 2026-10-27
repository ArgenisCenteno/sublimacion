<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->decimal('opening_cash_usd', 10, 2)->default(0);
            $table->decimal('opening_cash_bs', 12, 2)->default(0);
            $table->decimal('counted_cash_usd', 10, 2)->nullable();
            $table->decimal('counted_cash_bs', 12, 2)->nullable();
            $table->decimal('expected_cash_usd', 10, 2)->nullable();
            $table->decimal('expected_cash_bs', 12, 2)->nullable();
            $table->decimal('difference_usd', 10, 2)->nullable();
            $table->decimal('difference_bs', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['abierto', 'cerrado'])->default('abierto');
            $table->timestamp('reopened_at')->nullable();
            $table->foreignId('reopened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reopen_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_shifts');
    }
};
