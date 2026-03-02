<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeviceCategory;

class DeviceCategorySeeder extends Seeder
{
    /**
     * 既存の5つの機材種別をdevice_categoriesテーブルへシード
     */
    public function run(): void
    {
        $categories = [
            ['code' => 'STB',  'name' => 'STB',        'icon' => 'fa-tv',         'sort_order' => 1],
            ['code' => 'TAB',  'name' => 'タブレット',   'icon' => 'fa-tablet-alt', 'sort_order' => 2],
            ['code' => 'CAM',  'name' => 'カメラ',      'icon' => 'fa-camera',     'sort_order' => 3],
            ['code' => 'SIGN', 'name' => 'サイネージ',   'icon' => 'fa-desktop',    'sort_order' => 4],
            ['code' => 'OTH',  'name' => 'その他機材',   'icon' => 'fa-cogs',       'sort_order' => 5],
        ];

        foreach ($categories as $category) {
            DeviceCategory::updateOrCreate(
                ['code' => $category['code']],
                $category
            );
        }
    }
}
