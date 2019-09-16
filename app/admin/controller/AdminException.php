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
        // 请求异常
        if (env("APP_DEBUG") == true) {              //如是开启调试，就走原来的方法
            return parent::render($request, $e);
        } else {
            if ($e instanceof \Exception && request()->isAjax()) {
                return app('json')->fail(['code' => $e->getCode(), 'message' => $e->getMessage(), 'file' => $e->getFile()]);
            } else {
                $title = '系统错误';
                $msg = addslashes($e->getMessage());
                return \response(view('public/500', compact('title', 'msg'))->getContent());
            }
        }
    }
}