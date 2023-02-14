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

use app\services\message\notice\EnterpriseWechatService;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;
use think\facade\Log;

class EnterpriseWechatJob extends BaseJobs
{
    use QueueTrait;

    /**
     * 给企业微信群发送消息
     * @param $data
     * @return bool
     */
    public function doJob($data): bool
    {
        try {
            /** @var EnterpriseWechatService $enterpriseWechatService */
            $enterpriseWechatService = app()->make(EnterpriseWechatService::class);
            $enterpriseWechatService->weComSend($data);
            return true;
        } catch (\Exception $e) {
            Log::error('发送企业群消息失败,失败原因:' . $e->getMessage());
        }
    }
}
