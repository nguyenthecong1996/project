<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Location;
use App\Jobs\SendEmailJob;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller
{
    protected $user;
    public function __construct(User $user) {
        $this->user = $user;
    }

    public function register(Request $request){
        $checkUser = $this->user->where('email', $request->email)->first();
        if($checkUser) {
            return response()->json([
                'message' =>  'Email number exist',
            ], 200);
        }
        $code = mt_rand(1000, 9999);  
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'code' => $code
        ];
        $user = $this->user->create($data);
        dispatch(new SendEmailJob($request->email, $code));
        return response()->json([
            'message' =>  $user,
        ], 200);

    }

    public function verify(Request $request)
    {
        $checkUser = $this->user->where('email', $request->email)->first();
        if (!$checkUser) {
            return response()->json([
                'message' =>  'Email incorrect',
            ], 200);
        }

        if($checkUser->code != $request->code) {
            return response()->json([
                'message' =>  'Code incorrect',
            ], 200);
        }

        $checkUser->update([
            'status' => 1
        ]);

        $success['token'] =  'Bearer '. $checkUser->createToken('MyApp')->accessToken;
        $success['user'] =   $checkUser;
   
        return $this->sendResponse($success, 'User register successfully.');

    }

    public function sendResponse($result, $message)
    {
    	$response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];


        return response()->json($response, 200);
    }

    public function createToken($user)
    {
        $tokenResult = $user->createToken('RG9uc3NUUU9JQzVUcUs0ZGNMcFpjRG8yaFZjS3BEMXA=');
        $token =  $tokenResult->accessToken;
        return $token;
    }

    public function sendError($error, $errorMessages = [], $code = 404)
    {
    	$response = [
            'success' => false,
            'message' => $error,
        ];


        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }


        return response()->json($response, $code);
    }

    public function login(Request $request)
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $user = Auth::user(); 
            if ($user->status == 0) {
                return response()->json([
                    'message' =>  'Email not verify',
                ], 404);
            }
            $success['token'] =  'Bearer '. $user->createToken('MyApp')->accessToken; 
            $success['name'] =  $user;
    
            return $this->sendResponse($success, 'User login successfully.');
        } 
        else{ 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        } 
    }

    public function addLocation(Request $request)
    {
        $user = $request->user();
        $check = $user->locations()->where('type', $request->type)->first();

        if($check && $check->type == 0) {
            return response()->json([
                'message' =>  "location exist",
            ], 404);
        }
        $location = Location::create([
            'type' => $request->type,
            'long' => $request->long,
            'lat' => $request->lat,
            'user_id' => $user->id,
        ]);
        return response()->json([
            'data' =>  $location,
        ], 200);
    }

    public function updateLocation(Request $request)
    {
        $location = Location::find($request->location_id);

        if(!$location) {
            return response()->json([
                'message' =>  "location exist",
            ], 404);
        }
        $location->update([
            'long' => $request->long,
            'lat' => $request->lat,
        ]);
        return response()->json([
            'data' =>  $location,
        ], 200);
    }

    public function recode(Request $request)
    {
        $checkUser = $this->user->where('email', $request->email)->first();
        if (!$checkUser) {
            return response()->json([
                'message' =>  'Email incorrect',
            ], 200);
        }

        $code = mt_rand(1000, 9999);  
        $data = [
            'code' => $code
        ];
        $user = $checkUser->update($data);
        dispatch(new SendEmailJob($request->email, $code));

        return response()->json([
            'message' => $this->user->where('email', $request->email)->first(),
        ], 200);
        
    }

}
