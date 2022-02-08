<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;



class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         $this->call(UserSeeder::class);
         $this->call(RoleSeeder::class);
         $this->call(UserRoleSeeder::class);
         $this->call(WarehouseSeeder::class);
         $this->call(CategorySeeder::class);
         $this->call(ProductSeeder::class);
    }
}
