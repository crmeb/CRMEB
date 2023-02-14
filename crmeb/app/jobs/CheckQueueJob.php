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

namespace app\jobs;


use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;

/**
 * 检测消息队列是否执行
 * Class CheckQueueJob
 * @package app\jobs
 */
class CheckQueueJob extends BaseJobs
{
    use QueueTrait;

    public function doJob($key)
    {
        $path = root_path('runtime') . '.queue';
        file_put_contents($path, $key);
        return true;
    }
}
