<?php


namespace app\api;


use think\exception\DbException;
use think\exception\Handle;
use think\Response;
use Throwable;

class ApiExceptionHandle extends Handle
{

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // 添加自定义异常处理机制
        if ($e instanceof DbException) {
            return app('json')->fail('数据获取失败', [
                'file' => $e->getFile(),
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);
        } else {
            return app('json')->fail('系统出现异常', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'code' => $e->getCode(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),
                'previous' => $e->getPrevious(),
            ]);
        }
    }

}