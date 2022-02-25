<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class productseeder extends Controller
{
    public function seed(){
        DB::table('products')->insert(
            array(
                
                array(
                    'name' => 'book',
                    'price' => 15000,
                    'discount_price' => 13000,
                    'stock' => 40,

                ),
                array(
                    'name' => 'pencil',
                    'price' => 10000,
                    'discount_price' => 8000,
                    'stock' => 40,

                ),
                array(
                    'name' => 'cup',
                    'price' => 18000,
                    'discount_price' => 11000,
                    'stock' => 40,

                ),
                array(
                    'name' => 'phone',
                    'price' => 13000,
                    'discount_price' => 9000,
                    'stock' => 40,

                ),
                array(
                    'name' => 'pen',
                    'price' => 12000,
                    'discount_price' => 11000,
                    'stock' => 40,

                ),
                array(
                    'name' => 'chips',
                    'price' => 3000,
                    'discount_price' => 2000,
                    'stock' => 40,

                ),
                array(
                    'name' => 'tea',
                    'price' => 15000,
                    'discount_price' => 13000,
                    'stock' => 38,

                ),
                array(
                    'name' => 'coffe',
                    'price' => 10000,
                    'discount_price' => 5000,
                    'stock' => 30,

                ),
                array(
                    'name' => 'mango',
                    'price' => 14000,
                    'discount_price' => 12000,
                    'stock' => 10,

                ),
                array(
                    'name' => 'playstation',
                    'price' => 99000,
                    'discount_price' => 98000,
                    'stock' => 40,

                ),

            ),
        
        );

        return 'done';
    }
}
