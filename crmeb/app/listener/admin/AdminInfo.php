<?php
/**
 *  +----------------------------------------------------------------------
 *  | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
 *  +----------------------------------------------------------------------
 *  | Author: CRMEB Team <admin@crmeb.com>
 *  +----------------------------------------------------------------------
 */

namespace app\listener\admin;


use app\jobs\CheckQueueJob;
use crmeb\interfaces\ListenerInterface;

/**
 * 登录前获取信息
 * Class AdminInfo
 * @package app\listener\admin
 */
class AdminInfo implements ListenerInterface
{

    public function handle($event): void
    {
        CheckQueueJob::dispatch($event);
    }
}
