<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseServices;
class gpsController extends Controller
{
    protected $firebase;
    protected string $ref_tblName;
    public function __construct(FirebaseServices $firebaseService){
        $this->firebase = $firebaseService->getDatabase();
        $this->ref_tblName = 'GPS'; 
    }
    public function getGpsData (){
        $reference = $this->firebase->getReference($this->ref_tblName);
        $data = $reference->getValue(); 
        if(is_null($data)){
            return response()->json(['status' => 'error', 'message' => 'no data found','data'=>[]],404);
        }else{
            return response()->json(['status' => 'success', 'data' => $data],200,[],JSON_PRETTY_PRINT);
        }   
    }
}
