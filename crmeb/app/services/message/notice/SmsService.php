<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\message\notice;

use app\jobs\notice\SmsJob;
use app\jobs\TaskJob;
use app\services\message\NoticeService;
use app\services\kefu\service\StoreServiceServices;
use app\services\message\SystemNotificationServices;
use app\services\serve\ServeServices;
use crmeb\exceptions\ApiException;
use crmeb\services\CacheService;
use think\facade\Log;


/**
 * 短信发送消息列表
 * Created by PhpStorm.
 * User: xurongyao <763569752@qq.com>
 * Date: 2021/9/22 1:23 PM
 */
class SmsService extends NoticeService
{
    /**
     * 短信类型
     * @var string[]
     */
    private $smsType = ['yihaotong', 'aliyun', 'tencent'];

    /**
     * 发送短信消息
     * @param $phone
     * @param array $data
     * @return bool|void
     */
    public function sendSms($phone, array $data)
    {
        try {
            if ($this->noticeInfo['is_sms'] == 1) {
                try {
                    $this->send(true, $phone, $data, $this->noticeInfo['mark']);
                    return true;
                } catch (\Throwable $e) {
                    Log::error('发送短信失败,失败原因:' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return true;
        }
    }

    /**
     * 发送短信
     * @param bool $switch
     * @param $phone
     * @param array $data
     * @param string $mark
     * @return bool
     */
    public function send(bool $switch, $phone, array $data, string $mark)
    {
        if ($switch && $phone) {
            //获取发送短信驱动类型
            $type = $this->smsType[sys_config('sms_type', 0)];
            if ($type == 'tencent') {
                $data = $this->handleTencent($mark, $data);
            }
            $smsMake = app()->make(ServeServices::class)->sms($type);
            $smsId = $mark == 'verify_code' ? app()->make(SystemNotificationServices::class)->value(['mark' => 'verify_code'], 'sms_id') : $this->noticeInfo['sms_id'];
            //发送短信
            $res = $smsMake->send($phone, $smsId, $data);
            if ($res === false) {
                throw new ApiException($smsMake->getError());
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * 退款发送管理员消息任务
     * @param $order
     * @return bool
     */
    public function sendAdminRefund($order)
    {
        if ($this->noticeInfo['is_sms'] == 1) {
            /** @var StoreServiceServices $StoreServiceServices */
            $StoreServiceServices = app()->make(StoreServiceServices::class);
            $adminList = $StoreServiceServices->getStoreServiceOrderNotice();

            foreach ($adminList as $item) {
                $data = ['order_id' => $order['order_id'], 'admin_name' => $item['nickname']];
                $this->sendSms($item['phone'], $data);
            }
        }
        return true;
    }

    /**
     * 用户确认收货管理员短信提醒
     * @param $switch
     * @param $adminList
     * @param $order
     * @return bool
     */
    public function sendAdminConfirmTakeOver($order)
    {
        if ($this->noticeInfo['is_sms'] == 1) {
            /** @var StoreServiceServices $StoreServiceServices */
            $StoreServiceServices = app()->make(StoreServiceServices::class);
            $adminList = $StoreServiceServices->getStoreServiceOrderNotice();
            foreach ($adminList as $item) {
                $data = ['order_id' => $order['order_id'], 'admin_name' => $item['nickname']];
                $this->sendSms($item['phone'], $data);
            }
        }
        return true;
    }

    /**
     * 下单成功给客服管理员发送短信
     * @param $switch
     * @param $adminList
     * @param $order
     * @return bool
     */
    public function sendAdminPaySuccess($order)
    {
        if ($this->noticeInfo['is_sms'] == 1) {
            /** @var StoreServiceServices $StoreServiceServices */
            $StoreServiceServices = app()->make(StoreServiceServices::class);
            $adminList = $StoreServiceServices->getStoreServiceOrderNotice();
            foreach ($adminList as $item) {
                $data = ['order_id' => $order['order_id'], 'admin_name' => $item['nickname']];
                $this->sendSms($item['phone'], $data);
            }
        }
        return true;
    }

    /**
     * 处理腾讯云参数
     * @param $mark
     * @param $data
     * @return array
     */
    public function handleTencent($mark, $data)
    {
        $result = [];
        switch ($mark) {
            case 'verify_code':
                $result = [(string)$data['code'], (string)$data['time']];
                break;
            case 'send_order_refund_no_status':
            case 'order_pay_false':
                $result = [$data['order_id']];
                break;
            case 'price_revision':
                $result = [$data['order_id'], (string)$data['pay_price']];
                break;
            case 'order_pay_success':
                $result = [(string)$data['pay_price'], $data['order_id']];
                break;
            case 'order_take':
                $result = [$data['order_id'], $data['store_name']];
                break;
            case 'send_order_apply_refund':
            case 'admin_pay_success_code':
            case 'send_admin_confirm_take_over':
                $result = [$data['admin_name'], $data['order_id']];
                break;
            case 'order_deliver_success':
            case 'order_postage_success':
                $result = [$data['nickname'], $data['store_name'], $data['order_id']];
                break;
            case 'order_refund':
                $result = [$data['order_id'], $data['refund_price']];
                break;
            case 'recharge_success':
                $result = [$data['price'], $data['now_money']];
                break;
            case 'sign_remind':
                $result = [$data['site_name']];
                break;
        }
        return $result;
    }
}
