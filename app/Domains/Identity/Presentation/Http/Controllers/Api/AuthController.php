<?php

namespace App\Domains\Identity\Presentation\Http\Controllers\Api;

use App\Domains\Identity\Application\Services\AuthenticateUserService;
use App\Domains\Identity\Application\Services\RegisterUserService;
use App\Domains\Identity\Presentation\Http\Requests\LoginRequest;
use App\Domains\Identity\Presentation\Http\Requests\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController
{
    public function register(RegisterRequest $request, RegisterUserService $service): JsonResponse
    {
        $user = $service->handle($request->validated());

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ], 201);
    }

    public function login(LoginRequest $request, AuthenticateUserService $service): JsonResponse
    {
        $user = $service->handle($request->validated());

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => true, 'data' => null]);
    }
}
