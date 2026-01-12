<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Features;

class TwoFactorAuthenticationController extends Controller
{
    public function __construct()
    {
        if (Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')) {
            $this->middleware('password.confirm')->only('show');
        }
    }

    /**
     * Show the user's two-factor authentication settings page.
     */
    public function show(TwoFactorAuthenticationRequest $request): JsonResponse
    {
        $request->ensureStateIsValid();

        return response()->json([
            'twoFactorEnabled' => $request->user()->hasEnabledTwoFactorAuthentication(),
            'requiresConfirmation' => Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm'),
        ]);
    }

    public function store(Request $request, ConfirmTwoFactorAuthentication $enable)
    {
        $enable($request->user());
        return response()->json([
            'qr_code_svg' => $request->user()->twoFactorQrCodeSvg(),
            'setup_key' => $request->user()->twoFactorQrCodeUrl(),
        ]);
    }
}
