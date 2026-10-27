<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_deposit_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount_usd', 10, 2);
            $table->decimal('amount_bs', 12, 2)->nullable();
            $table->decimal('exchange_rate', 10, 4)->nullable();
            $table->enum('method', ['pago_movil', 'transferencia', 'efectivo', 'paypal']);
            $table->string('reference')->nullable();
            $table->string('receipt_path')->nullable();
            $table->string('paypal_order_id')->nullable();
            $table->string('paypal_transaction_id')->nullable();
            $table->enum('status', ['pendiente', 'verificado', 'rechazado'])->default('pendiente');
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->date('paid_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
