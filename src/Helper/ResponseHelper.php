<?php

use Illuminate\Http\JsonResponse;

if (!function_exists('response_success')) {
    /**
     * @param string $message
     * @param array $data
     * @param int|string $code
     * @param int $httpCode
     * @return JsonResponse
     */
    function response_success(string $message = 'success', $data = [], int|string $code = '0000', int $httpCode = 200): JsonResponse
    {
        return response()->json([
            'code' => $code,
            'msg' => $message,
            'data' => $data
        ], $httpCode);
    }
}

if (!function_exists('response_error')) {
    /**
     * @param string $message
     * @param array $data
     * @param int|string $code
     * @param int $httpCode
     * @return JsonResponse
     */
    function response_error(string $message = 'error', $data = [], int|string|null $code = '9999', int $httpCode = 500): JsonResponse
    {
        return response()->json([
            'code' => $code ?: '9999',
            'msg' => $message,
            'errors' => $data
        ], $httpCode);
    }
}
