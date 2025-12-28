<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
class updateProfilePic extends Controller
{
    public function updateProfileImage(Request $request){
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
        return response()->json(['success' => true,'message' => 'profile img updated successfully','profile_image' => url('profile_images/' . $user->image)],200);
    }
}
