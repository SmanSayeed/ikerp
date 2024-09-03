<?php

namespace App\Http\Controllers\Client;

use App\DTOs\ClientDto;
use App\DTOs\ClientRegisterDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterClientRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Resources\ClientResource;
use App\Services\ClientAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseHelper;
use Exception;
use Illuminate\Support\Facades\Validator;

class ClientAuthController extends Controller
{
    protected $clientAuthService;

    public function __construct(ClientAuthService $clientAuthService)
    {
        $this->clientAuthService = $clientAuthService;
    }

    public function register(RegisterClientRequest $request)
{
    try {
        $clientRegisterDto = ClientRegisterDto::from($request->validated());
        $result = $this->clientAuthService->register($clientRegisterDto);

        return ResponseHelper::success([
            'client' => new ClientResource($result['client']),
            'token' => $result['token'],
        ], 'Registration successful.');
    } catch (Exception $e) {
        return ResponseHelper::error($e->getMessage(), 500);
    }
}

public function login(LoginRequest $request)
{
    $credentials = $request->only('email', 'password');
    $result = $this->clientAuthService->login($credentials);

    if (!$result) {
        return ResponseHelper::error('Invalid credentials', 401);
    }

    return ResponseHelper::success([
        'client' => new ClientResource($result['client']),
        'token' => $result['token'],
    ], 'Login successful.');
}
public function profile(Request $request)
{
    return ResponseHelper::success(new ClientResource($request->user()), 'Profile retrieved successfully.');
}

public function updateProfile(Request $request)
{
    $user = $request->user();

    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'address' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:15',
    ]);

    if ($validator->fails()) {
        return ResponseHelper::error($validator->errors()->first(), 422);
    }

    $clientRegisterDto = ClientDto::from($validator->validated());
    $updatedClient = $this->clientAuthService->updateProfile($user, $clientRegisterDto);

    return ResponseHelper::success(new ClientResource($updatedClient), 'Profile updated successfully.');
}

    public function resetPassword(UpdatePasswordRequest $request)
    {
        $client = Auth::user();
        $this->clientAuthService->resetPassword($client, $request->password);

        return ResponseHelper::success(null, 'Password reset successfully.');
    }

    public function logout()
    {
        $client = Auth::user(); // This returns an instance of Authenticatable
        if ($client instanceof \App\Models\Client) {
            $this->clientAuthService->logout($client);
            return ResponseHelper::success(null, 'Logged out successfully.');
        }

        return ResponseHelper::error('No authenticated user found.', 401);
    }
}
