<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = app(CustomerService::class);

        $customers->create([
            'name' => '全聯福利中心',
            'tax_id' => '22662269',
            'contact_name' => '林經理',
            'phone' => '0911222333',
            'email' => 'customer@example.com',
            'address' => '忠孝東路四段45號',
            'notes' => '示範客戶資料',
            'is_active' => true,
        ]);

        Customer::factory()->count(5)->create();
    }
}