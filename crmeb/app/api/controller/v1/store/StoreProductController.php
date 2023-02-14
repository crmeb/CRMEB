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
namespace app\api\controller\v1\store;

use app\Request;
use app\services\product\product\StoreCategoryServices;
use app\services\product\product\StoreProductReplyServices;
use app\services\product\product\StoreProductServices;
use app\services\user\UserServices;

/**
 * 商品类
 * Class StoreProductController
 * @package app\api\controller\store
 */
class StoreProductController
{
    /**
     * 商品services
     * @var StoreProductServices
     */
    protected $services;

    public function __construct(StoreProductServices $services)
    {
        $this->services = $services;
    }

    /**
     * 商品列表
     * @param Request $request
     * @param StoreCategoryServices $services
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function lst(Request $request, StoreCategoryServices $services)
    {
        $where = $request->getMore([
            [['sid', 'd'], 0],
            [['cid', 'd'], 0],
            ['keyword', '', '', 'store_name'],
            ['priceOrder', ''],
            ['salesOrder', ''],
            [['news', 'd'], 0, '', 'is_new'],
            [['type', 0], 0],
            ['ids', ''],
            ['selectId', ''],
            ['productId', ''],
            ['coupon_category_id', '']
        ]);
        if ($where['selectId'] && (!$where['sid'] || !$where['cid'])) {
            if ($services->value(['id' => $where['selectId']], 'pid')) {
                $where['sid'] = $where['selectId'];
            } else {
                $where['cid'] = $where['selectId'];
            }
        }
        if ($where['ids'] && is_string($where['ids'])) {
            $where['ids'] = explode(',', $where['ids']);
            foreach ($where['ids'] as $key => &$item) {
                $where['ids'][$key] = (int)$item;
                if ($where['ids'][$key] == 0) unset($where['ids'][$key]);
            }
        }
        if (!$where['ids']) {
            unset($where['ids']);
        }
        $type = 'big';
        $field = ['image', 'recommend_image'];
        $list = $this->services->getGoodsList($where, (int)$request->uid());
        return app('json')->success(get_thumb_water($list, $type, $field));
    }

    /**
     * 商品分享二维码 推广员
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function code(Request $request, $id)
    {
        $code = $this->services->getCode((int)$id, $request->get('user_type', 'wechat'), $request->user());
        return app('json')->success(['code' => $code]);
    }

    /**
     * 商品详情
     * @param Request $request
     * @param $id
     * @param int $type
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function detail(Request $request, $id, $type = 0)
    {
        $data = $this->services->productDetail($request, (int)$id, (int)$type);
        return app('json')->success($data);
    }

    /**
     * 为你推荐
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function product_hot(Request $request)
    {
        $vip_user = $request->uid() ? app()->make(UserServices::class)->value(['uid' => $request->uid()], 'is_money_level') : 0;
        $list = $this->services->getProducts(['is_hot' => 1, 'is_show' => 1, 'is_del' => 0, 'vip_user' => $vip_user]);
        return app('json')->success(get_thumb_water($list, 'mid'));
    }

    /**
     * 获取首页推荐不同类型商品的轮播图和商品
     * @param Request $request
     * @param $type
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function groom_list(Request $request, $type)
    {
        $info['banner'] = [];
        $info['list'] = [];
        if ($type == 1) {//TODO 精品推荐
            $info['banner'] = sys_data('routine_home_bast_banner') ?: [];//TODO 首页精品推荐图片
            $info['list'] = $this->services->getRecommendProduct($request->uid(), 'is_best');//TODO 精品推荐个数
        } else if ($type == 2) {//TODO  热门榜单
            $info['banner'] = sys_data('routine_home_hot_banner') ?: [];//TODO 热门榜单 猜你喜欢推荐图片
            $info['list'] = $this->services->getRecommendProduct($request->uid(), 'is_hot');//TODO 热门榜单 猜你喜欢
        } else if ($type == 3) {//TODO 首发新品
            $info['banner'] = sys_data('routine_home_new_banner') ?: [];//TODO 首发新品推荐图片
            $info['list'] = $this->services->getRecommendProduct($request->uid(), 'is_new');//TODO 首发新品
        } else if ($type == 4) {//TODO 促销单品
            $info['banner'] = sys_data('routine_home_benefit_banner') ?: [];//TODO 促销单品推荐图片
            $info['list'] = $this->services->getRecommendProduct($request->uid(), 'is_benefit');//TODO 促销单品
        } else if ($type == 5) {//TODO 会员商品
            $info['list'] = $this->services->getRecommendProduct($request->uid(), 'is_vip');//TODO 会员商品
        }
        return app('json')->success($info);
    }

    /**
     * 商品评价数量和好评度
     * @param $id
     * @return mixed
     */
    public function reply_config($id)
    {
        /** @var StoreProductReplyServices $replyService */
        $replyService = app()->make(StoreProductReplyServices::class);
        $count = $replyService->productReplyCount($id);
        return app('json')->success($count);
    }

    /**
     * 获取商品评论
     * @param Request $request
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function reply_list(Request $request, $id)
    {
        [$type] = $request->getMore([
            [['type', 'd'], 0]
        ], true);
        /** @var StoreProductReplyServices $replyService */
        $replyService = app()->make(StoreProductReplyServices::class);
        $list = $replyService->getProductReplyList($id, $type);
        return app('json')->success(get_thumb_water($list, 'big', ['pics']));
    }

    /**
     * 获取预售列表
     * @param Request $request
     * @return mixed
     */
    public function advanceList(Request $request)
    {
        $where = $request->getMore([
            [['time_type', 'd'], 0]
        ]);
        return app('json')->success($this->services->getAdvanceList($where));
    }

}
