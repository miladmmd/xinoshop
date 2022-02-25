<?php

namespace App\Classes;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;



class createUser {


    public static function register($request,$type)
    {
       
       
       
 

        //if email duplicate exist
        $user = User::where('email',$request['email'])->first();

        if($user) {
            $response = [
                'user'=> 'user duplicated',
                'status'=>'error'
            ]; 
            return response()->json($response,409);
        }

      
        //hash password
        $request['password'] = Hash::make($request['password']);

        if($type == 'InPayment'){
            $inputs = $request;
        }else{
            $inputs = $request->all();
        }
        

       
        //save user
        $user = User::create($inputs);

        if($user) {
            $response = [
                'status'=> 'success',
                'user'=>$user
            ]; 
    
            return response()->json($response,201);
        }
    
        
    }

}