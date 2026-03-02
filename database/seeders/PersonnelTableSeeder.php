<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use DateTime;

class PersonnelTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i=1;$i<20;$i++){
            DB::table('personnels')->insert([
                'personnel_id' => "HHH${i}",
                'client_id' => "DDD${i}",
                'name' => "佐藤${i}",
                'tel' => "0801111111${i}",
                'email' => "test_mail${i}@sample.com",
                'note' => "${i}test${i}",
                "created_at" => new DateTime(),
                'modified_at' => new DateTime()
            ]);
        }
    }
}
