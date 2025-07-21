<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use Illuminate\Http\Request;
use \App\Traits\HttpResponses;
use function Pest\Laravel\json;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Import the HttpResponses trait to use its methods
    use HttpResponses;

    public function login(LoginUserRequest $request)
    {
        $request->validated($request->all());

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return $this->error('', 'Credentials do not match', 401);
        }

        $user = User::where('email', $request->email)->first();
        return $this->success([
            'user' => $user,
            'token' => $user->createToken('API Token:' . $user->name)->plainTextToken,
            'message' => 'User logged in successfully'
        ]);
    }

    public function register(StoreUserRequest $request)
    {
        $user = User::create([
            'first_name'     => $request->first_name,
            'last_name'      => $request->last_name,
            'email'          => $request->email,
            'contact_number' => $request->contact_number,
            'image'          => $request->image,
            'image_url'      => $request->image_url,
            'password'       => Hash::make($request->password),
        ]);

        return $this->success([
            'user'  => $user,
            'token' => $user->createToken('API Token: ' . $user->first_name)->plainTextToken,
            'message' => 'User created successfully',
        ], 'User created successfully', 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success('', 'User logged out successfully', 200);
    }
}
