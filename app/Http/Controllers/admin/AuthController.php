<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class AuthController extends Controller
{
    //
    public function login(Request $request){
        $validated = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if($validated->fails()){
            return response()->json([
                'status' => 400,
                'errors' => $validated->errors(),
            ], 400);
        }

        if(Auth::attempt($validated->validated())){
            $user = User::find(Auth::id());

            if($user->role == 'admin'){
                $token = $user->createToken('admin-token')->plainTextToken;

                return response()->json([
                    'status' => 200,
                    'message' => 'Login successful',
                    'token' => $token,
                    'id' => $user->id,
                    'name' => $user->name,
                    'user' => $user,
                ], 200);
            }
            else{
                return response()->json([
                    'status' => 403,
                    'message' => 'Forbidden',
                ], 403);
            }
        }

        return response()->json([
            'status' => 401,
            'message' => 'Unauthorized',
        ], 401);
    }
}
