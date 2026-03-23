<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class fetchWaterLevel extends Controller
{
    public function getAlertsLevel(Request $request){
        $user = $request->user();
        $alertLevel = DB::table('barangays')->select('white_level_alert', 'blue_level_alert', 'red_level_alert')->where('id', $user->barangay_id)->first();
        return response()->json(['success' => true,'data'=> [
                'white_level_alert'=> (int) $alertLevel->white_level_alert,
                'blue_level_alert' => (int) $alertLevel->blue_level_alert,
                'red_level_alert'=> (int) $alertLevel->red_level_alert,
            ]], 200);
    }
}
