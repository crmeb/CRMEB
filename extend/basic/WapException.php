<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2018/01/10
 */

namespace basic;


use Exception;
use service\JsonService;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\ValidateException;
use think\Request;
use think\Url;

class WapException extends Handle
{
    public function render(Exception $e){

        //可以在此交由系统处理
//        if(Request::instance()->get('_debug_info') == 'true')
            return parent::render($e);

        // 参数验证错误
        if ($e instanceof ValidateException) {
            return json($e->getError(), 422);
        }
        // 请求异常
        if ($e instanceof HttpException && request()->isAjax()) {
            return JsonService::fail('系统错误');
        }else{
            $url = 0;
            $title = '系统错误';
            $msg = addslashes($e->getMessage());
            exit(view('public/error',compact('title', 'msg', 'url'))->getContent());
        }
    }
}