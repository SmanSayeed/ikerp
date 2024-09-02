<?php

namespace App\Services;

use App\DTOs\ClientDto;
use App\DTOs\UserDto;
use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use Exception;
use App\Events\SendEmail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function __construct(private UserRepositoryInterface $userRepository) {}

    public function registerUser(UserDto $userDto)
    {
        try {
               // Make sure $userDto has valid data and check for null values
               if (is_null($userDto->email) || is_null($userDto->password)) {
                throw new \Exception('Invalid user data');
            }

            $userData = $userDto->toArray();
            $userData['password'] = bcrypt($userData['password']);

            $user = $this->userRepository->create($userData);

            // Generate and send email verification
            $verificationUrl = $this->generateVerificationUrl($user);
            $emailData = [
                'name' => $user->name,
                'verification_url' => $verificationUrl
            ];

            event(new SendEmail('verification', $emailData, $user->email));

            return $user;
        } catch (Exception $e) {
            throw new Exception('Failed to register user: ' . $e->getMessage());
        }
    }

    public function registerClient(ClientDto $clientDto)
    {
        try {
            // Create user in the users table
            $userData = [
                'name' => $clientDto->name,
                'email' => $clientDto->email,
                'password' => bcrypt($clientDto->password),
                'role' => $clientDto->role,
                'status' => $clientDto->status,
            ];

            $user = $this->userRepository->create($userData);

            // Create client in the clients table
            $clientData = [
                'name' => $clientDto->name,
                'email' => $clientDto->email,
                'address' => $clientDto->address ?? null,
                'phone' => $clientDto->phone ?? null,
                'client_type' => $clientDto->client_type,
                'payment_due_date' => $clientDto->payment_due_date ?? null,
                'vat_slab' => $clientDto->vat_slab ?? null,
                'gbs_information' => $clientDto->gbs_information ?? null,
                'is_vip' => $clientDto->is_vip ?? false,
                'vip_discount' => $clientDto->vip_discount ?? null,
                'parent_client_id' => $clientDto->parent_client_id ?? null,
                'user_id' => $user->id,
            ];

            $client = \App\Models\Client::create($clientData);

            // Send email verification
            $verificationUrl = $this->generateVerificationUrl($user);
            $emailData = [
                'name' => $user->name,
                'verification_url' => $verificationUrl
            ];
            event(new SendEmail('verification', $emailData, $user->email));

            return $client;
        } catch (Exception $e) {
            throw new Exception('Failed to register client: ' . $e->getMessage());
        }
    }



    public function loginUser(string $email, string $password)
    {
        try {
            $user = $this->userRepository->findByEmail($email);

            if (!$user) {
                throw new Exception('User not found.');
            }

            if (!\Illuminate\Support\Facades\Hash::check($password, $user->password)) {
                throw new Exception('Invalid credentials.');
            }

            if ($user->email_verified_at === null) {
                throw new Exception('Email not verified.');
            }

            if (!$user->status) { // Assuming `status` is a boolean field
                throw new Exception('User is not activated.');
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return ['user' => $user, 'token' => $token];
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function resendVerificationEmail($email)
    {
        try {
            $user = User::where('email',$email)->first(); // Get the currently authenticated user

            if (!$user) {
                throw new Exception('User not found.');
            }

            if ($user->email_verified_at) {
                throw new Exception('Email already verified.');
            }

            // Generate and send email verification
            $verificationUrl = $this->generateVerificationUrl($user);

            $emailData = [
                'name' => $user->name,
                'verification_url' => $verificationUrl
            ];

            event(new SendEmail('verification', $emailData, $user->email));

            return $user;
        } catch (Exception $e) {
            throw new Exception('Failed to resend verification email: ' . $e->getMessage());
        }
    }

    private function generateVerificationUrl($user)
    {
        return URL::temporarySignedRoute(
            'verify.email',
            now()->addMinutes(60),
            ['user' => $user->id]
        );
    }
}
