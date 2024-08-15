<?php

namespace App\Http\Controllers;

use App\DTOs\UserDto;
use App\Helpers\ResponseHelper;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Services\UserService;

class AuthController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function register(RegisterUserRequest $request)
    {
        $UserDto = new UserDto(
            $request->name,
            $request->email,
            $request->password,
            $request->role
        );

        $user = $this->userService->registerUser($UserDto);

        return ResponseHelper::success($user, 'User registered successfully.');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $loginData = $this->userService->loginUser($credentials['email'], $credentials['password']);

        if (!$loginData) {
            return ResponseHelper::error('Invalid credentials', 401);
        }

        return ResponseHelper::success($loginData, 'Login successful.');
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return ResponseHelper::success(null, 'Logged out successfully.');
    }
}
