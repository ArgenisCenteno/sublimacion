<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_deposits', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name');
            $table->decimal('amount_bs', 12, 2)->nullable();
            $table->decimal('amount_usd', 10, 2)->nullable();
            $table->string('reference')->nullable();
            $table->date('deposited_at');
            $table->foreignId('registered_by')->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_deposits');
    }
};
