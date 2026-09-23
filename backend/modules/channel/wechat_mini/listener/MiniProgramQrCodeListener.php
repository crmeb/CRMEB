<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------

namespace modules\channel\wechat_mini\listener;

use modules\channel\wechat_mini\sdk\MiniProgramService;

/**
 * 小程序核销码生成监听器
 * 监听 app 触发的小程序核销码生成事件
 */
class MiniProgramQrCodeListener
{
    /**
     * 处理小程序核销码生成事件
     * @param array $params ['data' => array, 'page' => string, 'size' => int]
     * @return string|null 生成的二维码内容
     */
    public function handle(array $params): ?string
    {
        return MiniProgramService::appCodeUnlimitService(
            $params['data'],
            $params['page'],
            $params['size']
        );
    }
}
