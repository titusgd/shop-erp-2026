<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_vendor', function (Blueprint $table) {
            $table->decimal('estimated_purchase_price', 14, 2)->nullable();
        });

        if (Schema::hasColumn('products', 'estimated_purchase_price')) {
            $products = DB::table('products')
                ->whereNotNull('estimated_purchase_price')
                ->get(['id', 'estimated_purchase_price']);

            foreach ($products as $product) {
                DB::table('product_vendor')
                    ->where('product_id', $product->id)
                    ->update([
                        'estimated_purchase_price' => $product->estimated_purchase_price,
                    ]);
            }

            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('estimated_purchase_price');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('products', 'estimated_purchase_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('estimated_purchase_price', 14, 2)
                    ->nullable()
                    ->after('notes');
            });
        }

        $firstPrices = DB::table('product_vendor')
            ->whereNotNull('estimated_purchase_price')
            ->select('product_id', DB::raw('MIN(estimated_purchase_price) as estimated_purchase_price'))
            ->groupBy('product_id')
            ->get();

        foreach ($firstPrices as $row) {
            DB::table('products')
                ->where('id', $row->product_id)
                ->update(['estimated_purchase_price' => $row->estimated_purchase_price]);
        }

        Schema::table('product_vendor', function (Blueprint $table) {
            $table->dropColumn('estimated_purchase_price');
        });
    }
};
