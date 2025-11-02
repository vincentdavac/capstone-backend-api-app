<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseServices;
class insertSensorReadings extends Controller
{
    protected $firebase;
    protected string $ref_tblName;
    public function __construct(FirebaseServices $firebaseService){
        $this->firebase = $firebaseService->getDatabase();
        $this->ref_tblName = 'BUOY-2025-8664'; 
    }
    public function insertSensorData(){
        
    }
}
