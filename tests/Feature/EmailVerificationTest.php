<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user registration sends a verification email.
     *
     * @return void
     */
    public function test_registration_sends_verification_email()
    {
        Notification::fake();

        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201);

        // Assert that a notification was sent to the given user
        Notification::assertSentTo(
            User::where('email', 'john.doe@example.com')->first(),
            \App\Notifications\VerifyEmailNotification::class
        );
    }

    /**
     * Test email verification route.
     *
     * @return void
     */
    public function test_email_verification()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = \URL::temporarySignedRoute(
            'verify.email',
            now()->addMinutes(60),
            ['user' => $user->id]
        );

        $response = $this->get($verificationUrl);

        $response->assertStatus(200);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
