<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseServices;

class waterController extends Controller{
    protected $firebase;
    protected string $ref_tblName;
    public function __construct(FirebaseServices $firebaseService){
        $this->firebase = $firebaseService->getDatabase();
        $this->ref_tblName = 'MS5837'; 
    }
    public function getWaterTemp (){
        $reference = $this->firebase->getReference($this->ref_tblName);
        $data = $reference->getValue(); 
        $temp = $data['WATER_PRESSURE'];
        if(is_null($temp)){
            return response()->json(['status' => 'error', 'message' => 'no data found','data'=>[]],404);
        }else{
            return response()->json(['status' => 'success', 'WATER_PRESSURE' => $temp],200,[],JSON_PRETTY_PRINT);
        }   
    }
}
