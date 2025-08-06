<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationErrorTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_request_returns_proper_error()
    {
        // Make a request to an authenticated endpoint without providing a token
        $response = $this->getJson('/api/pharmacy/patient/orders');

        $response->assertStatus(401)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
                'code'
            ])
            ->assertJson([
                'success' => false,
                'message' => 'Authentication required. Please provide a valid Bearer token.',
                'code' => 401
            ]);
    }

    public function test_invalid_token_returns_proper_error()
    {
        // Make a request with an invalid token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token'
        ])->getJson('/api/pharmacy/patient/orders');

        $response->assertStatus(401)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
                'code'
            ])
            ->assertJson([
                'success' => false,
                'message' => 'Authentication required. Please provide a valid Bearer token.',
                'code' => 401
            ]);
    }

    public function test_nonexistent_endpoint_returns_proper_error()
    {
        // Make a request to a non-existent endpoint
        $response = $this->getJson('/api/nonexistent/endpoint');

        $response->assertStatus(404)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
                'code'
            ])
            ->assertJson([
                'success' => false,
                'message' => 'API endpoint not found. Please check the URL and try again.',
                'code' => 404
            ]);
    }

    public function test_wrong_method_returns_proper_error()
    {
        // Make a POST request to a GET-only endpoint
        $response = $this->postJson('/api/pharmacy/patient/orders');

        $response->assertStatus(405)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
                'code'
            ])
            ->assertJson([
                'success' => false,
                'message' => 'Method not allowed for this endpoint.',
                'code' => 405
            ]);
    }

    public function test_authenticated_request_with_valid_token_works()
    {
        // Create a user and get a valid token
        $user = \App\Models\User\User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        // Make a request with a valid token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/pharmacy/patient/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'code'
            ])
            ->assertJson([
                'success' => true,
                'message' => 'No order history found. Start by placing your first order!',
                'code' => 200
            ]);
    }
}
