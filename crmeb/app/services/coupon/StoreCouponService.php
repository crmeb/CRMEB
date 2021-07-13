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

namespace app\services\coupon;


use app\dao\coupon\StoreCouponDao;
use app\services\BaseServices;
use app\services\order\StoreCartServices;
use app\services\product\product\StoreCategoryServices;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;

/**
 * Class StoreCouponService
 * @package app\services\coupon
 * @method save(array $data)
 */
class StoreCouponService extends BaseServices
{
    public function __construct(StoreCouponDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取列表
     * @param array $where
     * @return array
     */
    public function getList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $where['is_del'] = 0;
        $list = $this->dao->getList($where, $page, $limit);
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 添加优惠券表单
     * @param int $type
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function createForm(int $type)
    {
        $f[] = Form::input('title', '优惠券名称');
        switch ($type) {
            case 1://品类券
                $options = function () {
                    /** @var StoreCategoryServices $storeCategoryService */
                    $storeCategoryService = app()->make(StoreCategoryServices::class);
                    $list = $storeCategoryService->getTierList(1, 1);
                    $menus = [];
                    foreach (sort_list_tier($list) as $menu) {
                        $menus[] = ['value' => $menu['id'], 'label' => $menu['html'] . $menu['cate_name'], 'disabled' => false];
                    }

                    return $menus;
                };
                $f[] = Form::select('category_id', '选择品类')->setOptions(Form::setOptions($options))->filterable(1)->col(12);
                break;
            case 2://商品券
                $f[] = Form::frameImages('image', '商品', Url::buildUrl('admin/store.StoreProduct/index', array('fodder' => 'image', 'type' => 'many')))->icon('ios-add')->width('60%')->height('550px')->props(['srcKey' => 'image']);
                $f[] = Form::hidden('product_id', '');
                break;
        }
        $f[] = Form::number('coupon_price', '优惠券面值', 0)->min(0);
        $f[] = Form::number('use_min_price', '优惠券最低消费', 0)->min(0);
        $f[] = Form::number('coupon_time', '优惠券有效期限', 0)->min(0);
        $f[] = Form::number('sort', '排序')->value(0);
        $f[] = Form::radio('status', '状态', 1)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        $f[] = Form::hidden('type', $type);
        return create_form('添加优惠券', $f, Url::buildUrl('/marketing/coupon/save'), 'POST');
    }

    /**
     * 优惠卷模板修改表单
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function createIssue(int $id)
    {
        $res = $this->dao->getOne(['id' => $id, 'status' => 1, 'is_del' => 0]);
        if (!$res) throw new AdminException('发布的优惠劵已失效或不存在!');
        $f = [];
        $f[] = Form::input('id', '优惠劵ID', $id)->disabled(1);
        $f[] = Form::input('coupon_title', '优惠劵名称', $res['title'])->disabled(1);
        $f[] = Form::dateTimeRange('range_date', '领取时间')->placeholder('不填为永久有效');
        $f[] = Form::radio('is_permanent', '是否限量', 1)->options([['label' => '不限量', 'value' => 1], ['label' => '限量', 'value' => 0]]);
        $f[] = Form::number('count', '发布数量', 0)->min(0)->placeholder('不填或填0,为不限量');
        $f[] = Form::radio('is_type', '优惠券类型', 0)->options([['label' => '普通券', 'value' => 0], ['label' => '赠送券', 'value' => 1], ['label' => '新人券', 'value' => 2]]);
        $f[] = Form::number('full_reduction', '满赠金额', 0)->min(0)->placeholder('赠送优惠券的最低消费金额');
        $f[] = Form::radio('status', '状态', 1)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        return create_form('发布优惠券', $f, $this->url('/marketing/coupon/issue/' . $id), 'POST');
    }

    /**
     * 发布优惠券
     * @param int $id
     * @param int $_id
     * @param string $coupon_title
     * @param array $rangeTime
     * @param int $count
     * @param int $status
     * @param int $is_permanent
     * @param float $full_reduction
     * @param int $is_give_subscribe
     * @param int $is_full_give
     * @param int $is_type
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function upIssue(int $id, int $_id, string $coupon_title, array $rangeTime, int $count, int $status, int $is_permanent, float $full_reduction, int $is_give_subscribe, int $is_full_give, int $is_type)
    {
        if ($is_type == 1) {
            $is_full_give = 1;
        } elseif ($is_type == 2) {
            $is_give_subscribe = 1;
        }
        if ($_id != $id) throw new AdminException('操作失败,信息不对称');
        if (!$count) $count = 0;
        $couponInfo = $this->dao->getOne(['id' => $id, 'status' => 1, 'is_del' => 0]);
        if (!$couponInfo) throw new AdminException('发布的优惠劵已失效或不存在!');
        if (count($rangeTime) != 2) throw new AdminException('请选择正确的时间区间');
        list($startTime, $endTime) = $rangeTime;
        if (!$startTime) $startTime = 0;
        if (!$endTime) $endTime = 0;
        if (!$startTime && $endTime) throw new AdminException('请选择正确的开始时间');
        if ($startTime && !$endTime) throw new AdminException('请选择正确的结束时间');
        $data['cid'] = $id;
        $data['coupon_title'] = $coupon_title;
        $data['start_time'] = strtotime($startTime);
        $data['end_time'] = strtotime($endTime);
        $data['total_count'] = $count;
        $data['remain_count'] = $count;
        $data['is_permanent'] = $is_permanent;
        $data['status'] = $status;
        $data['is_give_subscribe'] = $is_give_subscribe;
        $data['is_full_give'] = $is_full_give;
        $data['full_reduction'] = $full_reduction;
        $data['is_del'] = 0;
        $data['add_time'] = time();
        $data['title'] = $couponInfo['title'];
        $data['integral'] = $couponInfo['integral'];
        $data['coupon_price'] = $couponInfo['coupon_price'];
        $data['use_min_price'] = $couponInfo['use_min_price'];
        $data['coupon_time'] = $couponInfo['coupon_time'];
        $data['product_id'] = $couponInfo['product_id'];
        $data['category_id'] = $couponInfo['category_id'];
        $data['type'] = $couponInfo->getData('type');
        /** @var StoreCouponIssueServices $storeCouponIssueService */
        $storeCouponIssueService = app()->make(StoreCouponIssueServices::class);
        $res = $storeCouponIssueService->save($data);
        $productIds = explode(',', $data['product_id']);
        if (count($productIds)) {
            $couponData = [];
            foreach ($productIds as $product_id) {
                $couponData[] = ['product_id' => $product_id, 'coupon_id' => $res->id];
            }
            /** @var StoreCouponProductServices $storeCouponProductService */
            $storeCouponProductService = app()->make(StoreCouponProductServices::class);
            $storeCouponProductService->saveAll($couponData);
        }
        if (!$res) throw new AdminException('发布优惠劵失败!');
    }

    /**
     * 优惠券失效
     * @param int $id
     */
    public function invalid(int $id)
    {
        $res = $this->dao->update($id, ['status' => 0]);
        if (!$res) throw new AdminException('操作失败');
        /** @var StoreCouponIssueServices $storeCouponIssueService */
        $storeCouponIssueService = app()->make(StoreCouponIssueServices::class);
        $storeCouponIssueService->update($id, ['status' => -1], 'cid');
    }

    /**
     * 获取下单可使用的优惠券列表
     * @param int $uid
     * @param $cartId
     * @param string $price
     * @param bool $new
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function beUsableCouponList(int $uid, $cartId, bool $new)
    {
        /** @var StoreCartServices $services */
        $services = app()->make(StoreCartServices::class);
        $cartGroup = $services->getUserProductCartListV1($uid, $cartId, $new, 1);
        /** @var StoreCouponUserServices $coupServices */
        $coupServices = app()->make(StoreCouponUserServices::class);
        return $coupServices->getUsableCouponList($uid, $cartGroup);
    }


}
