<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function getAuthenticatedUser()
    {
        $user = Auth::user();
        return response()->json(['success' => true, 'data' => $user], 200);
    }

    public function updateUser(Request $request)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|phone',
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        if (isset($validatedData['password'])) {
            $validatedData['password'] = bcrypt($validatedData['password']);
        }

        $user->update($validatedData);

        return response()->json(['success' => true, 'message' => 'Profil mis à jour avec succès', 'data' => $user], 200);
    }

    // Supprimer le profil utilisateur
    public function deleteProfile()
    {
        $user = Auth::user();
        $user->delete();

        return response()->json(['success' => true, 'message' => 'Profil supprimé avec succès'], 200);
    }
}
