<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orden_trabajo_id');
            $table->foreignId('user_id')->constrained()->restrictOnDelete(); // cajero
 
            $table->enum('divisa', ['USD', 'EUR', 'BS'])->default('USD');
            $table->decimal('monto_divisa', 12, 4);  // total pagado en esa divisa
            $table->decimal('tasa_cambio',  12, 4);  // tasa vigente al momento
            $table->decimal('monto_usd',    12, 4);  // equivalente total en USD
 
            $table->string('referencia')->nullable();   // referencia general del pago
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
 
        Schema::create('pago_metodos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_id')->constrained('pagos')->cascadeOnDelete();
 
            $table->enum('metodo', [
                'efectivo',
                'transferencia',
                'punto_de_venta',
                'zelle',
                'pago_movil',
                'otro',
            ]);
 
            $table->decimal('monto_divisa', 12, 4);  // porción en la divisa del pago padre
            $table->decimal('monto_usd',    12, 4);  // porción equivalente en USD
            $table->string('referencia')->nullable(); // referencia específica de este método
            $table->timestamps();
        });
 
        Schema::create('tasas_cambio', function (Blueprint $table) {
            $table->id();
            $table->enum('divisa', ['EUR', 'BS'])->unique();
            $table->decimal('tasa', 12, 4);
            $table->foreignId('updated_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('pago_metodos');
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('tasas_cambio');
    }
};
