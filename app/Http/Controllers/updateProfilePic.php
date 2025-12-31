<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class updateProfilePic extends Controller
{
    public function updateProfileImage(Request $request){
        $userData = $request->user();
        Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        $user = $request->user();
        // if ($user->profile_image && file_exists(public_path('profile_images/' . $user->profile_image))) {
        //     unlink(public_path('profile_images/' . $user->profile_image));
        // }
        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $imageName = Str::random(32) . '.' . $imageFile->getClientOriginalExtension();
            $imageFile->move(public_path('profile_images'), $imageName);
            $user->image = $imageName;
            $user->save();
        }
        $title = "Profile Updated";
        $body = "Your profile image has been successfully updated.";
        $status = "unread";
        $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');
        DB::table('system_notifications')->insert([
            'receiver_id' => $userData->id,'barangay_id' => $userData->barangay_id,'receiver_role' => $userData->user_type,
            'title' => $title,'body' => $body,'status' => $status,'created_at'=>$recorded,
        ]);
        return response()->json(['success' => true,'message' => 'profile img updated successfully','profile_image' => url('profile_images/' . $user->image)],200);
    }
}
