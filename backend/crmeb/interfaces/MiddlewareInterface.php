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

namespace crmeb\interfaces;


use think\Request;

/**
 * 中间件契约接口
 * 使用 think\Request 作为类型提示（app\Request 继承 think\Request，里氏替换兼容）
 */
interface MiddlewareInterface
{
    public function handle(Request $request, \Closure $next);
}
