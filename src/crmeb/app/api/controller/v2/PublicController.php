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

namespace app\api\controller\v2;


use app\Request;
use app\services\diy\DiyServices;
use app\services\product\product\StoreCategoryServices;
use app\services\product\product\StoreProductServices;
use app\services\wechat\WechatUserServices;

class PublicController
{
    /**
     * 主页获取
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $fastNumber = (int)sys_config('fast_number', 0);//TODO 快速选择分类个数
        $bastNumber = (int)sys_config('bast_number', 0);//TODO 精品推荐个数
        $firstNumber = (int)sys_config('first_number', 0);//TODO 首发新品个数
        $promotionNumber = (int)sys_config('promotion_number', 0);//TODO 首发新品个数
        /** @var StoreCategoryServices $categoryService */
        $categoryService = app()->make(StoreCategoryServices::class);
        $info['fastList'] = $fastNumber ? $categoryService->byIndexList($fastNumber, 'id,cate_name,pid,pic') : [];//TODO 快速选择分类个数
        /** @var StoreProductServices $storeProductServices */
        $storeProductServices = app()->make(StoreProductServices::class);
        $info['bastList'] = $bastNumber ? $storeProductServices->getRecommendProduct($request->uid(), 'is_best', $bastNumber) : [];//TODO 精品推荐个数
        $info['firstList'] = $firstNumber ? $storeProductServices->getRecommendProduct($request->uid(), 'is_new', $firstNumber) : [];//TODO 首发新品个数
        $benefit = $promotionNumber ? $storeProductServices->getRecommendProduct($request->uid(), 'is_benefit', $promotionNumber) : [];//TODO 首页促销单品
        $likeInfo = $storeProductServices->getRecommendProduct($request->uid(), 'is_hot', 3);//TODO 热门榜单 猜你喜欢
        if ($request->uid()) {
            /** @var WechatUserServices $wechatUserService */
            $wechatUserService = app()->make(WechatUserServices::class);
            $subscribe = $wechatUserService->value(['uid' => $request->uid()], 'subscribe') ? true : false;
        } else {
            $subscribe = true;
        }
        $tengxun_map_key = sys_config('tengxun_map_key');
        $site_name = sys_config('site_name');
        return app('json')->successful(compact('info', 'benefit', 'likeInfo', 'subscribe', 'tengxun_map_key', 'site_name'));
    }

    /**
     * 获取页面数据
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getDiy($name = '')
    {
        /** @var DiyServices $diyService */
        $diyService = app()->make(DiyServices::class);
        $data = $diyService->getDiy($name);
        return app('json')->successful($data);
    }

    /**
     * 是否强制绑定手机号
     * @return mixed
     */
    public function bindPhoneStatus()
    {
        $status = sys_config('store_user_mobile') ? true : false;
        return app('json')->success(compact('status'));
    }

    /**
     * 是否关注
     * @param Request $request
     * @param WechatServices $services
     * @return mixed
     */
    public function subscribe(Request $request, WechatServices $services)
    {
        return app('json')->success(['subscribe' => $services->isSubscribe((int)$request->uid())]);
    }

}
