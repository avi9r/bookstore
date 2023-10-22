<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            // Authentication successful
            $user = Auth::user();
            $token = $user->createToken('MyAppToken')->plainTextToken;
            $user->role = 'customer';
            if ($user->hasRole('admin')) {
                $user->role = 'admin';
            }
            return response()->json(['message' => 'Login successful', 'user' => $user, 'token' => $token]);
        } else {
            // Authentication failed
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    }

    public function logout()
    {
        Auth::user()->tokens()->delete();
        
        return response()->json(['message' => 'Logged out successfully']);
    }
}
