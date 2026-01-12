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
use Exception;
use Str;

class AuthController extends Controller
{
    use ApiResponse;

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

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Logout successfully');
    }

    public function user(Request $request): JsonResponse
    {
        return $this->success(['user' => $request->user()], null);
    }

    // for sending resetPassword link
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

    // for req update pass
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
