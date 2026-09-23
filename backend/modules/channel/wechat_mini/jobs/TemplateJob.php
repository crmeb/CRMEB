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

namespace modules\channel\wechat_mini\jobs;

use crmeb\basic\BaseJobs;
use modules\channel\wechat_mini\service\RoutineSubscribeServices;
use think\facade\Log;

/**
 * 小程序模板消息任务
 */
class TemplateJob extends BaseJobs
{

    /**
     * 发送小程序订阅消息
     * @param string $openid
     * @param string $tempId
     * @param array $data
     * @param string|null $link
     * @param string|null $color
     * @param string $mark
     * @return bool
     */
    public function doJob($openid, $tempId, $data, $link, $color, $mark = '')
    {
        try {
            if (!$openid) {
                Log::warning('小程序订阅消息队列任务跳过：openid为空 ' . json_encode([
                    'mark' => $mark,
                    'template_id' => $tempId,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                return true;
            }

            $template = new RoutineSubscribeServices();
            $template->to($openid);
            if ($color) {
                $template->color($color);
            }
            if ($link) {
                $template->url($link);
            }
            return $template->send($tempId, $data);
        } catch (\Throwable $e) {
            Log::error('小程序订阅消息队列任务异常 ' . json_encode([
                'mark' => $mark,
                'template_id' => $tempId,
                'has_openid' => (bool)$openid,
                'exception' => get_class($e),
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            throw $e;
        }
    }

}
