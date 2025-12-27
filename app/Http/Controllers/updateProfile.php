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
        $user = $request->user();
        $user->first_name = $request->fname;
        $user->last_name =$request->lname;
        $user->contact_number = $request->contact;
        $user->email = $request->email;
        $user->house_no =$request->house_no;
        $user->street = $request->street;
        $user->barangay_id = $request->barangay;
        $user->municipality = $request->municipality;
        $user->save();
        return response()->json(['success' => true,'message' => 'updated successfully',], 200);
    }
}
