<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    private const TOTAL = 33;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        $users = [
            [
                'name' => '系統管理員',
                'username' => 'admin',
                'email' => 'admin@example.com',
            ],
            [
                'name' => '店長',
                'username' => 'manager',
                'email' => 'manager@example.com',
            ],
            [
                'name' => '店員甲',
                'username' => 'staff01',
                'email' => 'staff01@example.com',
            ],
            [
                'name' => '店員乙',
                'username' => 'staff02',
                'email' => 'staff02@example.com',
            ],
            [
                'name' => '測試使用者',
                'username' => 'test',
                'email' => 'test@example.com',
            ],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'username' => $user['username'],
                    'password' => $password,
                    'email_verified_at' => now(),
                ],
            );
        }

        $remaining = self::TOTAL - User::query()->count();

        if ($remaining > 0) {
            User::factory()
                ->count($remaining)
                ->create([
                    'password' => $password,
                ]);
        }
    }
}
