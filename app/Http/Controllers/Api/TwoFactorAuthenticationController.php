<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Laravel\Fortify\Features;

/**
 * @group Two-Factor Authentication
 *
 * APIs for managing two-factor authentication (2FA)
 */
class TwoFactorAuthenticationController extends Controller
{
    use ApiResponse;

    /* public function __construct() */
    /* { */
    /* if (Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')) { */
    /* $this->middleware('password.confirm')->only('show'); */
    /* } */
    /* } */

    /**
     * Get 2FA status
     *
     * @authenticated
     *
     * @response {
     *   "status": "success",
     *   "message": "2FA status retrieved success",
     *   "data": {
     *     "twoFactorEnabled": false,
     *     "pendingConfirmation": false,
     *     "requiresConfirmation": true
     *   }
     * }
     */
    public function show(TwoFactorAuthenticationRequest $request): JsonResponse
    {
        // not using session
        /* $request->ensureStateIsValid(); */

        $user = $request->user();

        $data = [
            'twoFactorEnabled' => $request->user()->hasEnabledTwoFactorAuthentication(),
            'pendingConfirmation' => !is_null($user->two_factor_secret) && is_null($user->two_factor_confirmed_at),
            'requiresConfirmation' => Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm'),
        ];

        return $this->success($data, '2FA status retrieved success');
    }

    /**
     * Enable 2FA
     *
     * Enables two-factor authentication and generates QR code for setup.
     *
     * @authenticated
     *
     * @response {
     *   "status": "success",
     *   "message": "2FA authentication enable",
     *   "data": {
     *     "status": "enable",
     *     "qr_code_svg": "<svg>...</svg>",
     *     "setup_key": "otpauth://...",
     *     "secret": "ABC123..."
     *   }
     * }
     */
    public function store(Request $request, EnableTwoFactorAuthentication $enable)
    {
        $enable($request->user());

        $data = [
            'status' => 'enable',
            'qr_code_svg' => $request->user()->twoFactorQrCodeSvg(),
            'setup_key' => $request->user()->twoFactorQrCodeUrl(),
            'secret' => decrypt($request->user()->two_factor_secret),
        ];

        return $this->success($data, '2FA authentication enable');
    }

    /**
     * Confirm 2FA setup
     *
     * Confirms two-factor authentication setup with verification code.
     *
     * @authenticated
     *
     * @bodyParam code string required The 2FA verification code. Example: 123456
     *
     * @response {
     *   "status": "success",
     *   "message": "2FA auth confirmed"
     * }
     * @response 422 {
     *   "status": "error",
     *   "message": "The provided two factor authentication code was invalid."
     * }
     */
    public function confirm(Request $request, ConfirmTwoFactorAuthentication $confirm)
    {
        $request->validate(['code' => 'required|string']);
        $confirm($request->user(), $request->input('code'));

        $user = $request->user();

        $recoveryCodes = [];
        if ($user->two_factor_recovery_codes) {
            $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
        }
        return $this->success(['recovery_codes' => $recoveryCodes], '2FA auth confirmed');
    }

    /**
     * Disable 2FA
     *
     * Disables two-factor authentication for the user.
     *
     * @authenticated
     *
     * @response {
     *   "status": "success",
     *   "message": "2FA auth disable"
     * }
     */
    public function destroy(Request $request, DisableTwoFactorAuthentication $disable)
    {
        $request->validate(['password' => 'required|string']);

        if (!Hash::check($request->password, $request->user()->password)) {
            return $this->error('invalid password', 422);
        }

        $disable($request->user());

        return $this->success(null, '2FA auth disable');
    }

    /**
     * Get Recovery Codes (Xem lại mã dự phòng)
     * Cũng cần check password cho chắc ăn
     */
    public function recoveryCodes(Request $request): JsonResponse
    {
        $request->validate(['password' => 'required']);

        if (!Hash::check($request->password, $request->user()->password)) {
            return $this->error('Invalid password', 422);
        }

        $user = $request->user();

        if (!$user->two_factor_recovery_codes) {
            return $this->error('2FA not enabled', 400);
        }

        return $this->success([
            'recovery_codes' => json_decode(decrypt($user->two_factor_recovery_codes), true)
        ], 'Recovery codes retrieved');
    }

    /**
     * Generate New Recovery Codes (Tạo mã mới)
     */
    public function regenerateRecoveryCodes(Request $request, GenerateNewRecoveryCodes $generate): JsonResponse
    {
        $request->validate(['password' => 'required']);

        if (!Hash::check($request->password, $request->user()->password)) {
            return $this->error('Invalid password', 422);
        }

        $generate($request->user());

        return $this->success([
            'recovery_codes' => json_decode(decrypt($request->user()->two_factor_recovery_codes), true)
        ], 'Recovery codes regenerated');
    }
}
