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

namespace app\api\controller\v1;


use app\Request;
use app\services\PlatformDetector;
use app\services\driver\pay\PayChannelServices;
use crmeb\services\pay\Pay;

/**
 * 支付回调
 * Class PayController
 * @package app\api\controller\v1
 */
class PayController
{

    /**
     * 支付回调（根据扩展自动路由）
     * @param string $type 回调类型（对应channel_key）
     * @return string|\think\Response
     * @throws \Exception
     */
    public function notify(string $type): string|\think\Response
    {
        /** @var \app\services\pay\PayServices $payServices */
        $payServices = app()->make(\app\services\pay\PayServices::class);
        return $payServices->handleNotify(urldecode($type));
    }

    /**
     * 支付配置（动态读取支付方式字典表）
     * @param Request $request
     * @return mixed
     */
    public function config(Request $request)
    {
        /** @var PayChannelServices $payChannelServices */
        $payChannelServices = app()->make(PayChannelServices::class);

        // 自动识别当前平台（从 config/platform.php 动态检测，零硬编码）
        $platform = PlatformDetector::detect();
        $userBalance = $request->user('now_money');

        $config = $payChannelServices->getAvailableChannels($platform, $userBalance);

        return app('json')->success($config);
    }

    public function transferNotify()
    {
        return app()->make(Pay::class, ['v3_wechat_pay'])->handleTransferNotify();
    }
}
