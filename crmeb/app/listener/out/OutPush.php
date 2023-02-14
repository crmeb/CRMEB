<?php

namespace app\listener\out;

use app\jobs\OutPushJob;
use app\services\out\OutAccountServices;
use crmeb\interfaces\ListenerInterface;
use crmeb\services\CacheService;
use crmeb\services\HttpService;
use think\facade\Log;

class OutPush implements ListenerInterface
{
    public function handle($event): void
    {
        [$type, $data] = $event;
        /** @var OutAccountServices $outAccountServices */
        $outAccountServices = app()->make(OutAccountServices::class);
        $outAccountList = $outAccountServices->selectList(['is_del' => 0, 'status' => 1])->toArray();
        foreach ($outAccountList as $item) {
            if ($item['push_open'] == 1) {
                $token = $this->getPushToken($item);
                if ($type == 'order_create_push') {
                    OutPushJob::dispatch('orderCreate', [$data['order_id'], $item['order_create_push'] . '?pushToken=' . $token]);
                } elseif ($type == 'order_pay_push') {
                    OutPushJob::dispatch('paySuccess', [$data['order_id'], $item['order_pay_push'] . '?pushToken=' . $token]);
                } elseif ($type == 'refund_create_push') {
                    OutPushJob::dispatch('refundCreate', [$data['order_id'], $item['refund_create_push'] . '?pushToken=' . $token]);
                } elseif ($type == 'refund_cancel_push') {
                    OutPushJob::dispatch('refundCancel', [$data['order_id'], $item['refund_cancel_push'] . '?pushToken=' . $token]);
                } elseif ($type == 'user_update_push') {
                    OutPushJob::dispatch('userUpdate', [$data, $item['user_update_push'] . '?pushToken=' . $token]);
                }
            }
        }
    }

    /**
     * 获取推送token
     * @param array $info
     * @return false|mixed
     */
    public function getPushToken(array $info)
    {
        $token = CacheService::get('pushToken' . $info['id']);
        if (!$token) {
            $param = json_encode(['push_account' => $info['push_account'], 'push_password' => $info['push_password']], JSON_UNESCAPED_UNICODE);
            $res = HttpService::postRequest($info['push_token_url'], $param, ['Content-Type:application/json', 'Content-Length:' . strlen($param)]);
            $res = $res ? json_decode($res, true) : [];
            if (!$res || !isset($res['code']) || $res['code'] != 0) {
                Log::error(['msg' => $info['title'] . '，获取token失败']);
                return false;
            }
            CacheService::set('pushToken' . $info['id'], $res['token'], $res['time']);
            return $res['token'];
        } else {
            return $token;
        }

    }
}