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

use app\services\message\NoticeService;
use app\services\kefu\service\StoreServiceServices;
use app\services\message\MessageSystemServices;
use think\facade\Log;

/**
 * 短信发送消息列表
 * Created by PhpStorm.
 * User: xurongyao <763569752@qq.com>
 * Date: 2021/9/22 1:23 PM
 */
class SystemMsgService extends NoticeService
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
        $this->isOpen = $this->noticeInfo['is_system'] === 1;
        return $this;

    }

    /**
     * 发送消息
     * @param int $uid
     * @param $data
     * @return bool|void
     */
    public function sendMsg(int $uid, $data)
    {
        $this->isOpen = $this->noticeInfo['is_system'] === 1;
        try {
            if ($this->isOpen) {
                $title = $this->noticeInfo['system_title'];
                $str = $this->noticeInfo['system_text'];
                foreach ($data as $key => $item) {
                    $str = str_replace('{' . $key . '}', $item, $str);
                    $title = str_replace('{' . $key . '}', $item, $title);
                }
                $sdata = [];
                $sdata['mark'] = $this->noticeInfo['mark'];
                $sdata['uid'] = $uid;
                $sdata['content'] = $str;
                $sdata['title'] = $title;
                $sdata['type'] = 1;
                $sdata['add_time'] = time();
                $sdata['data'] = json_encode($data);
                /** @var MessageSystemServices $MessageSystemServices */
                $MessageSystemServices = app()->make(MessageSystemServices::class);
                $MessageSystemServices->save($sdata);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return true;
        }
    }

    /**
     * 给客服发站内信
     * @param $data
     * @return bool|void
     */
    public function kefuSystemSend($data)
    {
        /** @var MessageSystemServices $MessageSystemServices */
        $MessageSystemServices = app()->make(MessageSystemServices::class);
        /** @var StoreServiceServices $StoreServiceServices */
        $StoreServiceServices = app()->make(StoreServiceServices::class);
        $adminList = $StoreServiceServices->getStoreServiceOrderNotice();
        try {
            if ($this->isOpen) {
                $save = [];
                $title = $this->noticeInfo['system_title'];
                $str = $this->noticeInfo['system_text'];
                foreach ($data as $k => $val) {
                    $str = str_replace('{' . $k . '}', $val, $str);
                    $title = str_replace('{' . $k . '}', $val, $title);
                }
                foreach ($adminList as $key => $item) {
                    $save[$key]['mark'] = $this->noticeInfo['mark'];
                    $save[$key]['uid'] = $item['uid'];
                    $save[$key]['content'] = $str;
                    $save[$key]['title'] = $title;
                    $save[$key]['type'] = 2;
                    $save[$key]['add_time'] = time();
                    $save[$key]['data'] = json_encode($data);
                }
                $MessageSystemServices->saveAll($save);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return true;
        }
    }
}
