<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2018/01/10
 */

namespace app\admin\controller;

use think\exception\Handle;
use think\exception\ValidateException;
use think\Response;
use Throwable;

/**
 * 后台异常处理
 *
 * Class AdminException
 * @package app\admin\controller
 */
class AdminException extends Handle
{

    public function render($request, Throwable $e): Response
    {
        // 参数验证错误
        if ($e instanceof ValidateException) {
            return app('json')->make(422, $e->getError());
        }
        if ($e instanceof \Exception && request()->isAjax()) {
            return app('json')->fail($e->getMessage(), ['code' => $e->getCode(), 'line' => $e->getLine(), 'message' => $e->getMessage(), 'file' => $e->getFile()]);
        }

        return parent::render($request, $e);
    }
}