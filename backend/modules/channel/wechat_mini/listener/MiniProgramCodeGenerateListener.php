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
declare(strict_types=1);

namespace modules\channel\wechat_mini\listener;

use crmeb\interfaces\ListenerInterface;
use modules\channel\wechat_mini\sdk\MiniProgramService;
use think\facade\Log;

/**
 * 小程序码生成监听器
 * 响应 app 层 MiniProgramCodeGenerate 事件，调用小程序 SDK 生成小程序码
 */
class MiniProgramCodeGenerateListener implements ListenerInterface
{
    /**
     * 生成小程序码
     * @param mixed $event 事件数据，结构 ['scene' => mixed, 'page' => string, 'width' => int]
     * @return mixed 返回小程序码图片资源，失败返回 false
     */
    public function handle($event)
    {
        $scene = $event['scene'] ?? '';
        $page = $event['page'] ?? null;
        $width = (int)($event['width'] ?? 430);

        try {
            return MiniProgramService::appCodeUnlimitService($scene, $page, $width);
        } catch (\Throwable $e) {
            Log::warning('[WechatMini] MiniProgramCodeGenerate failed: ' . $e->getMessage());
            return false;
        }
    }
}
