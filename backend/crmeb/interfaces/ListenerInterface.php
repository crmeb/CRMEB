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


interface ListenerInterface
{
    /**
     * 处理事件
     * 普通事件（fire-and-forget）无需返回值；
     * 查询型事件（query/collect）可返回数据
     * 子类可按需添加 :void / :?array / :array 等返回类型声明
     * @param $event 事件数据
     */
    public function handle($event);
}
