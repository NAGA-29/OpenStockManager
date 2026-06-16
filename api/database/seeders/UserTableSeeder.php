<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use DateTime;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Sample User',
            'email' => "user@demo.com",
            'password' => Hash::make('@user1234'),
            'role' => 'user',
            'created_at' => new DateTime(),
            'updated_at' => new DateTime(),
        ]);
        DB::table('users')->insert([
            'name' => 'Admin User',
            'email' => "admin@demo.com",
            'password' => Hash::make('@admin1234'),
            'role' => 'admin',
            'created_at' => new DateTime(),
            'updated_at' => new DateTime(),
        ]);
    }
}
