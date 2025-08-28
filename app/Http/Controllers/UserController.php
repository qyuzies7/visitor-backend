<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(){
        return response()->json(User::all());
    }

    public function store(Request $request){
        $validatedData = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin',
            'station_id' => 'required|exists:stations,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validatedData['password']=bcrypt($validated['password']);

        $user = User::create($validated);
        return response()->json($user, 201);
    }

    public function update(Request $request, $id){
        $user = User::findOrFail($id);
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:admin',
            'station_id' => 'required|exists:stations,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);
        return response()->json($user);
    }

    public function destroy($id){
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }
}