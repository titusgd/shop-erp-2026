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
        Schema::create('product_vendor', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();

            $table->primary(['product_id', 'vendor_id']);
        });

        if (Schema::hasColumn('products', 'vendor_id')) {
            $rows = DB::table('products')
                ->whereNotNull('vendor_id')
                ->get(['id', 'vendor_id']);

            foreach ($rows as $row) {
                DB::table('product_vendor')->insert([
                    'product_id' => $row->id,
                    'vendor_id' => $row->vendor_id,
                ]);
            }

            Schema::table('products', function (Blueprint $table) {
                $table->dropConstrainedForeignId('vendor_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('vendor_id')
                ->nullable()
                ->after('product_unit_id')
                ->constrained()
                ->nullOnDelete();
        });

        $firstVendors = DB::table('product_vendor')
            ->select('product_id', DB::raw('MIN(vendor_id) as vendor_id'))
            ->groupBy('product_id')
            ->get();

        foreach ($firstVendors as $row) {
            DB::table('products')
                ->where('id', $row->product_id)
                ->update(['vendor_id' => $row->vendor_id]);
        }

        Schema::dropIfExists('product_vendor');
    }
};
