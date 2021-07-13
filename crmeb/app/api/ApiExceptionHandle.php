<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\api;


use crmeb\exceptions\ApiException;
use crmeb\exceptions\AuthException;
use think\exception\DbException;
use think\exception\Handle;
use think\facade\Env;
use think\Response;
use Throwable;
use think\exception\ValidateException;

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
        } else if ($e instanceof AuthException || $e instanceof ApiException || $e instanceof ValidateException) {
            return app('json')->fail($e->getMessage());
        } else {
            return app('json')->fail('很抱歉!系统开小差了', Env::get('app_debug', false) ? [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'code' => $e->getCode(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),
                'previous' => $e->getPrevious(),
            ] : []);
        }
    }

}
