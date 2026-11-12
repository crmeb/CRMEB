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
namespace app\listener\sms;

use crmeb\interfaces\ListenerInterface;
use crmeb\services\CacheService;
use crmeb\services\yihaotong\Serve;
use think\facade\Log;

/**
 * 一号通短信余额检查
 * Class SmsBalanceCheckListener
 * @package app\listener\sms
 */
class SmsBalanceCheckListener implements ListenerInterface
{
    /**
     * 短信余额预警阈值（条）
     */
    public const BALANCE_THRESHOLD = 100;

    /**
     * 实际请求一号通的节流窗口（秒）
     */
    public const CHECK_INTERVAL = 600;

    /**
     * 一号通短信余额检查（节流后请求 user/info，低于阈值记日志）
     * @param mixed $event
     * @return void
     */
    public function handle($event): void
    {
        try {
            //节流：窗口期内不重复请求一号通
            if (CacheService::get('sms_balance_check_time')) {
                return;
            }
            CacheService::set('sms_balance_check_time', time(), self::CHECK_INTERVAL);

            /** @var Serve $serve */
            $serve = app()->make(Serve::class, [yihaotong_config()]);
            $info = $serve->getUser();
            //未开通一号通短信不处理
            if (empty($info['sms']['open'])) {
                return;
            }
            $num = (int)($info['sms']['num'] ?? 0);
            if ($num <= self::BALANCE_THRESHOLD) {
                Log::error('一号通短信余额不足，剩余' . $num . '条，请及时充值');
            }
        } catch (\Throwable $e) {
            Log::error('一号通短信余额检查失败:' . $e->getMessage());
        }
    }
}
