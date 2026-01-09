<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

/**
 * To format output api (for consistentcy)
 */
trait ApiResponse
{
    protected function success($data = null, ?string $message, int $code = 200): JsonResponse
    {
        $response = ['status' => 'success'];

        if ($message) {
            $response['message'] = $message;
        }

        if ($data != null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    protected function error(string $message, int $code = 400, $error = null): JsonResponse
    {
        $response = [
            'status' => 'error',
            'message' => $message,
        ];

        if ($error)
            $response['error'] = $error;

        return response()->json($response, $code);
    }

    protected function created($data = null, string $message = 'Resouces created'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }
}
