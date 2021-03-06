<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Location;
use App\Jobs\SendEmailJob;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Twilio\Rest\Client;
class AuthController extends Controller
{
    protected $user;
    public function __construct(User $user) {
        $this->user = $user;
    }

    public function sms($receiverNumber, $code)
    {        
        // dd($receiverNumber, $code);
        $message = "Mã code của bạn là: ". $code;
        try {
  
            $account_sid = getenv("TWILIO_SID");
            $auth_token = getenv("TWILIO_TOKEN");
            $twilio_number = getenv("TWILIO_FROM");
  
            $client = new Client($account_sid, $auth_token);
            $client->messages->create($receiverNumber, [
                'from' => $twilio_number, 
                'body' => $message]);  
        } catch (Exception $e) {
            dd("Error: ". $e->getMessage());
        }
    }

    public function register(Request $request){
        $checkUser = $this->user->where('email', $request->email)->first();
        if($checkUser) {
            return response()->json([
                'message' =>  'Email number exist ',
            ], 200);
        }
        $code = mt_rand(1000, 9999);  
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => bcrypt($request->password),
            'code' => $code
        ];
        $this->sms($request->phone_number, $code);
        $user = $this->user->create($data);
        // dispatch(new SendEmailJob($request->email, $code));
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

    public function logout(){   
        if (Auth::check()) {
            Auth::user()->token()->revoke();
            return response()->json(['success' =>'logout_success'],200); 
        }else{
            return response()->json(['error' =>'api.something_went_wrong'], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $data = [];
        if(isset($request->name)) {
            $data['name'] = $request->name;
        }

        if(isset($request->phone_number)) {
            $data['phone_number'] = $request->phone_number;
        }
        $user->update($data);
        return response()->json([
            'data' => $user,
        ], 200);
    }

    public function loginSocial(Request $request)
    {
        $request->validate([
            'social_type' => 'required|max:255',
            'social_id' => 'required|max:255',
            'name' => 'nullable|max:255',
            'email' => 'nullable|string|max:255|regex:/^([a-zA-Z0-9\+_\-]+)(\.[a-zA-Z0-9\+_\-]+)*@([a-zA-Z0-9\-]+\.)+[a-zA-Z]{2,6}$/|email',
        ]);
        try {
            if ($request->social_type == "google") {
                $model = User::where('social_id', $request->social_id)->first();
            } else {
                $model = User::where('social_facebook_id', $request->social_id)->first();
            }        
            
            if (!$model && !empty($request->email)) {
                $model = User::where('email', $request->email)->first();
                if ($model) {
                    if ($request->social_type == "google") {
                        $model->update([
                            'social_id' => $request->social_id,
                            'social_type' => $request->social_type
                        ]);
                    } else {
                        $model->update([
                            'social_facebook_id' => $request->social_id,
                            'social_type' => $request->social_type
                        ]);
                    }
                }
            }
            $result = $this->loginOrCreate($model, $request);
            $tokenResult = $result['tokenResult'];
            $model = $result['model'];

            $accessToken = 'Bearer ' . $tokenResult->accessToken;
            $data = [
                'message' => __('api.login_success'),
                'access_token' => $accessToken,
                'profile' => $model
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            return  $e->getMessage();
        }
    }

    public function loginOrCreate($model, $request)
    {
        if (!$model) {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'social_type' => $request->social_type,
                'status' => 1
            ];
            if( $request->social_type == 'google') {
                $data['social_id'] = $request->social_id;
            } else {
                $data['social_facebook_id'] = $request->social_id;
            }
            $model = User::create($data);
        }

        $tokenResult = $model->createToken('MyApp');

        return [
            'tokenResult' => $tokenResult,
            'model' => $model
        ];
    }


}
