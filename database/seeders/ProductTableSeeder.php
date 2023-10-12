<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Product::create([
            "name" => "Kaos",
            "price" => 100000,
            "discount" => 10,
        ]);
        Product::create([
            "name" => "Celana",
            "price" => 200000,
            "discount" => 10,
        ]);
    }
}
