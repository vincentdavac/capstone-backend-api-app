<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class getNotifCount extends Controller
{
    public function getCount(Request $request){
        $user = $request->user();
        $count = DB::table('alerts')->where('user_id', $user->id)->where('is_read', 0)->get()->count();;
        return $count;
    }
    public function getCountNotif(Request $request){
        $user = $request->user();
        $countNotif =  DB::table('system_notifications')->where('receiver_id', $user->id)->where('status', 'unread')->get()->count();
        return $countNotif;
    }
    public function allCount(){
        $result = DB::transaction(function () {
            $request = request();
            $countNotif = $this->getCountNotif($request);
            $count = $this->getCount($request);
            return ['countNotif' => $countNotif,'count' => $count];
        });
        return response()->json(['success' => true,'data' => $result], 200);
    }
}
