<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('currency', ['USD', 'BS'])->nullable()->after('exchange_rate');
            $table->foreignId('cash_shift_id')->nullable()->after('bank_deposit_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cash_shift_id');
            $table->dropColumn('currency');
        });
    }
};
