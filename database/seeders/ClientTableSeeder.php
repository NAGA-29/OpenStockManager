<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use DateTime;

class ClientTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i=1;$i<20;$i++){
            DB::table('clients')->insert([
                'client_id' => "DDD${i}",
                'company' => "株式会社${i}",
                'url' => "https://sample${i}",
                'tel' => "0801111111${i}",
                'street_address' => "東京都新宿区${i}丁目${i}番地",
                'note' => "test${i}",
                "created_at" => new DateTime(),
                'modified_at' => new DateTime(),
            ]);
        }
    }
}
