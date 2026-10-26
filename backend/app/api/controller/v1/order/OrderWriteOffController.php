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
declare(strict_types=1);

namespace app\api\controller\v1\order;

use app\Request;
use app\services\order\OrderWriteOffServices;

/**
 * 移动端订单核销（店员/配送员）
 */
class OrderWriteOffController
{
    /**
     * 查询或执行核销
     * is_confirm=0 返回订单确认信息；=1 执行核销
     * @param Request $request
     * @param OrderWriteOffServices $services
     * @return mixed
     */
    public function orderVerific(Request $request, OrderWriteOffServices $services)
    {
        [$verifyCode, $isConfirm] = $request->postMore([
            ['verify_code', ''],
            ['is_confirm', 0],
        ], true);

        $verifyCode = trim((string)$verifyCode);
        if ($verifyCode === '') {
            return app('json')->fail('请输入核销码或者扫描核销二维码');
        }

        $uid = (int)$request->uid();
        // 不允许客户端跳过权限校验（auth 固定 0）
        $orderInfo = $services->writeOffOrder($verifyCode, (int)$isConfirm, $uid, 0);

        if ((int)$isConfirm === 0) {
            return app('json')->success([
                'order_id' => (string)($orderInfo['order_id'] ?? $orderInfo['order_no'] ?? ''),
                'order_no' => (string)($orderInfo['order_no'] ?? ''),
                'image' => (string)($orderInfo['image'] ?? ''),
                'title' => (string)($orderInfo['title'] ?? ''),
                'nickname' => (string)($orderInfo['nickname'] ?? ''),
                'verify_code' => (string)($orderInfo['verify_code'] ?? $verifyCode),
                'shipping_type' => (int)($orderInfo['shipping_type'] ?? 0),
                'pay_price' => $orderInfo['pay_price'] ?? 0,
                'total_num' => (int)($orderInfo['total_num'] ?? 0),
            ]);
        }

        return app('json')->success('核销成功');
    }
}
