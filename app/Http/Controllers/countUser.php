<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class countUser extends Controller{
    public function countUsers(){
        $count= User::where('is_admin', 0)->count();
        if (is_null($count)) {
            return response()->json(['status' => 'error', 'message' => 'no data found', 'data' => []], 404);
        } else {
            return response()->json(['status' => 'success', 'data' => $count], 200, [], JSON_PRETTY_PRINT);
        }
    }
}
