<?php

namespace App\Http\Controllers;

use App\DTOs\UserDTO;
use App\Helpers\ResponseHelper;
use App\Http\Requests\RegisterUserRequest;
use App\Services\UserService;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function register(RegisterUserRequest $request)
    {
        $userDTO = new UserDTO(
            $request->name,
            $request->email,
            $request->password,
            $request->role
        );

        $user = $this->userService->registerUser($userDTO);

        return ResponseHelper::success($user, 'User registered successfully.');
    }
}
