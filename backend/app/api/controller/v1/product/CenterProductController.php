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

namespace app\api\controller\v1\product;

use app\Request;
use app\services\product\CenterProductServices;

/**
 * 中台商品用户端控制器
 * 用户端：统一商品列表/搜索/类型筛选/商品详情
 * 详情接口返回双层声明式结构 {page_config, data}，组装逻辑下沉至 CenterProductServices::getDetail()
 */
class CenterProductController
{
    /**
     * 统一商品列表（从产品中台 eb_product_center 查询）
     * @param Request $request
     * @param CenterProductServices $services
     * @return mixed
     */
    public function productList(Request $request, CenterProductServices $services)
    {
        /** @var \app\services\product\product\StoreCategoryServices $categoryServices */
        $categoryServices = app()->make(\app\services\product\product\StoreCategoryServices::class);
        $where = $request->getMore([
            [['sid', 'd'], 0],
            [['cid', 'd'], 0],
            ['keyword', '', '', 'store_name'],
            ['priceOrder', ''],
            ['salesOrder', ''],
            [['news', 'd'], 0],
            [['selectId', 'd'], 0],
            ['productId', ''],
            ['coupon_category_id', ''],
            ['cate_id', ''],
            ['limit', 30],
            ['ids', ''],
            ['store_label_id', ''],
            // 旧版移动端列表页（goods_list/goods_cate/首页/微页专题）仍传 cid/sid/productId/coupon_category_id，
            // DAO getSearchList 按旧语义处理：cid 含子分类下钻、sid 精确匹配、coupon_category_id 优惠券分类
            [['cid', 'd'], 0],
            [['sid', 'd'], 0],
            ['coupon_category_id', ''],
            ['productId', ''],
        ]);
        // 旧参数 productId 与 ids 同义（指定商品跳转），统一并入 ids 处理
        if (!empty($where['productId'])) {
            $where['ids'] = $where['productId'];
        }
        unset($where['productId']);
        [$page, $limit] = $services->getPageValue();
        // limit 强制整型并设上限，避免异常大分页
        $limit = max(1, min(100, (int)($where['limit'] ?: $limit)));
        unset($where['limit']);
        // 排序字段白名单：只允许 asc/desc，其余清空（防 ORDER BY 注入）
        foreach (['priceOrder', 'salesOrder'] as $orderKey) {
            $orderVal = strtolower(trim((string)($where[$orderKey] ?? '')));
            $where[$orderKey] = in_array($orderVal, ['asc', 'desc'], true) ? $orderVal : '';
            if ($where[$orderKey] === '') {
                unset($where[$orderKey]);
            }
        }
        // 分类页 selectId：有父级视为二级，否则一级
        if ($where['selectId'] && (!$where['sid'] || !$where['cid'])) {
            if ($categoryServices->value(['id' => $where['selectId']], 'pid')) {
                $where['sid'] = $where['selectId'];
            } else {
                $where['cid'] = $where['selectId'];
            }
        }
        unset($where['selectId']);
        // 品类/商品券落地页：productId → ids
        if (!empty($where['productId'])) {
            $where['ids'] = $where['productId'];
        }
        unset($where['productId']);
        // 新品排序（前端 news=1）
        if (!empty($where['news'])) {
            $where['newsOrder'] = 'news';
        }
        unset($where['news']);
        // DIY「指定商品」「商品标签」：逗号串转整型数组
        foreach (['ids', 'store_label_id'] as $idKey) {
            $where[$idKey] = toIntArray($where[$idKey] ?? '');
            if (empty($where[$idKey])) {
                unset($where[$idKey]);
            }
        }
        if (!empty($where['ids'])) {
            // 指定商品按已选数量返回，不受默认分页条数限制
            $limit = max($limit, count($where['ids']));
        }
        // 分类 / 品类券：统一整型化
        $where['cate_id'] = toIntArray($where['cate_id'] ?? '');
        $where['coupon_category_id'] = toIntArray($where['coupon_category_id'] ?? '');
        if (empty($where['cate_id'])) {
            unset($where['cate_id']);
        }
        if (empty($where['coupon_category_id'])) {
            unset($where['coupon_category_id']);
        }
        if (empty($where['sid'])) {
            unset($where['sid']);
        }
        if (empty($where['cid'])) {
            unset($where['cid']);
        }
        // 关键词截断，降低超长 LIKE 压力
        if (isset($where['store_name']) && is_string($where['store_name'])) {
            $where['store_name'] = mb_substr(trim($where['store_name']), 0, 50);
            if ($where['store_name'] === '') {
                unset($where['store_name']);
            }
        }
        $list = $services->getUserList($where, $page, $limit, (int)$request->uid());

        // 为每个商品附加 type_info（通过 Handler 动态获取）
        $registry = app()->make(\crmeb\trade\product\ProductTypeRegistry::class);
        foreach ($list as &$item) {
            $type = $item['product_type'] ?? 'normal';
            $handler = $registry->get($type);
            $item['type_info'] = $handler->getListExtend((int)($item['id'] ?? 0));
        }
        unset($item);

        return app('json')->success(get_thumb_water($list, 'big', ['image', 'recommend_image']));
    }

    /**
     * 商品详情（双层声明式结构 {page_config, data}）
     * 控制器仅做参数校验与调用，组装逻辑全部下沉至 CenterProductServices::getDetail()
     * 扩展字段（params_list/protection_list/label_list/custom_form 等）通过 ProductDetailExtend 事件注入
     * @param Request $request
     * @param int $id 中台商品ID
     * @param CenterProductServices $services
     * @return mixed
     */
    public function detail(Request $request, $id, CenterProductServices $services)
    {
        $uid = (int)$request->uid();
        $id = (int)$id;
        // 活动上下文参数：activity_type 非 normal 时，activity_id 为对应活动商品ID
        $activityType = (string)$request->param('activity_type', 'normal');
        $activityId = (int)$request->param('activity_id', 0);
        try {
            $result = $services->getDetail($id, $uid, $activityType, $activityId);
        } catch (\RuntimeException $e) {
            return app('json')->fail($e->getMessage());
        }
        return app('json')->success($result);
    }

    /**
     * 商品实时价格（获取会员价/优惠券折扣后的到手价）
     * SKU 价格查询、会员等级折扣、VIP 会员价、优惠券抵扣等完整逻辑
     * 全部下沉至 CenterProductServices::realPrice()，控制器仅做参数透传
     * @param Request $request
     * @param int $id
     * @param CenterProductServices $services
     * @return mixed
     */
    public function realPrice(Request $request, $id, CenterProductServices $services)
    {
        $uid = (int)$request->uid();
        $unique = (string)request()->param('unique', '');
        // 复用 Service 层完整价格计算逻辑（会员价/优惠券/VIP），避免控制器重复实现导致字段缺失
        $result = $services->realPrice($uid, (int)$id, $unique);
        return app('json')->success($result);
    }

    /**
     * 统一商品列表（管理后台选商品用）
     */
    public function list(CenterProductServices $services)
    {
        $where = [
            'product_type' => request()->param('product_type', ''),
            'keyword'      => request()->param('keyword', ''),
            'cate_id'      => request()->param('cate_id', 0),
        ];
        $page = (int) request()->param('page', 1);
        $limit = (int) request()->param('limit', 10);
        return app('json')->success($services->getList($where, $page, $limit));
    }

    /**
     * 商品搜索
     */
    public function search(CenterProductServices $services)
    {
        $keyword = request()->param('keyword', '');
        $page = (int) request()->param('page', 1);
        $limit = (int) request()->param('limit', 10);
        return app('json')->success($services->search($keyword, $page, $limit));
    }

    /**
     * 获取产品类型列表
     */
    public function typeList(CenterProductServices $services)
    {
        return app('json')->success($services->getTypeList());
    }
}
