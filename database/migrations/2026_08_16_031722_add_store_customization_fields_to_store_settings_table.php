<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->string('store_name')->nullable();
            $table->string('store_slogan')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('color_primary')->default('#42AAAB');
            $table->string('color_secondary')->default('#358C8D');
            $table->string('instagram_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('tiktok_url')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('address')->nullable();
            $table->string('business_hours')->nullable();

            $table->string('pago_movil_bank')->nullable();
            $table->string('pago_movil_phone')->nullable();
            $table->string('pago_movil_cedula_rif')->nullable();
            $table->string('pago_movil_holder_name')->nullable();
            $table->string('transferencia_bank')->nullable();
            $table->string('transferencia_account_number')->nullable();
            $table->string('transferencia_cedula_rif')->nullable();
            $table->string('transferencia_holder_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn([
                'store_name', 'store_slogan', 'logo_path', 'color_primary', 'color_secondary',
                'instagram_url', 'facebook_url', 'tiktok_url', 'whatsapp_number',
                'contact_email', 'contact_phone', 'address', 'business_hours',
                'pago_movil_bank', 'pago_movil_phone', 'pago_movil_cedula_rif', 'pago_movil_holder_name',
                'transferencia_bank', 'transferencia_account_number', 'transferencia_cedula_rif', 'transferencia_holder_name',
            ]);
        });
    }
};
