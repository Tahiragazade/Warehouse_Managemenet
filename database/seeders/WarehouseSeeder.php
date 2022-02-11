<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('warehouses')->insert([
            'name' => 'warehouse_1',
            'types'=>1,
            'created_by'=>1
        ]);
        DB::table('warehouses')->insert([
            'name' => 'warehouse_2',
            'types'=>1,
            'created_by'=>1
        ]);
    }
}
