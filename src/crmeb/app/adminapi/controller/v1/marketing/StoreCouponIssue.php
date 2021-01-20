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
namespace app\adminapi\controller\v1\marketing;

use app\adminapi\controller\AuthController;
use app\services\coupon\StoreCouponIssueServices;
use app\services\product\product\StoreProductCouponServices;
use app\services\product\product\StoreProductServices;
use think\facade\App;

/**
 * 已发布优惠券管理
 * Class StoreCouponIssue
 * @package app\adminapi\controller\v1\marketing
 */
class StoreCouponIssue extends AuthController
{
    public function __construct(App $app, StoreCouponIssueServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取优惠券列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['status', 1],
            ['coupon_title', ''],
            ['receive_type', ''],
            ['type', ''],
        ]);
        $list = $this->services->getCouponIssueList($where);
        return app('json')->success($list);
    }

    /**
     * 添加优惠券
     * @return mixed
     */
    public function saveCoupon()
    {
        $data = $this->request->postMore([
            ['coupon_title', ''],
            ['coupon_price', 0.00],
            ['use_min_price', 0.00],
            ['coupon_time', 0],
            ['start_use_time', 0],
            ['end_use_time', 0],
            ['start_time', 0],
            ['end_time', 0],
            ['receive_type', 0],
            ['is_permanent', 0],
            ['total_count', 0],
            ['product_id', ''],
            ['category_id', 0],
            ['type', 0],
            ['sort', 0],
            ['status', 0],
        ]);
        if ($data['start_time'] && $data['start_use_time']) {
            if ($data['start_use_time'] < $data['start_time']) {
                return app('json')->fail('使用开始时间不能小于领取开始时间');
            }
        }
        if ($data['end_time'] && $data['end_use_time']) {
            if ($data['end_use_time'] < $data['end_time']) {
                return app('json')->fail('使用结束时间不能小于领取结束时间');
            }
        }
        $res = $this->services->saveCoupon($data);
        if ($res) return app('json')->success('添加成功!');
    }

    /**
     * 修改优惠券状态
     * @param $id
     * @param $status
     * @return mixed
     */
    public function status($id, $status)
    {
        $this->services->update($id, ['status' => $status]);
        return app('json')->success('修改成功');
    }

    /**
     * 复制优惠券获取优惠券详情
     * @param int $id
     * @return mixed
     */
    public function copy($id = 0)
    {
        if (!$id) return app('json')->fail('参数错误');
        $info = $this->services->get($id);
        if ($info) $info = $info->toArray();
        if ($info['product_id'] != '') {
            $productIds = explode(',', $info['product_id']);
            /** @var StoreProductServices $product */
            $product = app()->make(StoreProductServices::class);
            $productImages = $product->getColumn([['id', 'in', $productIds]], 'image', 'id');
            foreach ($productIds as $item) {
                $info['productInfo'][] = [
                    'product_id' => $item,
                    'image' => $productImages[$item]
                ];
            }
        }
        return app('json')->success($info);
    }

    /**
     * 删除
     * @param string $id
     * @return mixed
     */
    public function delete($id)
    {
        $this->services->update($id, ['is_del' => 1]);
        /** @var StoreProductCouponServices $storeProductService */
        $storeProductService = app()->make(StoreProductCouponServices::class);
        //删除商品关联这个优惠券
        $storeProductService->delete(['issue_coupon_id' => $id]);
        return app('json')->success('删除成功!');
    }

    /**
     * 修改状态
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function edit($id)
    {
        return app('json')->success($this->services->createForm($id));
    }

    /**
     * 领取记录
     * @param string $id
     * @return mixed|string
     */
    public function issue_log($id)
    {
        $list = $this->services->issueLog($id);
        return app('json')->success($list);
    }
}
