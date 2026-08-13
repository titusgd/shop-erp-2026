<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('remittance_bank')->nullable()->after('notes');
            $table->string('remittance_account', 50)->nullable()->after('remittance_bank');
            $table->string('settlement_method', 32)->nullable()->after('remittance_account');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['remittance_bank', 'remittance_account', 'settlement_method']);
        });
    }
};
