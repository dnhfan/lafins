<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Application;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
        ]);
        // --------------------------------------------------------------
        // 1. API CONFIGURATION (STATELESS)
        // ----------------------------------------------------------------

        // Make API routes stateful for SPA authentication
        /* $middleware->statefulApi(); */

        /* $middleware->api(prepend: [ */
        /* \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class, */
        /* ]); */

        // ----------------------------------------------------------------
        // 2. WEB CONFIGURATION (Cleanup)
        // ----------------------------------------------------------------

        /* $middleware->append(\Illuminate\Http\Middleware\HandleCors::class); */
        /**/
        /* $middleware->encryptCookies(except: ['appearance', 'sidebar_state']); */
        /**/
        /* $middleware->web(append: [ */
        /* HandleAppearance::class, */
        /* HandleInertiaRequests::class, */
        /* AddLinkHeadersForPreloadedAssets::class, */
        /* ]); */
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
