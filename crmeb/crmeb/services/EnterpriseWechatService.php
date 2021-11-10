<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\services;

class EnterpriseWechatService
{


    public function send($content,$msgtype='')
    {
        try {
            $url = sys_config('enterprise_wechat_url');
            $data = [
                'msgtype' => $msgtype,
                'markdown' => ['content'=>$content],
                'mentioned_mobile_list'=>['@18161705878']

            ];
            HttpService::postRequest($url,json_encode($data));
        } catch (\Throwable $e) {
            Log::error('发送企业群消息失败,失败原因:' . $e->getMessage());
        }
    }

}
