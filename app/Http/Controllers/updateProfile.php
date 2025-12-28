<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class updateProfile extends Controller{
    public function updateProfile(Request $request){
        Validator::make($request->all(), [
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'contact' => 'required|string|max:20',
            'email' => 'required|email|unique:users,email,' . $request->user()->id,
            'house_no' => 'nullable|string|max:50',
            'street' => 'nullable|string|max:255',
            'barangay' => 'nullable|string|max:255',
            'municipality' => 'nullable|string|max:255',
        ]);
        $brgyId = null;
        if($request->barangay == 'Barangay 160' || $request->barangay == 'Brgy 160' || $request->barangay == "160"){
            $brgyId = 1;
        }else if($request->barangay == 'Barangay 161' || $request->barangay == 'Brgy 161' || $request->barangay == '161'){
            $brgyId = 2;
        }else if($request->barangay == 'Barangay 162' || $request->barangay == 'Brgy 162' || $request->barangay == '162'){
            $brgyId = 3;
        }else if($request->barangay == 'Barangay 163' || $request->barangay == 'Brgy 163' || $request->barangay == '163'){
            $brgyId = 4;
        }else if($request->barangay == 'Barangay 164' || $request->barangay == 'Brgy 164' || $request->barangay == '164'){
            $brgyId = 5;
        }
        $user = $request->user();
        $user->first_name = $request->fname;
        $user->last_name =$request->lname;
        $user->contact_number = $request->contact;
        $user->email = $request->email;
        $user->house_no =$request->house_no;
        $user->street = $request->street;
        $user->barangay_id = $brgyId;
        $user->municipality = $request->municipality;
        $user->save();
        return response()->json(['success' => true,'message' => 'updated successfully',], 200);
    }
}
