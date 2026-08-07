<?php

namespace Database\Seeders;

use App\Models\City;
use App\Services\DistrictService;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $districts = app(DistrictService::class);

        $defaults = [
            '臺北市' => ['中正區', '大同區', '中山區', '松山區', '大安區', '萬華區', '信義區', '士林區', '北投區', '內湖區', '南港區', '文山區'],
            '新北市' => ['板橋區', '三重區', '中和區', '永和區', '新莊區', '新店區', '樹林區', '鶯歌區', '三峽區', '淡水區'],
            '高雄市' => ['新興區', '前金區', '苓雅區', '鹽埕區', '鼓山區', '旗津區', '前鎮區', '三民區', '左營區', '楠梓區'],
        ];

        foreach ($defaults as $cityName => $districtNames) {
            $city = City::query()->where('name', $cityName)->first();

            if ($city === null) {
                continue;
            }

            foreach ($districtNames as $districtName) {
                $districts->create([
                    'city_id' => $city->id,
                    'name' => $districtName,
                    'is_active' => true,
                ]);
            }
        }
    }
}
