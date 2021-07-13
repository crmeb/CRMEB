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
namespace app\api\controller\v1\store;

use app\Request;
use app\services\other\QrcodeServices;
use app\services\product\product\StoreCategoryServices;
use app\services\product\product\StoreProductReplyServices;
use app\services\product\product\StoreProductServices;
use app\services\system\attachment\SystemAttachmentServices;

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
     * @return mixed
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
            ['selectId', '']
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
        }
        if (!$where['ids']) {
            unset($where['ids']);
        }
        return app('json')->successful($this->services->getGoodsList($where, (int)$request->uid()));
    }

    /**
     * 商品分享二维码 推广员
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function code(Request $request, $id)
    {
        $id = (int)$id;
        /** @var SystemAttachmentServices $attach */
        $attach = app()->make(SystemAttachmentServices::class);
        if (!$id || !$this->services->isValidProduct($id)) {
            return app('json')->fail('商品不存在或已下架');
        }
        $userType = $request->get('user_type', 'wechat');
        $user = $request->user();
        try {
            switch ($userType) {
                case 'wechat':
                    //公众号
                    $name = $id . '_product_detail_' . $user['uid'] . '_is_promoter_' . $user['is_promoter'] . '_wap.jpg';
                    /** @var QrcodeServices $qrcodeService */
                    $qrcodeService = app()->make(QrcodeServices::class);
                    $url = $qrcodeService->getWechatQrcodePath($name, '/pages/goods_details/index?id=' . $id . '&spread=' . $user['uid']);
                    if ($url === false)
                        return app('json')->fail('二维码生成失败');
                    else
                        return app('json')->successful(['code' => image_to_base64($url)]);
                    break;
                case 'routine':
                    /** @var QrcodeServices $qrcodeService */
                    $qrcodeService = app()->make(QrcodeServices::class);
                    $url = $qrcodeService->getRoutineQrcodePath($id, $user['uid'], 0, ['is_promoter' => $user['is_promoter']]);
                    if ($url === false)
                        return app('json')->fail('二维码生成失败');
                    else
                        return app('json')->successful(['code' => $url]);
            }
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage(), [
                'code' => $e->getCode(),
                'line' => $e->getLine(),
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * 商品详情
     * @param Request $request
     * @param $id
     * @param int $type
     * @return mixed
     */
    public function detail(Request $request, $id, $type = 0)
    {
        $data = $this->services->productDetail($request, (int)$id, (int)$type);
        return app('json')->successful($data);
    }

    /**
     * 为你推荐
     * @return mixed
     */
    public function product_hot()
    {
        $list = $this->services->getProducts(['is_hot' => 1, 'is_show' => 1, 'is_del' => 0]);
        return app('json')->success($list);
    }

    /**
     * 获取首页推荐不同类型商品的轮播图和商品
     * @param Request $request
     * @param $type
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
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
            $whereVip = [
                ['vip_price', '>', 0],
                ['is_vip', '=', 1],
            ];
            $info['list'] = $this->services->getRecommendProduct($request->uid(), $whereVip);//TODO 会员商品
        }
        return app('json')->successful($info);
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
        return app('json')->successful($count);
    }

    /**
     * 获取商品评论
     * @param Request $request
     * @param $id
     * @param $type
     * @return mixed
     */
    public function reply_list(Request $request, $id)
    {
        [$type] = $request->getMore([
            [['type', 'd'], 0]
        ], true);
        /** @var StoreProductReplyServices $replyService */
        $replyService = app()->make(StoreProductReplyServices::class);
        $list = $replyService->getProductReplyList($id, $type);
        return app('json')->successful($list);
    }

}
