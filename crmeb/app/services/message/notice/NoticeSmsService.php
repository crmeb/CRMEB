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

namespace app\services\message\notice;

use app\jobs\notice\SmsJob;
use app\services\message\NoticeService;
use app\services\message\service\StoreServiceServices;
use think\facade\Log;


/**
 * 短信发送消息列表
 * Created by PhpStorm.
 * User: xurongyao <763569752@qq.com>
 * Date: 2021/9/22 1:23 PM
 */
class NoticeSmsService extends NoticeService
{

    /**
     * 判断是否开启权限
     * @var bool
     */
    private $isopend = true;

    /**
     * 是否开启权限
     * @param string $mark
     * @return $this
     */
    public function isOpen(string $mark)
    {
        $this->isopend = $this->notceinfo['is_sms'] === 1;
        return $this;

    }


    /**
     * 发送短信消息
     * @param string $tempCode 模板消息常量名称
     * @param $uid uid
     * @param array $data 模板内容
     * @param string $link 跳转链接
     * @param string|null $color 文字颜色
     * @return bool|mixed
     */
    public function sendSms($phone, array $data, string $template)
    {
        try {
            $this->isopend = $this->notceinfo['is_sms'] === 1;
            if ($this->isopend) {
                SmsJob::dispatch('doJob', [$phone, $data, $template]);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return true;
        }
    }

    /**
     * 退款发送管理员消息任务
     * @param $order
     * @return bool
     */
    public function sendAdminRefund($order)
    {
        /** @var StoreServiceServices $StoreServiceServices */
        $StoreServiceServices = app()->make(StoreServiceServices::class);
        $adminList = $StoreServiceServices->getStoreServiceOrderNotice();

        foreach ($adminList as $item) {
            $data = ['order_id' => $order['order_id'], 'admin_name' => $item['nickname']];
            $this->sendSms($item['phone'], $data, 'ADMIN_RETURN_GOODS_CODE');
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
        /** @var StoreServiceServices $StoreServiceServices */
        $StoreServiceServices = app()->make(StoreServiceServices::class);
        $adminList = $StoreServiceServices->getStoreServiceOrderNotice();
        foreach ($adminList as $item) {
            $data = ['order_id' => $order['order_id'], 'admin_name' => $item['nickname']];
            $this->sendSms($item['phone'], $data, 'ADMIN_TAKE_DELIVERY_CODE');
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

        /** @var StoreServiceServices $StoreServiceServices */
        $StoreServiceServices = app()->make(StoreServiceServices::class);
        $adminList = $StoreServiceServices->getStoreServiceOrderNotice();
        foreach ($adminList as $item) {
            $data = ['order_id' => $order['order_id'], 'admin_name' => $item['nickname']];
            $this->sendSms($item['phone'], $data, 'ADMIN_PAY_SUCCESS_CODE');
        }
        return true;
    }


}
