<?php

namespace App\Classes;
use App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Products;
use App\Models\carts;
use App\Models\cart_items;


class checkAddProducts {

    public static function checkStockAddproduct($addProducts){
        
   
       
        // check quantity with stock
        $selectedProducts = [];
        $resProduct['status'] = NULL;
        foreach($addProducts as $addProduct){

            //get product selected
            $product = Products::find($addProduct->id);

            //check exist product and stock product
            if($product){

                $stockres = $product->stock - $addProduct->quantity;

                if($stockres >= 0){

                }else{
                    $response = [
                        'status'=> 0,
                        'msg'=>'out of stock'
                    ]; 
                    return $response;
            
                    
                }

            }else{

                $response = [
                    'status'=> 0,
                    'msg'=>'false id'
                ]; 
                return $response;
        
              
            }

            array_push($selectedProducts,$product);

        }

        $resProduct['status'] = 1;
        $resProduct['selected'] = $selectedProducts;
        return $resProduct;
    
    }

    public static function addToCartSession($selectedProducts,$request){
        
        //check session is empty or not
        if($request->session()->has('cart')){
            $productsession = $request->session()->get('cart');
            //loop to selected product and comarison with session product
            
            foreach($selectedProducts as $spr){
                
                $detect = 0;
                //loop to session product
                foreach($productsession['items'] as $key=>$val){
                    //if selected product exist in session product check quantity and increase detect var
                    if($val['product_id'] == $spr->id ){
                            //if quantity session product not equal to selected product change quanity session product with selected proiduct  
                            if($val['quantity'] !== $spr->quantity){
                                //if selected product quantity was zero  remove this item from sessin
                                if($spr->quantity == 0){
                                   unset($productsession['items'][$key]);
                                   $productsession['items'] = array_values($productsession['items']);
                                }else{
                                    $productsession['items'][$key]['quantity'] = $spr->quantity;
                                }
                                
                                
                            }
                           
                        $detect+=1;
                        
                    }
                }

                // if detect = 0 means new product exist in selected product and this product will added to session product

                if($detect == 0) {
                    if($spr->quantity>0){
                        $newitem['cart_id'] = 0;
                        $newitem['product_id'] = $spr->id;
                        $newitem['quantity'] = $spr->quantity;
                        $productsession['items'][] = $newitem;
                    }
         
                }else{
                    $detect = 0;
                }
            }

            //check product item was zero empty this
            if(count($productsession['items']) == 0){

                $request->session()->forget('cart');
                return NULL;
            }else{
                //update session
                $request->session()->put('cart',$productsession);
                return $productsession;
            }

        }else{

        //create cart obj 
        $cart['id'] = 0;
        $cart['user_id'] = 0;
  
        //create cart items
        $cart_items = [];
        foreach($selectedProducts as $sp){
            $cart_item['cart_id'] = 0;
            $cart_item['product_id'] = $sp->id;
            $cart_item['quantity'] = $sp->quantity;
            array_push($cart_items,$cart_item);
        }

        //add items to cart obj
         $cart['items'] = $cart_items;
         $request->session()->put('cart',$cart);

        }
      
        return $cart;

    }

    public static function addToCartUserLogin($selectedProducts,$request,$user){
     
        $cartsessions = self::addToCartSession($selectedProducts,$request);
      

        //check add to cart exist in database
        $cart = carts::where('user_id',$user->id)->first();

       
        if($cart){
             
        }else{
            $cart = carts::create(['user_id'=>$user->id]);
        }

        

        // $itemsDb = cart_items::where('cart_id',$cart->id)->get();

            $itemsc=[];
            foreach($cartsessions['items'] as $item){
                $itemsdb = cart_items::where('cart_id',$cart->id)->where('product_id',$item['product_id'])->where('status',0)->first();
                
                if($itemsdb){
                   
                    if($item['quantity']>0){

                        $itemsdb->update(['quantity'=>$item['quantity']]);
                        $itemsc[]=$itemsdb;
                    }elseif($item['quantity'] == 0){
                        $itemsdb->delete();
                    }
                    
                }else{
                   
                    if($item['quantity']>0){
                        $inputitem['quantity'] = $item['quantity'];
                        $inputitem['product_id'] = $item['product_id'];
                        $inputitem['cart_id'] = $cart->id;
                        $itemC = cart_items::create($inputitem);
        
                        $itemsc[]=$itemC;
                    }
                  
                }

           
    
           
            }

    
            $cart['items'] = cart_items::where('cart_id',$cart->id)->where('status',0)->get();

        

   

        if($cart){
            $request->session()->forget('cart');
        }



        return $cart;


    }

    public static function getCheckOut($userid,$request){
        
        if($userid == 0){
            $cart = $request->session()->get('cart');
       
            
            if($cart == null){
         
    
                return $cart;
            }
            
           
            $cartitems= $cart['items'];
            

            $products = [];
            foreach($cartitems as $item){
               $infoitem= Products::find($item['product_id']);
               $info['quantity'] = $item['quantity'];
               $info['cart_item_id'] = 0;
               $info['item'] = $infoitem;
               $products[] = $info;
              
            }
            //create check out
            $sumtotal_discount = 0;
            $sumtotal = 0;
            foreach($products as $product){
                $discountprice = $product['item']['discount_price'] * $product['quantity'];
                $price = $product['item']['price'] * $product['quantity'];
                $sumtotal_discount += $discountprice;
                $sumtotal += $price;
            }

            $checkOut['items'] = $products;
            $checkOut['total'] = $sumtotal;
            $checkOut['discount'] = $sumtotal - $sumtotal_discount ;
            $checkOut['payment'] = $sumtotal_discount;
            return $checkOut;

            

        }else{

            
            $cart = carts::where('user_id',$userid)->first();
           

            if($cart){
                $cartitems = cart_items::where('cart_id',$cart->id)->where('status',0)->get();
             
            
                $products = [];
                foreach($cartitems as $item){
                   $infoitem= Products::find($item['product_id']);
                   $info['quantity'] = $item['quantity'];
               
                   $info['cart_item_id'] = $item['id'];
                   $info['item'] = $infoitem;
                   $products[] = $info;
                }
            
                 //create check out
                $sumtotal_discount = 0;
                $sumtotal = 0;
                // foreach($products as $product){
                //     $sumtotal_discount += $product['discount_price'];
                //     $sumtotal += $product['price'];
                // }

                foreach($products as $product){
                    $discountprice = $product['item']['discount_price'] * $product['quantity'];
                    $price = $product['item']['price'] * $product['quantity'];
                    $sumtotal_discount += $discountprice;
                    $sumtotal += $price;
                }

                $checkOut['items'] = $products;
                $checkOut['total'] = $sumtotal;
                $checkOut['discount'] = $sumtotal - $sumtotal_discount ;
                $checkOut['payment'] = $sumtotal_discount;
                return $checkOut;
    
            }

            
        }
    }






}

