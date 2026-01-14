<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Http\Resources\UserResource;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @group User Profile
 *
 * APIs for managing user profile
 */
class ProfileController extends Controller
{
    use ApiResponse;

    /**
     * Get user profile
     *
     * @authenticated
     *
     * @response {
     *   "status": "success",
     *   "message": "Getted user",
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com"
     *   }
     * }
     */
    public function show(Request $request): JsonResponse
    {
        return $this->success(new UserResource($request->user()), 'Getted user');
    }

    /**
     * Update user profile
     *
     * @authenticated
     *
     * @bodyParam name string The user's name. Example: John Doe
     * @bodyParam email string The user's email address. Example: john@example.com
     *
     * @response {
     *   "status": "success",
     *   "message": "Updated user",
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com"
     *   }
     * }
     */
    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $user = $request->user();

        $request->user()->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $this->success(new UserResource($request->user()), 'Updated user');
    }

    /**
     * Delete user account
     *
     * Permanently deletes the user's account and all associated data.
     *
     * @authenticated
     *
     * @bodyParam password string required Current password for confirmation. Example: Password123!
     *
     * @response {
     *   "status": "success",
     *   "message": "Deleted user"
     * }
     * @response 422 {
     *   "status": "error",
     *   "message": "The provided password is incorrect."
     * }
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        $user->tokens()->delete();

        $user->delete();
        return $this->success(null, 'Deleted user');
    }
}
