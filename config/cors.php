<?php

return [
    /*
     * |--------------------------------------------------------------------------
     * | Cross-Origin Resource Sharing (CORS) Configuration
     * |--------------------------------------------------------------------------
     * |
     * | Here you may configure your settings for cross-origin resource sharing
     * | or "CORS". This determines what cross-origin operations may execute
     * | in web browsers. You are free to adjust these settings as needed.
     * |
     * | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
     * |
     */
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'register'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:3000'), 'http://localhost:8000'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    // 👇 Nếu bạn dùng Sanctum (cookie), cái này phải là true.
    // NHƯNG nếu để true, thì allowed_origins KHÔNG ĐƯỢC là ['*'].
    // => Case này nếu bạn test Token (Bearer) thì để false, ['*'] là ok.
    // => Nếu bạn muốn chuẩn chỉ, hãy điền đúng url docs vào allowed_origins
    'supports_credentials' => true,
];
