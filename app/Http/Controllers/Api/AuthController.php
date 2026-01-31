<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Traits\ApiResponse;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
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

            /* Auth::login($user); */
            /* $request->session()->regenerate(); */

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
     * @response 200 {
     *   "status": "success",
     *   "message": "Two-factor authentication required",
     *   "data": {
     *     "requires_2fa": true,
     *     "temp_token": "2|tempXYZ789shortlived",
     *     "expires_in": 300
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
            $user = $loginRequest->validateCredentials();

            $deviceName = $loginRequest->input('device_name', $loginRequest->userAgent() ?? 'Unknown Device');

            // Check if user has 2FA enabled
            if ($user->hasEnabledTwoFactorAuthentication()) {
                // Create temporary token with limited scope (5 minutes)
                $tempToken = $user->createToken(
                    '2fa-pending',
                    ['2fa-challenge'],
                    now()->addMinutes(5)
                );

                return $this->success([
                    'requires_2fa' => true,
                    'temp_token' => $tempToken->plainTextToken,
                    'expires_in' => 300,
                ], 'Two-factor authentication required');
            }

            // Normal login without 2FA
            $token = $user->createToken($deviceName)->plainTextToken;

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
     * Verify two-factor authentication code
     *
     * @authenticated
     *
     * @bodyParam code string The 2FA verification code (6 digits). Example: 123456
     * @bodyParam recovery_code string Recovery code as alternative to OTP. Example: abc-def-123
     *
     * @response {
     *   "status": "success",
     *   "message": "Two-factor authentication successful",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com"
     *     },
     *     "token": "3|fullaccesstoken30dayvalid"
     *   }
     * }
     * @response 422 {
     *   "status": "error",
     *   "message": "Invalid two-factor authentication code"
     * }
     */
    public function twoFactorChallenge(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'nullable|string',
            'recovery_code' => 'nullable|string',
        ]);

        $user = $request->user();

        // Check if using recovery code
        if ($request->filled('recovery_code')) {
            $recoveryCode = $this->validateRecoveryCode($user, $request->recovery_code);

            if (!$recoveryCode) {
                return $this->error('Invalid recovery code', 422);
            }

            // Replace used recovery code
            $user->replaceRecoveryCode($recoveryCode);
        }
        // Check regular OTP code
        elseif ($request->filled('code')) {
            if (!$this->validateTwoFactorCode($user, $request->code)) {
                return $this->error('Invalid two-factor authentication code', 422);
            }
        } else {
            return $this->error('Please provide either code or recovery_code', 422);
        }

        // Delete the temp token
        $request->user()->currentAccessToken()->delete();

        $deviceName = $request->input('device_name', $request->userAgent() ?? 'Unknown Device');

        // Issue full access token
        $token = $user->createToken($deviceName)->plainTextToken;

        return $this->success([
            'user' => $user,
            'token' => $token,
        ], 'Two-factor authentication successful');
    }

    /**
     * Validate the two-factor authentication code.
     */
    protected function validateTwoFactorCode(User $user, string $code): bool
    {
        $provider = app(TwoFactorAuthenticationProvider::class);

        return $provider->verify(
            decrypt($user->two_factor_secret),
            $code
        );
    }

    /**
     * Validate and get the recovery code if valid.
     */
    protected function validateRecoveryCode(User $user, string $recoveryCode): ?string
    {
        return collect($user->recoveryCodes())->first(function ($code) use ($recoveryCode) {
            return hash_equals($code, $recoveryCode) ? $code : null;
        });
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
        $request->user()->currentAccessToken()->delete();

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
