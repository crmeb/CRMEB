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
namespace crmeb\subscribes;


/**
 * 用户事件
 * Class UserSubscribe
 * @package crmeb\subscribes
 */
class UserSubscribe
{

    public function handle()
    {

    }

    /**
     * 管理员后台给用户添加金额
     * @param $event
     */
    public function onAdminAddMoney($event)
    {
        list($user, $money) = $event;
        //$user 用户信息
        //$money 添加的金额
    }

}
