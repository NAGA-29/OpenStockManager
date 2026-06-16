<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call(DeviceCategorySeeder::class);
        $this->call(UserTableSeeder::class);
        $this->call(DevicesTableSeeder::class);
        $this->call(ClientTableSeeder::class);
        $this->call(ContactTableSeeder::class);
        $this->call(RentalHistTableSeeder::class);
        $this->call(DeviceRentalTableSeeder::class);
        $this->call(SaleHistTableSeeder::class);
        $this->call(DeviceSaleTableSeeder::class);
    }
}
