<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CitySeeder::class,
            DistrictSeeder::class,
            VendorSeeder::class,
            CustomerSeeder::class,
            ProductCategorySeeder::class,
            ProductUnitSeeder::class,
            ProductSeeder::class,
            WarehouseTypeSeeder::class,
            WarehouseSeeder::class,
            PurchaseRequisitionSeeder::class,
            PurchaseOrderSeeder::class,
        ]);
    }
}
