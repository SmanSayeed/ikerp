<?php

namespace App\Services;

use App\DTOs\ClientRegisterDto;
use App\Models\Client;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Exception;

class ClientAuthService
{
    /**
     * Register a new client.
     *
     * @param array $data
     * @return array
     */
    public function register(ClientRegisterDto $clientRegisterDto)
    {
        try {
            $data = $clientRegisterDto->toArray();
            $data['password'] = bcrypt($data['password']);
            $client = Client::create($data);
            // Generate token
            $token = $client->createToken('client-auth-token')->plainTextToken;
            return ['client' => $client, 'token' => $token];
        } catch (Exception $e) {
            throw new Exception('Registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Login a client.
     *
     * @param array $credentials
     * @return array|null
     */
    public function login(array $credentials)
    {
        $client = Client::where('email', $credentials['email'])->first();

        if (!$client || !Hash::check($credentials['password'], $client->password)) {
            return null;
        }

        // Generate token
        $token = $client->createToken('client-auth-token')->plainTextToken;

        return ['client' => $client, 'token' => $token];
    }

   /**
     * Update client profile.
     *
     * @param Client $client
     * @param ClientRegisterDto $clientRegisterDto
     * @return Client
     */
    public function updateProfile(Client $client, ClientRegisterDto $clientRegisterDto)
    {
        $client->update($clientRegisterDto->toArray());
        return $client;
    }

    /**
     * Reset client password.
     *
     * @param Client $client
     * @param string $password
     * @return void
     */
    public function resetPassword(Authenticatable $client, $password)
    {
        $client->password = bcrypt($password);
        $client->save();
    }

    /**
     * Logout client.
     *
     * @param Client $client
     * @return void
     */
    public function logout(Authenticatable $client)
    {
        $client->tokens()->delete();
    }
}
