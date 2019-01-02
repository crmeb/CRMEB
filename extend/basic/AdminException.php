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
use think\Log;
use think\Request;
use think\Url;

class AdminException extends Handle
{

    public function render(Exception $e){
        // 参数验证错误
        if ($e instanceof ValidateException) {
            return json($e->getError(), 422);
        }
        // 请求异常
        if ($e instanceof HttpException && request()->isAjax()) {
            return JsonService::fail('系统错误');
        }else{
            if(config("app_debug")==true){              //如是开启调试，就走原来的方法
                return parent::render($e);
            }else {
                $title = '系统错误';
                $msg = addslashes($e->getMessage());
                $this->recordErrorLog($e);
                exit(view('public/500', compact('title', 'msg'))->getContent());
            }
        }
    }
    /*
    * 将异常写入日志
    */
    private function recordErrorLog(Exception $e) {
        Log::init([
            'type' => 'File',
            'path' => LOG_PATH,
            'level' => ['error'],
        ]);
        Log::record($e->getMessage(), 'error');
    }
}