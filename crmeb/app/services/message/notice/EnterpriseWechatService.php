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

namespace app\services\message\notice;

use app\jobs\notice\EnterpriseWechatJob;
use app\services\message\NoticeService;
use crmeb\services\HttpService;
use think\facade\Log;

/**
 * 企业微信发送消息
 * Created by PhpStorm.
 * User: xurongyao <763569752@qq.com>
 * Date: 2021/9/22 1:23 PM
 */
class EnterpriseWechatService extends NoticeService
{
    /**
     * 判断是否开启权限
     * @var bool
     */
    private $isOpen = true;

    /**
     * 是否开启权限
     * @param string $mark
     * @return $this
     */
    public function isOpen(string $mark)
    {
        $this->isOpen = $this->noticeInfo['is_ent_wechat'] == 1 && $this->noticeInfo['url'] !== '';
        return $this;

    }

    /**
     * 发送企业微信客服消息
     * @param $data
     */
    public function weComSend($data)
    {
        if ($this->isOpen) {
            $url = $this->noticeInfo['url'];
            $ent_wechat_text = $this->noticeInfo['ent_wechat_text'];
            try {
                $str = $ent_wechat_text;
                foreach ($data as $key => $item) {
                    $str = str_replace('{' . $key . '}', $item, $str);
                }
                $s = explode('\n', $str);
                $d = '';
                foreach ($s as $item) {
                    $d .= $item . "\n>";
                }
                $d = substr($d, 0, strlen($d) - 2);
                HttpService::postRequest($url, json_encode([
                    'msgtype' => 'markdown',
                    'markdown' => ['content' => $d]
                ]));
            } catch (\Throwable $e) {
                Log::error('发送企业群消息失败,失败原因:' . $e->getMessage());

            }
        }
    }
}
