<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use DateTime;

class RentalHistTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 1; $i <= 5; $i++) {
            DB::table('rental_hists')->insert([
                'lend_id' => "RENT${i}",
                'client' => "DDD${i}",
                'contact' => $i,
                'staff' => 1,
                'all_returned' => $i % 2 === 0,
                'checkout_at' => new DateTime(),
                'schedule_return_at' => new DateTime(),
                'return_at' => $i % 2 === 0 ? new DateTime() : null,
                'note' => "rental note ${i}",
                'created_at' => new DateTime(),
                'modified_at' => new DateTime(),
            ]);
        }
    }
}
