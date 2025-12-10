<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class currentConditionv2 extends Controller
{
    public function getwindKh(){
        $kh = DB::table('wind_readings')->select('wind_speed_k_h')->latest('recorded_at')->first();
        $windKh = (int)$kh->wind_speed_k_h;
        $status = null;
        if($windKh >= 39 && $windKh <=61){
            $status = "White";
        }else if($windKh >=62 && $windKh <=88){
            $status = "Blue";
        }else if($windKh >= 89 && $windKh<=117){
            $status = "Blue";
        }
        else{
            $status = "Red";
        }
        return['wind_speed_k_h'=>$windKh,'staus'=>$status];
    }
     public function getwindMs(){
        $kh = DB::table('wind_readings')->select('wind_speed_m_s')->latest('recorded_at')->first();
        $windMs = (int)$kh->wind_speed_m_s;
        $status = null;
        if($windMs >= 39 && $windMs <=61){
            $status = "White";
        }else if($windMs >=62 && $windMs <=88){
            $status = "Blue";
        }else if($windMs >= 89 && $windMs<=117){
            $status = "Blue";
        }
        else{
            $status = "Red";
        }
        return ['wind_speed_m_s' => $windMs, 'status' => $status];
    }
    public function getdepth(){
        $depth =DB::table('depth_readings')->select('depth_ft')->latest()->first();
        $depthFeet =$depth->depth_ft;
        $div = $depthFeet / 17;
        $finalData = $div *100;
        $status = null;
        if($finalData <= 40){
            $status = 'White';
        }else if ($finalData >=41 && $finalData <=60){
            $status ='Blue';
        }else if($finalData >=61 && $finalData <=99){
            $status = 'Blue';
        }else{
            $status ='Red';
        }
        return ['wind_speed_m_s' => $depthFeet, 'status' => $status];
    }
     public function getCurrentCondition(){
        $data = DB::transaction(function () {
            return [
                'windKh' => $this->getwindKh(),
                'windms'=> $this->getwindMs(),
                'waterDepth'=>$this->getdepth(),
            ];
        });
       return response()->json(['success' => true, 'data' => $data], 200,[],JSON_PRETTY_PRINT);
    }
}
