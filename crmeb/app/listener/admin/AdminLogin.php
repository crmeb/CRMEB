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

/**
 * Class AdminLogin
 * @package app\listener\admin
 */
class AdminLogin
{

    public function handle($event)
    {
        $res = false;
        $res1 = false;
        try {
            [$key] = $event;
            //检测消息队列是否执行
            $path = root_path('runtime') . '.queue';
            $content = file_get_contents($path);
            $res = $key === $content;
            unlink($path);
        } catch (\Throwable $e) {
        }

        try {

            $timerPath = root_path('runtime') . '.timer';
            $timer = file_get_contents($timerPath);
            if ($timer && $timer <= time() && $timer > (time() - 10)) {
                $res1 = true;
            }

        } catch (\Throwable $e) {
        }

        return [$res, $res1];
    }
}
