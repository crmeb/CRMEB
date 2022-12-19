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

namespace app\services\user;


use app\dao\user\UserBrokerageFrozenDao;
use app\services\BaseServices;

/**
 * 佣金冻结
 * Class UserBrokerageFrozenServices
 * @package app\services\user
 * @method getUserFrozenPrice(int $uid, bool $isFrozen) 获取某个账户下冻结过期的佣金
 * @method updateFrozen(string $orderId) 修改佣金冻结状态
 * @method getFrozenBrokerage() 获取用户的冻结佣金数组
 * @method getSumFrozenBrokerage() 获取冻结佣金金额
 */
class UserBrokerageFrozenServices extends BaseServices
{
    /**
     * UserBrokerageFrozenServices constructor.
     * @param UserBrokerageFrozenDao $dao
     */
    public function __construct(UserBrokerageFrozenDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 保存冻结金额
     * @param int $uid
     * @param string $price
     * @param int $uillId
     * @param string $orderId
     * @return mixed
     */
    public function saveBrokage(int $uid, string $price, int $uillId, string $orderId)
    {
        $broken_time = intval(sys_config('extract_time'));
        $data = [
            'uid' => $uid,
            'price' => $price,
            'uill_id' => $uillId,
            'order_id' => $orderId,
            'add_time' => time(),
            'status' => 1,
            'frozen_time' => time() + $broken_time * 86400
        ];
        return $this->dao->save($data);
    }
}
