<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;
use Laravel\Sanctum\PersonalAccessToken;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid Credentials',
                'errors' => $validator->errors()
            ], 422);
        }

        $registerUserData = $validator->validated();

        $user = User::create([
            'name' => $registerUserData['name'],
            'email' => $registerUserData['email'],
            'password' => Hash::make($registerUserData['password']),
        ]);
        return response()->json([
            'status' => 200,
            'message' => 'User Created ',
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Invalid Credentials',
                'errors' => $validator->errors()
            ], 422);
        }

        $loginUserData = $validator->validated();
        $user = User::where('email', $loginUserData['email'])->first();
        if (!$user || !Hash::check($loginUserData['password'], $user->password)) {
            return response()->json([
                'status' => 'Unauthorized',
                'message' => 'Invalid Credentials'
            ], 401);
        }
        $user->tokens()->delete();
        $token = $user->createToken($user->name . '-AuthToken')->plainTextToken;
        return response()->json([
            'status' => 200,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => 14400,
        ]);
    }

    public function logout()
    {
        if (!auth()->user()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Token'
            ], 400);
        }
        auth()->user()->tokens()->delete();

        return response()->json([
            'status' => 200,
            "message" => "logged out"
        ]);
    }

    public function user()
    {
        try {
            $userData = auth()->user();
            $todayTracked = $userData->trackerLogs()->latest()->first();

            if (!empty($todayTracked) && $todayTracked->created_at->isToday() && $todayTracked->elapsed_time !== null) {
                $elapsedTimeInSeconds = $todayTracked->elapsed_time;
            } else {
                $elapsedTimeInSeconds = 0;
            }

            // add new key
            $userData->today_tracked = $elapsedTimeInSeconds;
            $userData->shift_name = $userData->shift?->name;
            return response()->json([
                'status' => 'success',
                'data' => $userData,
            ], 200);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get user detail'
            ], 500);
        }
    }

    public function checkToken(Request $request)
    {
        try {
            $token = $request->bearerToken();
            if (!$token) {
                return response()->json(['message' => 'Token not provided.'], 401);
            }

            $accessToken = PersonalAccessToken::findToken($token);

            if (!$accessToken) {
                return response()->json([
                    'status' => false,
                    'message' => 'Token is invalid.'
                ], 401);
            }

            if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Token is expired.'
                ], 401);
            }
            $user = $accessToken->tokenable;

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Token is not associated with any user.'
                ], 401);
            }
            return response()->json([
                'status' => true,
                'message' => 'Token is valid.',
                'user' => $user
            ], 200);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to verify token'
            ], 500);
        }
    }


}

