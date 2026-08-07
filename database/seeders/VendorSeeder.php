<?php

namespace Database\Seeders;

use App\Models\Vendor;
use App\Services\VendorService;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendors = app(VendorService::class);

        $vendors->create([
            'name' => '台塑企業',
            'tax_id' => '12345675',
            'contact_name' => '王經理',
            'phone' => '0912345678',
            'email' => 'contact@example.com',
            'address' => '台北市信義區信義路五段7號',
            'notes' => '示範廠商資料',
            'is_active' => true,
        ]);

        Vendor::factory()->count(5)->create();
    }
}
