<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Traits\ApiResponse;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Exception;
use Str;

/**
 * @group Authentication
 *
 * APIs for user authentication
 */
class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Register a new user
     *
     * @bodyParam name string required The user's name. Example: John Doe
     * @bodyParam email string required The user's email address. Example: john@example.com
     * @bodyParam password string required The user's password. Example: Password123!
     * @bodyParam password_confirmation string required Password confirmation. Example: Password123!
     *
     * @response 201 {
     *   "status": "success",
     *   "message": "User registered successfully",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com"
     *     },
     *     "token": "1|abc123..."
     *   }
     * }
     * @response 422 {
     *   "status": "error",
     *   "message": "Invalid credentials",
     *   "errors": {
     *     "email": ["The email has already been taken."]
     *   }
     * }
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => [
                    'required',
                    'confirmed',
                    Rules\Password::default()
                ],
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('auth-token')->plainTextToken;

            return $this->created([
                'user' => $user,
                'token' => $token,
            ], 'User registered successfully');
        } catch (ValidationException $e) {
            return $this->error('Invalid credentials', 422, $e->errors());
        }
    }

    /**
     * Login user
     *
     * @bodyParam email string required The user's email. Example: john@example.com
     * @bodyParam password string required The user's password. Example: Password123!
     *
     * @response {
     *   "status": "success",
     *   "message": "Login successfully",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com"
     *     },
     *     "token": "2|xyz789..."
     *   }
     * }
     * @response 422 {
     *   "status": "error",
     *   "message": "Invalid credentials"
     * }
     */
    public function login(LoginRequest $loginRequest): JsonResponse
    {
        try {
            $user = $loginRequest->authenticate();

            $token = $user->createToken('auth-token')->plainTextToken;

            return $this->success([
                'user' => $user,
                'token' => $token,
            ], 'Login successfully');
        } catch (ValidationException $e) {
            return $this->error(
                'Invalid credentials', 422, $e->errors()
            );
        } catch (Exception $e) {
            return $this->error(
                'Login error: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Logout user
     *
     * @authenticated
     *
     * @response {
     *   "status": "success",
     *   "message": "Logout successfully"
     * }
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if ($token) {
            $token->delete();
        } else {
            // 1. logout with session
            Auth::guard('web')->logout();

            // 2. destroy the curr session
            $request->session()->invalidate();

            // 3. create new token
            $request->session()->regenerateToken();
        }

        return $this->success(null, 'Logout successfully');
    }

    /**
     * Get authenticated user
     *
     * @authenticated
     *
     * @response {
     *   "status": "success",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com"
     *     }
     *   }
     * }
     */
    public function user(Request $request): JsonResponse
    {
        return $this->success(['user' => $request->user()], null);
    }

    /**
     * Send password reset link
     *
     * @bodyParam email string required The user's email address. Example: john@example.com
     *
     * @response {
     *   "status": "success",
     *   "message": "We have emailed your password reset link!"
     * }
     * @response 400 {
     *   "status": "error",
     *   "message": "We can't find a user with that email address."
     * }
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required | email',
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::ResetLinkSent) {
            return $this->success(null, __($status));
        }

        return $this->error(__($status), 400);
    }

    /**
     * Reset password
     *
     * @bodyParam token string required The password reset token. Example: abc123token
     * @bodyParam email string required The user's email address. Example: john@example.com
     * @bodyParam password string required The new password. Example: NewPassword123!
     * @bodyParam password_confirmation string required Password confirmation. Example: NewPassword123!
     *
     * @response {
     *   "status": "success",
     *   "message": "Your password has been reset!"
     * }
     * @response 400 {
     *   "status": "error",
     *   "message": "This password reset token is invalid."
     * }
     */
    public function resetPassword(Request $request)
    {
        // validate
        $request->validate([
            'token' => 'required',
            'email' => 'required | email',
            'password' => [
                'required',
                'confirmed',
                Rules\Password::default(),
            ],
        ]);

        // reset
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PasswordReset) {
            return $this->success(null, __($status));
        }

        return $this->error(__($status), 400);
    }
}
