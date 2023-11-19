<?php

namespace App\Http\Controllers\Api\Authorization;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($request->only(['email', 'password']))) {
            $user = User::where('email', $credentials['email'])->firstOrFail();

            return response()->json([
                'info' => 'OK',
                'token' => $user->createToken('login-token')->plainTextToken
            ]);
        }

        return response()->json(['info' => __('Email or password is incorrect')], Response::HTTP_UNAUTHORIZED);
    }

    public function logout(Request $request): JsonResponse
    {
        auth('sanctum')->user()->tokens()->delete();

        return response()->json(['info' => 'OK']);
    }
}
