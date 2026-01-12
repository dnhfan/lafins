<?php

test('cors headers are present in preflight request', function () {
    // Dùng hàm call() để gửi request OPTIONS
    $response = $this->call('OPTIONS', '/api/dashboard', [], [], [], [
        'HTTP_Origin' => 'http://localhost:3000',
        'HTTP_Access-Control-Request-Method' => 'GET',
    ]);

    $response
        ->assertStatus(204)  // Laravel CORS mặc định trả về 204 No Content cho OPTIONS
        ->assertHeader('access-control-allow-origin', 'http://localhost:3000');
});
