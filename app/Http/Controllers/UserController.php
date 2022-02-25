<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use App\classes\createUser;
use App\classes\checkAddProducts;







class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

         //validate register user
         
               //validate data register user
               $validator = Validator::make($request->all(), [
                'email' => 'email|required',
                'name' => 'required',
                'password' => 'required|confirmed|min:6',
            ]);
    
            //if validator fails
            if ($validator->fails()) {
            return response()->json($validator->errors(), 404);
            }
        $res = createUser::register($request,'reg');
        return $res;
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
    

        
        $validator = Validator::make($request->all(), [
            'email' => 'email',
            'password' => 'required|min:6',
        ]);



        if ($validator->fails()) {
       
         
            return response()->json($validator->errors(), 403);
        }

        $inputs = $request ->all();

       

        $user = User::where('email',$inputs['email'])->first();
        if($user) {


            if (Hash::check( $inputs['password'], $user->password)) {
                //add product in session to login start

                $getcheckout = checkAddProducts::getCheckOut(0,$request);
                
                if($getcheckout !== null){
                        //create inputs product
                       
                        $inputsProductInsession = [];
                        foreach($getcheckout['items'] as $item){
                            $inp['id'] = $item['item']['id'];
                            $inp['quantity'] = $item['quantity'];
                            $inputsProductInsession[] = $inp;

                        }
                        $prjson = json_encode($inputsProductInsession);
                        //check stock product exist or not
                        $checkStockProducts = checkAddProducts::checkStockAddproduct(json_decode($prjson));
                        if($checkStockProducts['status'] == 0) {
                            $getcart = $checkStockProducts;
                            
                        }else{

                            $cart = checkAddProducts::addToCartUserLogin(json_decode($prjson),$request,$user);
                            $getcart = $cart;
                        }

               
                }else{
                    $getcart = null;
                }
                 //add product in session to login start end
                
           


                
                $token =  $user->createToken('token-name')->plainTextToken;
               
                $response = [
                    'user'=>$user,
                    'token'=> $token,
                    'status'=> 'success',
                    'checkout' =>$getcart
    
                ];
    
                return response()->json($response,200);
            }else{
                $response = [
                    'user'=>'incorrect password',
                    'token'=> NULL
        
                ];
                return response()->json($response,403);
            }
        }else{

            $response = [
                'user'=>"account don't exist",
                
            ];

            return response()->json($response,401);
        }
      
    }

  
}
