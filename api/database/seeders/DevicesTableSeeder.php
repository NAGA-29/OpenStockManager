<?php

namespace Database\Seeders;
use App\devices;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use DateTime;

class DevicesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i=1;$i<=30;$i++){
            DB::table('devices')->insert([
                'device_id' => "STB_device0${i}",
                'device_type' => "STB",
                'device_name' => "S8",
                'device_serial' => 'abc' . (string)(123+$i*2),
                'custom_fields' => json_encode([
                    'os' => 'Android9',
                    'os_ver' => '9',
                ]),
                // 'first_work_at' => new DateTime(),
                // 'purchase_date_at' => new DateTime(),
                // 'client' => 'テスト株式会社'.(567 + $i * 2),
                // 'sale_date_at' => new DateTime(),
                // 'option' => 'テキスト',
                'defective' => False,
                'not_for_sale' => False,
                // 'note' => 'テキスト',
                'lending_now' => '',
                'using_user_id' => 1000 + $i,
                "created_at" => new DateTime(),
                'modified_at' => new DateTime(),
                // 'soft_deleted_at' => new DateTime(),
            ]);
        }
        for($i=1;$i<=20;$i++){
            DB::table('devices')->insert([
                'device_id' => "cam_device0${i}",
                'device_type' => "CAM",
                'device_name' => "S8",
                'device_serial' => 'abc' . (string)(321+$i*2),
                'custom_fields' => json_encode([
                    'os' => 'Android9',
                    'os_ver' => '9',
                ]),
                // 'first_work_at' => new DateTime(),
                // 'purchase_date_at' => new DateTime(),
                // 'client' => 'テスト株式会社'.(765 + $i * 2),
                // 'sale_date_at' => new DateTime(),
                // 'option' => 'テキスト',
                'defective' => False,
                'not_for_sale' => False,
                // 'note' => 'テキスト',
                'lending_now' => '',
                'using_user_id' => 1000 - $i,
                "created_at" => new DateTime(),
                'modified_at' => new DateTime(),
                // 'soft_deleted_at' => new DateTime(),
            ]);
        }
        for($i=1;$i<=20;$i++){
            DB::table('devices')->insert([
                'device_id' => "tab_device0${i}",
                'device_type' => "TAB",
                'device_name' => "AD10C",
                'device_serial' => 'abc' . (string)(675+$i*2),
                'custom_fields' => json_encode([
                    'os' => 'Android9',
                    'os_ver' => '9',
                ]),
                // 'first_work_at' => new DateTime(),
                // 'purchase_date_at' => new DateTime(),
                // 'client' => 'テスト株式会社'.(765 + $i * 2),
                // 'sale_date_at' => new DateTime(),
                // 'option' => 'テキスト',
                'defective' => False,
                'not_for_sale' => False,
                // 'note' => 'テキスト',
                'lending_now' => '',
                'using_user_id' => 1000 - $i,
                "created_at" => new DateTime(),
                'modified_at' => new DateTime(),
                // 'soft_deleted_at' => new DateTime(),
            ]);
        }
        for($i=1;$i<=20;$i++){
            DB::table('devices')->insert([
                'device_id' => "sign_device0${i}",
                'device_type' => 'SIGN',
                'device_name' => "たてナビ",
                'device_serial' => 'efg' . (string)(101+$i*2),
                'custom_fields' => json_encode([
                    'os' => 'Android9',
                    'os_ver' => '9',
                ]),
                // 'first_work_at' => new DateTime(),
                // 'purchase_date_at' => new DateTime(),
                // 'client' => 'テスト株式会社'.(765 + $i * 2),
                // 'sale_date_at' => new DateTime(),
                // 'option' => 'テキスト',
                'defective' => False,
                'not_for_sale' => False,
                // 'note' => 'テキスト',
                'lending_now' => '',
                'using_user_id' => 1000 - $i,
                "created_at" => new DateTime(),
                'modified_at' => new DateTime(),
                // 'soft_deleted_at' => new DateTime(),
            ]);
        }
    }
}
