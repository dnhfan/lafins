<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Traits\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Exception;

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

    public function login(LoginRequest $loginRequest)
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

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Logout successfully');
    }

    public function user(Request $request)
    {
        return $this->success(['user' => $request->user()], null);
    }

    public function forgotPassword(Request $request)
    {
        // reset email logig
    }

    public function resetPassword(Request $request)
    {
        // code...
    }
}
