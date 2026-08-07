<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use App\Services\WarehouseService;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouses = app(WarehouseService::class);

        $defaults = [
            [
                'name' => '總倉',
                'contact_name' => '張倉管',
                'phone' => '02-2345-6789',
                'email' => 'main-warehouse@example.com',
                'address' => '台北市中正區忠孝東路一段1號',
                'notes' => '主要出貨倉庫',
            ],
            [
                'name' => '北區物流倉',
                'contact_name' => '李小姐',
                'phone' => '03-1234-5678',
                'email' => 'north-warehouse@example.com',
                'address' => '桃園市龜山區復興一路100號',
                'notes' => '北區備貨倉庫',
            ],
            [
                'name' => '南區物流倉',
                'contact_name' => '王先生',
                'phone' => '07-3456-7890',
                'email' => 'south-warehouse@example.com',
                'address' => '高雄市前鎮區成功二路88號',
                'notes' => '南區備貨倉庫',
            ],
        ];

        foreach ($defaults as $warehouse) {
            $warehouses->create([
                ...$warehouse,
                'is_active' => true,
            ]);
        }

        Warehouse::factory()->count(3)->create();
    }
}
