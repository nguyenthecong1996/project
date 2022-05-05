<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
class AuthController extends Controller
{
    protected $user;
    public function __construct(User $user) {
        $this->user = $user;
    }

    public function register(Request $request){
        $checkUser = $this->user->where('phone_number', $request->phone_number)->first();
        if($checkUser) {
            return response()->json([
                'message' =>  'phone number exist',
            ], 200);
        }
        $data = [
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'password' => bcrypt($request->password),
        ];
        $user = $this->user->create($data);

        // dispatch(new SendEmailJob(Ultilities::clearXSS($request->email), $lang, $code, User::VERIFY_ACCOUNT_TYPE));
        return response()->json([
            'message' =>  $user,
        ], 200);

        // $code = mt_rand(1000, 9999); 
    }

}
