<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function get()
    {
        $products = Products::all();
      
        $response = [
            'status'=> 'success',
            'products'=>$products
        ]; 

        return response()->json($response,200);
    }


}
