<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:9|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create a new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
        ]);

        // Return a success response
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        // Validate the request data
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Attempt to authenticate the user
        if (!auth()->attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        //remember me
        // if(auth()->attempt())

        // Generate a new token for the authenticated user
        $user = auth()->user();
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return a success response
        return response()->json([
            'message' => 'User logged in successfully',
            'user' => auth()->user(),
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        // Revoke the user's token
        auth()->user()->tokens()->delete();

        // Return a success response
        return response()->json(['message' => 'User logged out successfully']);
    }

    public function getUser(Request $request)
    {
        // Return the authenticated user
        return response()->json(auth()->user());
    }

    public function updateUser(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        // Validate the request data
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . auth()->id(),
            'phone' => 'sometimes|required|string|max:9|unique:users,phone,' . auth()->id(),
            'password' => 'sometimes|required|string|min:8|confirmed',
        ]);

        // Update the authenticated user's information

        $user->update($request->only('name', 'phone', 'password'));

        // Return a success response
        return response()->json(['message' => 'User updated successfully']);
    }
}
