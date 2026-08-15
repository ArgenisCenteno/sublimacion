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
        Schema::table('pagos', function (Blueprint $table) {
            $table->enum('estado', ['activo', 'anulado', 'parcial'])->default('activo')->after('monto_usd');
            $table->foreignId('anulado_by')->nullable()->constrained('users')->nullOnDelete()->after('estado');
            $table->timestamp('anulado_at')->nullable()->after('anulado_by');
        });
    }
 
    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropForeign(['anulado_by']);
            $table->dropColumn(['estado', 'anulado_by', 'anulado_at']);
        });
    }
};
