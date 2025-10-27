<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    // ===============================
    // 通用回傳方法
    // ===============================

    /**
     * 成功回傳
     *
     * @param mixed $data 回傳資料
     * @param string $msg 訊息
     * @param int $status HTTP 狀態碼
     * @return JsonResponse
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
     *
     * @param string $msg 訊息
     * @param int $status HTTP 狀態碼
     * @param mixed $data 附加資料
     * @return JsonResponse
     */
    protected function error(string $msg = 'error', int $status = 400, $data = null): JsonResponse
    {
        return response()->json([
            'msg' => $msg,
            'data' => $data,
        ], $status);
    }

    // ===============================
    // 語意化 CRUD 回傳方法
    // ===============================

    /**
     * 新增成功回傳 (201 Created)
     *
     * @param mixed $data 回傳資料
     * @param string $msg 訊息
     * @return JsonResponse
     */
    protected function created($data = null, string $msg = 'created'): JsonResponse
    {
        return $this->success($data, $msg, 201);
    }

    /**
     * 更新成功回傳 (200 OK)
     *
     * @param mixed $data 回傳資料
     * @param string $msg 訊息
     * @return JsonResponse
     */
    protected function updated($data = null, string $msg = 'updated'): JsonResponse
    {
        return $this->success($data, $msg, 200);
    }

    /**
     * 刪除成功回傳 (204 No Content)
     *
     * @param string $msg 訊息
     * @return JsonResponse
     */
    protected function deleted(string $msg = 'deleted'): JsonResponse
    {
        // 204 不回傳 body
        return response()->json(null, 204);
    }
}
