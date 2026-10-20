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
        $where = $request->getMore([
            ['keyword', '', '', 'store_name'],
            ['priceOrder', ''],
            ['salesOrder', ''],
            ['cate_id', ''],
            ['limit', 30],
            ['ids', ''],
            ['store_label_id', ''],
        ]);
        [$page, $limit] = $services->getPageValue();
        if (!empty($where['limit'])) {
            $limit = (int)$where['limit'];
        }
        // DIY 商品列表/商品选项卡「指定商品」「商品标签」：ids/store_label_id 逗号串转数组（DAO 按数组 whereIn/FIND_IN_SET 处理，ids 需按顺序排序）
        foreach (['ids', 'store_label_id'] as $idKey) {
            $idValue = is_array($where[$idKey]) ? $where[$idKey] : explode(',', (string)$where[$idKey]);
            $where[$idKey] = array_values(array_filter(array_map('intval', $idValue)));
            if (empty($where[$idKey])) {
                unset($where[$idKey]);
            }
        }
        if (!empty($where['ids'])) {
            // 指定商品按已选数量返回，不受默认分页条数限制
            $limit = max($limit, count($where['ids']));
        }
        if ($where['cate_id'] !== '') {
            $where['cate_id'] = explode(',', $where['cate_id']);
            foreach ($where['cate_id'] as $keys => &$items) {
                $where['cate_id'][$keys] = (int)$items;
            }
        } else {
            $where['cate_id'] = [];
        }
        $list = $services->getUserList($where, $page, $limit);

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
