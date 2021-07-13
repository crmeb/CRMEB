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

namespace app\api\controller\pc;


use app\Request;
use app\services\pc\UserServices;

class UserController
{
    protected $services;

    public function __construct(UserServices $services)
    {
        $this->services = $services;
    }

    /**
     * 用户资金记录明细
     * @param Request $request
     * @param $type 0 全部  1 消费  2 充值  3 返佣  4 提现
     * @return mixed
     */
    public function getBalanceRecord(Request $request, $type)
    {
        $uid = (int)$request->uid();
        return app('json')->successful($this->services->getBalanceRecord($uid, $type));
    }

    /**
     * 获取收藏列表
     * @param Request $request
     * @return mixed
     */
    public function getCollectList(Request $request)
    {
        $uid = (int)$request->uid();
        return app('json')->successful($this->services->getCollectList($uid));
    }
}
