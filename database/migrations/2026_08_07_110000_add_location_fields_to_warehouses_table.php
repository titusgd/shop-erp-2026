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
        Schema::table('warehouses', function (Blueprint $table) {
            $table->string('postal_code', 20)->nullable()->after('email');
            $table->foreignId('city_id')->nullable()->after('postal_code')->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->after('city_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('district_id');
            $table->dropConstrainedForeignId('city_id');
            $table->dropColumn('postal_code');
        });
    }
};
