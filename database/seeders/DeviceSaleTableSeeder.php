<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use DateTime;

class DeviceSaleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 中間テーブル
     *
     * @return void
     */
    public function run()
    {
        for($i=1;$i<10;$i++){
            DB::table('device_sale')->insert([
                'device_id' => "tab_device0${i}",
                'sale_id' => "SALE${i}",
                'sale_date_at' => new DateTime(),
                // 'return_at' => new DateTime()
            ]);
        }
    }
}
