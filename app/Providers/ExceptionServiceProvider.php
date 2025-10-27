<?php

namespace App\Providers;

use App\Exceptions\ApiException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $handler = $this->app->make(\Illuminate\Contracts\Debug\ExceptionHandler::class);

        $handler->renderable(function (\Throwable $e, $request) {

            if ($e instanceof ApiException) {
                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 400;
                return response()->json([
                    'msg' => $e->getMessage(),
                    'code' => $status,
                ], $status); //
            }

            // Token 過期 / 未授權
            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'msg' => 'Token 已過期或未授權，請重新登入',
                    'code' => 401,
                ], 401);
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'msg' => '驗證失敗',
                    'errors' => $e->errors(), // 會回傳 {field: [message]}
                    'code' => 422,
                ], 422);
            }

            // HTTP 例外（403, 404, 405…）
            if ($e instanceof HttpException) {
                return response()->json([
                    'msg' => $e->getMessage() ?: 'Http Error',
                    'code' => $e->getStatusCode(),
                ], $e->getStatusCode());
            }

            // 其他例外（500）
            return response()->json([
                'msg' => '伺服器錯誤',
                'code' => 500,
            ], 500);
        });
    }
}
