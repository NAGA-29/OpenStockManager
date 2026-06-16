<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use DateTime;

class DeviceRentalTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 中間テーブル
     *
     * @return void
     */
    public function run()
    {
        for ($i = 1; $i <= 5; $i++) {
            DB::table('device_rental')->insert([
                'device_id' => "STB_device0${i}",
                'lend_id' => "RENT${i}",
                'checkout_at' => new DateTime(),
                'return_at' => $i % 2 === 0 ? new DateTime() : null,
            ]);
        }
    }
}
