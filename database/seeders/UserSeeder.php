<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'created_by'=>1
        ]);
        DB::table('users')->insert([
            'name' => 'user_1',
            'email' => 'user1@test.com',
            'password' => Hash::make('password'),
            'created_by'=>1
        ]);
        DB::table('users')->insert([
            'name' => 'user_2',
            'email' => 'user2@test.com',
            'password' => Hash::make('password'),
            'created_by'=>1
        ]);
    }
}
