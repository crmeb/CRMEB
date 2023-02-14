<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\jobs\notice;

use app\services\message\notice\SmsService;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;
use think\facade\Log;


class SmsJob extends BaseJobs
{
    use QueueTrait;

    /**
     * 发送短信
     * @param $switch
     * @param $adminList
     * @param $order
     * @return bool
     */
    public function doJob($phone, array $data, string $template)
    {

        try{
            /** @var SmsService $smsServices */
            $smsServices = app()->make(SmsService::class);
            $smsServices->send(true, $phone, $data, $template);
            return true;
        }catch (\Throwable $e) {
            Log::error('发送短信失败,失败原因:' . $e->getMessage());
        }

    }

}
