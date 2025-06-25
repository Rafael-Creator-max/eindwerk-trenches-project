<?php

namespace App\Http\Controllers\API;

/**
 * @group Authentication
 * 
 * APIs for user authentication and management
 */

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class TokenAuthController extends Controller
{
    /**
     * Authenticate user and create token
     *
     * Authenticate a user with email and password to receive an access token.
     *
     * @bodyParam email string required The email address of the user. Example: user@example.com
     * @bodyParam password string required The user's password. Example: your-secure-password
     *
     * @response 200 {
     *     "access_token": "1|a1b2c3d4...",
     *     "token_type": "Bearer",
     *     "user": {
     *         "id": 1,
     *         "name": "John Doe",
     *         "email": "user@example.com",
     *         "email_verified_at": "2023-01-01T12:00:00.000000Z",
     *         "created_at": "2023-01-01T12:00:00.000000Z",
     *         "updated_at": "2023-01-01T12:00:00.000000Z"
     *     }
     * }
     * @response 401 {
     *     "message": "The provided credentials are incorrect.",
     *     "errors": {
     *         "email": ["The provided credentials are incorrect."]
     *     }
     * }
     * @response 422 {
     *     "message": "The given data was invalid.",
     *     "errors": {
     *         "email": ["The email field is required."],
     *         "password": ["The password field is required."]
     *     }
     * }
     */
    public function login(Request $request)
    {
        \Log::info('Login attempt', ['email' => $request->email]);
        
        try {
            // Enable query logging to help with debugging
            \DB::enableQueryLog();
            
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
            \Log::info('Validation passed');

            if (!Auth::attempt($request->only('email', 'password'))) {
                \Log::warning('Authentication failed', ['email' => $request->email]);
                return response()->json([
                    'message' => 'The provided credentials are incorrect.',
                    'errors' => [
                        'email' => ['The provided credentials are incorrect.']
                    ]
                ], 401);
            }
            \Log::info('Authentication successful');

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json([
                    'message' => 'User not found.',
                    'errors' => [
                        'email' => ['No user found with this email address.']
                    ]
                ], 404);
            }
            \Log::info('User found', ['user_id' => $user->id]);
            
            try {
                $token = $user->createToken('auth_token')->plainTextToken;
                \Log::info('Token created', ['token' => substr($token, 0, 10) . '...']);
                
                // Log the queries that were executed
                \Log::info('Database queries:', \DB::getQueryLog());
                
                return response()->json([
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'user' => $user
                ]);
            } catch (\Exception $e) {
                \Log::error('Token creation failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'message' => 'Failed to create authentication token.',
                    'error' => $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Login error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'An error occurred during login.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user (Revoke the token)
     *
     * Revoke the user's current authentication token.
     *
     * @authenticated
     * @header Authorization Bearer {token}
     *
     * @response 200 {
     *     "message": "Successfully logged out"
     * }
     * @response 401 {
     *     "message": "Unauthenticated."
     * }
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Get authenticated user details
     *
     * Returns the details of the currently authenticated user.
     *
     * @authenticated
     * @header Authorization Bearer {token}
     *
     * @response 200 {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "user@example.com",
     *     "email_verified_at": "2023-01-01T12:00:00.000000Z",
     *     "created_at": "2023-01-01T12:00:00.000000Z",
     *     "updated_at": "2023-01-01T12:00:00.000000Z"
     * }
     * @response 401 {
     *     "message": "Unauthenticated."
     * }
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Register a new user
     *
     * Create a new user account with the provided information.
     *
     * @bodyParam name string required The name of the user. Example: John Doe
     * @bodyParam email string required The email address of the user. Must be unique. Example: user@example.com
     * @bodyParam password string required The desired password. Minimum 8 characters. Example: your-secure-password
     * @bodyParam password_confirmation string required Confirm the password. Must match password. Example: your-secure-password
     *
     * @response 201 {
     *     "message": "User registered successfully",
     *     "user": {
     *         "name": "John Doe",
     *         "email": "user@example.com",
     *         "updated_at": "2023-01-01T12:00:00.000000Z",
     *         "created_at": "2023-01-01T12:00:00.000000Z",
     *         "id": 1
     *     },
     *     "token": "1|a1b2c3d4..."
     * }
     * @response 422 {
     *     "message": "The given data was invalid.",
     *     "errors": {
     *         "email": ["The email has already been taken."],
     *         "password": ["The password confirmation does not match."]
     *     }
     * }
     */
    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => bcrypt($validatedData['password']),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Registration error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
