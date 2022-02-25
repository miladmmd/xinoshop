<?php
namespace App\Classes;
use App\Models\Products;
use App\Models\cart_items;
class checkPaymentMinusStock {

    public  $paymentSatatus;
    public  $checkout;
    
    function __construct($paymentSatatus,$checkout){
        $this->paymentSatatus = $paymentSatatus;
        $this->checkout = $checkout;
    }



   function finalResult() {

        if($this->paymentSatatus == 'ok') {
            $checkout = $this->checkout;
         
            //minus quanntity of product from product success purchase
          
            foreach($checkout['items'] as $item){
                //update quantity
                $itemQuantity = $item['quantity'];
                $itemProductId = $item['item']['id'];
                $prdb = Products::find($itemProductId);
                $newstock = $prdb['stock']-$itemQuantity;
                $prdb->update(['stock'=>$newstock]);

                //update cart items(change status from 0 to 1 to romove from cart item) 
                $cartitem = cart_items::find( $item['cart_item_id']);
                $cartitem->update(['status'=>1]);
                

                if($prdb->update(['stock'=>$newstock])){
                    $prs[] = $prdb;
                }

            }
            
            $res['message'] = 'Your purchase was successful';
            $res['orders'] =  $checkout['items'];
            return  $res;
        }else{
            $res['message'] = 'something wrong please try again';
            return $res;
        }

    }

}