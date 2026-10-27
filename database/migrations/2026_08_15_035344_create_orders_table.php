<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('canal', ['online', 'tienda_fisica'])->default('online');
            $table->enum('status', [
                'pendiente_pago',
                'pago_parcial',
                'pago_confirmado',
                'en_preparacion',
                'enviado',
                'entregado',
                'cancelado',
            ])->default('pendiente_pago');
            $table->enum('delivery_type', ['retiro_tienda', 'envio_local', 'envio_nacional'])->nullable();
            $table->string('shipping_carrier')->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email');
            $table->string('customer_cedula_rif')->nullable();
            $table->string('payment_method');
            $table->decimal('subtotal_usd', 10, 2);
            $table->decimal('shipping_cost_usd', 10, 2)->default(0);
            $table->decimal('total_usd', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
