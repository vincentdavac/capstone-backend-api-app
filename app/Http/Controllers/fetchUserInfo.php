<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class fetchUserInfo extends Controller
{
    public function getUserInfo(Request $request){
      $user = $request->user();
      $data=  DB::table('users')->join('barangays', 'users.barangay_id', '=', 'barangays.id')->where('users.id', $user->id)
      ->select('users.first_name','last_name','email','barangays.name','users.contact_number','users.house_no','users.street','users.municipality',
      DB::raw("CASE WHEN users.image IS NOT NULL THEN CONCAT('" . url('profile_images/') . "/', users.image) ELSE NULL END as image"))->first();
      return response()->json(['Success' =>true, 'data'=>$data], 200);
    }
}
