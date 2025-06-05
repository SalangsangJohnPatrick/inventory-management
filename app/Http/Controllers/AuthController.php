<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                return response()->json([
                    'success' => false,
                    'message' => implode("\n", $errors) // convert to single string separated by line breaks
                ], 422);
            }

            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => $request->password,
            ]);

            $token = JWTAuth::fromUser($user);

            return response()->json(['token' => $token], 201);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Signup failed. Please try again.', 
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->only(['email', 'password']);

            if (!$token = auth()->attempt($credentials)) {
                return response()->json(['error' => 'Please check your credentials.'], 401);
            }

            return response()->json(['token' => $token]);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Login failed. Please try again.', 
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
