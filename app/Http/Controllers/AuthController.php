<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Firestore\FirestoreClient;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
class AuthController extends Controller
{
    protected $auth;
    public $firestore;
    public function __construct(FirebaseAuth $auth, FirestoreClient $firestore) {
        $this->auth = $auth;
        $this->firestore = $firestore;
    }

    protected function register(Request $request){
        $t =  $this->firestore->collection('dreams')->documents();
        foreach ($t as $item1) {
                dd($item1->id());
        }
        // $te = $this->auth->getUser('JNEKxs0IfrQjnl7o2kI3QCEZYSE2');
    }

}
