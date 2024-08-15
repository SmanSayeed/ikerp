<?php

namespace App\Services;

use App\DTOs\UserDto;
use App\Repositories\UserRepositoryInterface;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function registerUser(UserDto $UserDto)
    {
        return $this->userRepository->create($UserDto->toArray());
    }

    public function loginUser(string $email, string $password)
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !\Hash::check($password, $user->password)) {
            return null;
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }
}
