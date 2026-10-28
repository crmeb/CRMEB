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
namespace app\adminapi\controller\merchant;

use app\adminapi\controller\AuthController;
use app\services\user\UserServices;
use think\facade\App;

/**
 * 核销订单
 * Class SystemVerifyOrder
 * @package app\adminapi\controller\merchant
 */
class SystemVerifyOrderController extends AuthController
{
    /**
     * 构造方法
     * SystemVerifyOrder constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * 订单列表推荐人详细
     * @param $uid
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function orderSpreadUser($uid)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $spread = [];
        $spread = $userServices->getUserInfo((int)$uid);
        if ($spread) {
            $spread = $spread->toArray();
            $spread['birthday'] = $spread['birthday'] ? date('Y-m-d', $spread['birthday']) : '';
            $spread['last_time'] = $spread['last_time'] ? date('Y-m-d H:i:s', $spread['last_time']) : '';
        }
        return app('json')->success(['spread' => $spread]);
    }
}
