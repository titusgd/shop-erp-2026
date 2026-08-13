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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('estimated_purchase_price', 14, 2)->nullable()->after('notes');
            $table->decimal('estimated_selling_price', 14, 2)->nullable()->after('estimated_purchase_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['estimated_purchase_price', 'estimated_selling_price']);
        });
    }
};
