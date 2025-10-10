<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * 成功回傳
     */
    protected function success($data = null, string $msg = 'success', int $status = 200): JsonResponse
    {
        return response()->json([
            'msg' => $msg,
            'data' => $data,
        ], $status);
    }

    /**
     * 失敗回傳
     */
    protected function error(string $msg = 'error', int $status = 400, $data = null): JsonResponse
    {
        return response()->json([
            'msg' => $msg,
            'data' => $data,
        ], $status);
    }
}
