<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use DateTime;

class SaleHistTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i=1;$i<10;$i++){
            DB::table('sale_hists')->insert([
                'sale_id' => "SALE${i}",
                'client' => "DDD${i}",
                'personnel' => "HHH${i}",
                'staff' => 1,
                'sale_date_at' => new DateTime(),
                'note' => "test${i}だ",
                'created_at' => new DateTime(),
                'modified_at' => new DateTime(),
            ]);
        }
    }
}
