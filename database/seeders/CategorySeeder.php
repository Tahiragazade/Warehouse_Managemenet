<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('categories')->insert([
            'name' => 'Category_1',
            'parent_id'=>null
        ]);
        DB::table('categories')->insert([
            'name' => 'Category_2',
            'parent_id'=>null
        ]);
        DB::table('categories')->insert([
            'name' => 'Category_3',
            'parent_id'=>1
        ]);
    }
}
