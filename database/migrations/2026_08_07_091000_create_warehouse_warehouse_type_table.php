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
        Schema::create('warehouse_warehouse_type', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_type_id')->constrained()->cascadeOnDelete();

            $table->primary(['warehouse_id', 'warehouse_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_warehouse_type');
    }
};
