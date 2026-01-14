<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @group User Password
 *
 * API for changing user password
 */
class PasswordController extends Controller
{
    use ApiResponse;

    /**
     * Update password
     *
     * @authenticated
     *
     * @bodyParam current_password string required The current password. Example: OldPassword123!
     * @bodyParam password string required The new password. Example: NewPassword123!
     * @bodyParam password_confirmation string required Password confirmation. Example: NewPassword123!
     *
     * @response {
     *   "status": "success",
     *   "message": "Password updated"
     * }
     * @response 422 {
     *   "status": "error",
     *   "message": "The current password is incorrect."
     * }
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return $this->success(null, 'Password updated');
    }
}
