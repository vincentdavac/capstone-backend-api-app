<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class fetchUserInfo extends Controller
{
    public function getUserInfo(Request $request){
      $user = $request->user();
      $data=  DB::table('users')->join('barangays', 'users.barangay_id', '=', 'barangays.id')->where('users.id', $user->id ?? 8)
        ->select('users.first_name','last_name','email','barangays.name')->first();;
      return response()->json(['Success' =>true, 'data'=>$data], 200);
    }
}
