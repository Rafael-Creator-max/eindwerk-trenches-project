<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_sends_verification_email_after_registration()
    {
        Mail::fake();
        
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'User registered successfully. Please check your email to verify your account.',
                'requires_verification' => true
            ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertFalse($user->hasVerifiedEmail());
    }

    /** @test */
    public function it_prevents_login_with_unverified_email()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Please verify your email address before logging in.',
                'requires_verification' => true
            ]);
    }

    /** @test */
    public function it_allows_login_with_verified_email()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'user' => [
                    'id',
                    'name',
                    'email',
                ]
            ]);
    }

    /** @test */
    public function it_resends_verification_email()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/email/verification-notification');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Verification link resent to your email address.'
            ]);
    }

    /** @test */
    public function it_returns_error_when_resending_to_verified_email()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/email/verification-notification');

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Email already verified.',
                'is_verified' => true
            ]);
    }

    /** @test */
    public function it_includes_verification_status_in_user_endpoint()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'is_verified'
            ]);
    }
}
