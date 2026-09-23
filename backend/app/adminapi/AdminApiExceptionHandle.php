<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\adminapi;

use app\http\BaseApiExceptionHandle;
use think\exception\HttpResponseException;
use think\Response;
use Throwable;

/**
 * 管理后台 API 异常处理器
 * 通用日志与渲染流程见基类，本类保留管理端差异：日志兜底与 HttpResponseException 前置分支
 */
class AdminApiExceptionHandle extends BaseApiExceptionHandle
{
    /**
     * 日志首列操作者身份标识（管理员 ID）
     * @return mixed
     */
    protected function operatorId()
    {
        return request()->adminId();
    }

    /**
     * 启用 report 外层兜底：认证宏未注入（异常发生在认证中间件之前）时降级为记录错误消息
     * @return bool
     */
    protected function guardReport(): bool
    {
        return true;
    }

    /**
     * render 前置钩子：HttpResponseException 直接交由框架默认渲染，保留其原始响应
     * @param \think\Request $request
     * @param Throwable $e
     * @return Response|null
     */
    protected function renderCustom($request, Throwable $e): ?Response
    {
        if ($e instanceof HttpResponseException) {
            return parent::render($request, $e);
        }
        return null;
    }
}
