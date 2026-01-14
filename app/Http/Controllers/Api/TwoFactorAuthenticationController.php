<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Features;

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
     * Show the user's two-factor authentication settings page.
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

    public function confirm(Request $request, ConfirmTwoFactorAuthentication $confirm)
    {
        $request->validate(['code' => 'required|string']);
        $confirm($request->user(), $request->input('code'));

        return $this->success(null, '2FA auth confirmed');
    }

    public function destroy(Request $request, DisableTwoFactorAuthentication $disable)
    {
        $disable($request->user());

        return $this->success(null, '2FA auth disable');
    }
}
