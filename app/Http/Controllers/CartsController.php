<?php

namespace App\Http\Controllers;

use App\Models\carts;
use App\Models\cart_items;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\classes\checkAddProducts;
use App\classes\createUser;
use App\classes\checkPaymentMinusStock;
use Illuminate\Support\Str;
use Mockery;


class CartsController extends Controller
{


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function add(Request $request)
    {
      
        $inputs = $request->all();

        $inputs['products'] = json_decode($inputs['products']);
       
           
        // check quantity with stock
        $resheckAddProducts = checkAddProducts::checkStockAddproduct($inputs['products']);
     
        if($resheckAddProducts['status'] == 0) {
            return response()->json($resheckAddProducts,200);
            
        }

        
       
        //check quest user or not

        $user = auth('sanctum')->user();
        
        if($user){
           
            //check cart and add product selected to cart items
       
            $cart = checkAddProducts::addToCartUserLogin($inputs['products'],$request,$user);
            return $cart;
            
           


        }else{

            $cart=checkAddProducts::addToCartSession($inputs['products'],$request);
            

            return $cart;

            
            
            // return $get_session_product;
            
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getcheckout(Request $request)
    {

        $user = auth('sanctum')->user(); 
      
       
        if($user){
            
         
            $getcheck = checkAddProducts::getCheckOut($user->id,$request);
           
            $response = [
                'status'=> 'success',
                'checkout'=>$getcheck,
            ];

            return response()->json($response,200);

        }else{
            
            $getcheck = checkAddProducts::getCheckOut(0,$request);
            $response = [
                'status'=> 'success',
                'checkout'=>$getcheck,
            ];

            return response()->json($response,200);
            
        }
    }



    public function order(Request $request){

      
        $user = auth('sanctum')->user(); 
        $inputs = $request->all();
        

       
           
            
            //get check out

            //check card is empty or not if user or guest user
            $getcheckout = checkAddProducts::getCheckOut(0,$request);
            //if guest user
            if(!$user){
                if($getcheckout == null) {
                    $response = [
                        'status'=> 'error',
                        'checkout'=>'empty card',
                    ];
        
                    return response()->json($response,200);
                }
            }else{
                $cart = carts::where('user_id',$user->id)->first();
                $cartitems = cart_items::where('cart_id',$cart->id)->where('status',0)->get();
                if($getcheckout == null && count($cartitems) == 0){
                    $response = [
                        'status'=> 'error',
                        'checkout'=>'empty card',
                    ];
        
                    return response()->json($response,200);
                }


            }
         
       
       
            //create inputs product

            if($getcheckout !== null){
                $inputsProductInsession = [];
                foreach($getcheckout['items'] as $item){
                    $inp['id'] = $item['item']['id'];
                    $inp['quantity'] = $item['quantity'];
                    $inputsProductInsession[] = $inp;
    
                }
    
                $prjson = json_encode($inputsProductInsession);
            }

          
            //createuser

            if(!$user){
                    //validate data order guest user
                    $validator = Validator::make($request->all(), [
                        'email' => 'email|required',
                        'name' => 'required',
                        'password' => 'required|confirmed|min:6',
                    ]);
            
                    //if validator fails
                    if ($validator->fails()) {
                    $t = $validator->errors();   
                    return response()->json($validator->errors(), 404);
                    }
                    $user = createUser::register($inputs,'InPayment');
                    $userobj = json_decode($user->getContent());
                    if($userobj->status == 'error'){
                        return $user;
                    }

                            //check stock is enough
           
                    $checkStockProducts = checkAddProducts::checkStockAddproduct(json_decode($prjson));
                    if($checkStockProducts['status'] == 0) {
                        return response()->json($checkStockProducts,200);
                        
                    }
                        //add cart to database
                    $cart = checkAddProducts::addToCartUserLogin(json_decode($prjson),$request,$userobj->user);
                    
                    
            }else{
                    

                    //if session cart not empty add new cart item to old item
                    if($getcheckout !== null){
                        //check stock is enough
                        $checkStockProducts = checkAddProducts::checkStockAddproduct(json_decode($prjson));
                        if($checkStockProducts['status'] == 0) {
                            return response()->json($checkStockProducts,200);
                            
                        }
                            //add cart to database
                        $cart = checkAddProducts::addToCartUserLogin(json_decode($prjson),$request,$user);
                    }else{
                        $cart['user_id'] = $user->id;
                    }
         
                

            }
            

        if($cart){
            //get checkout user 
            
            $checkout = checkAddProducts::getCheckOut($cart->user_id,$request);
         
            

           
        }


        
        //MockPaymentGateway:
        $mock =Mockery::mock(MockPaymentGateway::class);
        $mock->shouldReceive('paymentGateway')->once()->andReturn('ok');
        $paymentstatus = $mock->paymentGateway();
   

        $checkPaymentMinusStock = new checkPaymentMinusStock($paymentstatus,$checkout);


        $response = [
            'status'=> 'success',
            'checkout'=>$checkPaymentMinusStock->finalResult(),
        ];

        return response()->json($response,200);
       
     
        //response to user payment and minus stock produc

        
    }
}
