<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

use App\Http\Controllers\CartsController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\productseeder;




/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/productseeder',[productseeder::class,'seed']);
 Route::post('/register',[UserController::class,'create']);
 Route::post('/login',[UserController::class,'login']);
 Route::get('/products',[ProductsController::class,'get']);
 Route::get('/checkout/user',[CartsController::class,'getcheckout']);
 Route::post('/addtocart',[CartsController::class,'add']);

 Route::post('/order',[CartsController::class,'order']);
 











