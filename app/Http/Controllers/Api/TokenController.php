<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @group Token Management
 * * APIs for manage token
 */
class TokenController extends Controller
{
    use ApiResponse;

    /**
     * list all active sessions
     */
    public function index(Request $request): JsonResponse
    {
        $token = $request->user()->tokens->map(function ($token) use ($request) {
            return [
                'id' => $token->id,
                'name' => $token->name,
                'last_used_at' => $token->last_used_at,
                'created_at' => $token->created_at,
                // for which token is using now by user
                'is_current' => $token->id === $request->user()->currentAccessToken()->id,
            ];
        });

        return $this->success($token, 'Active sessions retrieved successfully');
    }

    /**
     * Revoke specific session
     * Đăng xuất khỏi một thiết bị cụ thể (Kick device)
     */
    public function destroy(Request $request, string $tokenId): JsonResponse
    {
        // find $tokenId
        $token = $request->user()->token()->where('id', $tokenId)->first();

        if (!$token) {
            return $this->error('Token not found or Not belong to you');
        }

        $token->delete();

        return $this->success(null, 'Device logged out successfully');
    }

    /**
     * Revoke all other sessions
     * Đăng xuất TẤT CẢ thiết bị khác, trừ cái đang dùng
     */
    public function destroyOthers(Request $request): JsonResponse
    {
        $currentId = $request->user()->currentAccessToken()->id;

        $request->user()->token()->where('id', '!=', $currentId)->delete();

        return $this->success(null, 'Logged out from all other devices');
    }
}
