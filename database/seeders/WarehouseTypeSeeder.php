<?php

namespace Database\Seeders;

use App\Models\WarehouseType;
use App\Services\WarehouseTypeService;
use Illuminate\Database\Seeder;

class WarehouseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = app(WarehouseTypeService::class);

        $defaults = [
            ['name' => '總倉', 'notes' => '主要集中倉儲'],
            ['name' => '門市倉', 'notes' => '門市現場庫存'],
            ['name' => '暫存倉', 'notes' => '短期暫存用途'],
            ['name' => '退貨倉', 'notes' => '退貨商品存放'],
            ['name' => '寄售倉', 'notes' => '寄售商品存放'],
        ];

        foreach ($defaults as $type) {
            $types->create([
                ...$type,
                'is_active' => true,
            ]);
        }

        WarehouseType::factory()->count(2)->create();
    }
}
