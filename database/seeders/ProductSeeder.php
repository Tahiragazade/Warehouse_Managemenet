<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('products')->insert([
            'name' => 'Product_1',
            'category_id'=>1,
            'price'=>30,
            'sale_price'=>45,
        ]);
        DB::table('products')->insert([
            'name' => 'Product_2',
            'category_id'=>2,
            'price'=>20,
            'sale_price'=>40,
        ]);
        DB::table('products')->insert([
            'name' => 'Product_3',
            'category_id'=>3,
            'price'=>15,
            'sale_price'=>35,
        ]);
    }
}
