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

namespace app\api;

use app\http\BaseApiExceptionHandle;

/**
 * C 端用户 API 异常处理器
 * 通用日志与渲染流程见基类，本类仅声明操作者身份标识
 */
class ApiExceptionHandle extends BaseApiExceptionHandle
{
    /**
     * 日志首列操作者身份标识（C 端用户 ID，游客为 0）
     * @return mixed
     */
    protected function operatorId()
    {
        return request()->uid();
    }
}
