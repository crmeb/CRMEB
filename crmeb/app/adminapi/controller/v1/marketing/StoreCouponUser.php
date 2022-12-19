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
namespace app\adminapi\controller\v1\marketing;

use app\adminapi\controller\AuthController;
use app\services\activity\coupon\StoreCouponIssueServices;
use app\services\activity\coupon\StoreCouponUserServices;
use think\facade\App;

/**
 * 优惠券发放记录控制器
 * Class StoreCategory
 * @package app\admin\controller\system
 */
class StoreCouponUser extends AuthController
{
    public function __construct(App $app, StoreCouponUserServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 用户领取记录
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['status', ''],
            ['coupon_title', ''],
            ['nickname', ''],
        ]);
        $list = $this->services->systemPage($where);
        return app('json')->success($list);
    }

    /**
     * 发放优惠券到指定个人
     * @return mixed
     */
    public function grant()
    {
        $data = $this->request->postMore([
            ['id', 0],
            ['uid', '']
        ]);
        if (!$data['id']) return app('json')->fail(100100);
        /** @var StoreCouponIssueServices $issueService */
        $issueService = app()->make(StoreCouponIssueServices::class);
        $coupon = $issueService->get($data['id']);
        if (!$coupon) {
            return app('json')->fail(100026);
        } else {
            $coupon = $coupon->toArray();
        }
        $user = explode(',', $data['uid']);
        if (!$issueService->setCoupon($coupon, $user))
            return app('json')->fail(100031);
        else
            return app('json')->success(100030);

    }
}
