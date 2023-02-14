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

namespace app\api\controller\v2;


use app\Request;
use app\services\diy\DiyServices;
use app\services\product\product\StoreCategoryServices;
use app\services\product\product\StoreProductServices;
use app\services\user\UserServices;
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
        /** @var StoreCategoryServices $categoryService */
        $categoryService = app()->make(StoreCategoryServices::class);
        $info['fastList'] = $fastNumber ? $categoryService->byIndexList($fastNumber, 'id,cate_name,pid,pic') : [];//TODO 快速选择分类个数
        /** @var StoreProductServices $storeProductServices */
        $storeProductServices = app()->make(StoreProductServices::class);
        //获取推荐商品
        [$baseList, $firstList, $benefit, $likeInfo, $vipList] = $storeProductServices->getRecommendProductArr((int)$request->uid(), ['is_best', 'is_new', 'is_benefit', 'is_hot']);
        $info['bastList'] = $baseList;//TODO 精品推荐个数
        $info['firstList'] = $firstList;//TODO 首发新品个数
        if ($request->uid()) {
            /** @var UserServices $userService */
            $userService = app()->make(UserServices::class);
            //看是否会员过期
            $userService->offMemberLevel($request->uid());
            /** @var WechatUserServices $wechatUserService */
            $wechatUserService = app()->make(WechatUserServices::class);
            $subscribe = (bool)$wechatUserService->value(['uid' => $request->uid(), 'user_type' => 'wechat'], 'subscribe');
        } else {
            $subscribe = true;
        }
        $tengxun_map_key = sys_config('tengxun_map_key');
        $site_name = sys_config('site_name');
        return app('json')->success(compact('info', 'benefit', 'likeInfo', 'subscribe', 'tengxun_map_key', 'site_name'));
    }

    /**
     * 获取页面数据
     * @param Request $request
     * @param string $name
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getDiy(Request $request, $name = '')
    {
        list($id) = $request->getMore([
            ['id', 0]
        ], true);
        /** @var DiyServices $diyService */
        $diyService = app()->make(DiyServices::class);
        $data = $diyService->getDiy($id);
        return app('json')->success($data);
    }

    /**
     * @param int $id
     * @return mixed
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/2/8
     */
    public function getVersion($id = 0)
    {
        /** @var DiyServices $diyService */
        $diyService = app()->make(DiyServices::class);
        $version = $diyService->getDiyVersion((int)$id);
        return app('json')->success(['version' => $version ?: '']);
    }

    /**
     * 是否强制绑定手机号
     * @return mixed
     */
    public function bindPhoneStatus()
    {
        $status = (bool)sys_config('store_user_mobile');
        return app('json')->success(compact('status'));
    }

    /**
     * 是否关注
     * @param Request $request
     * @param WechatServices $services
     * @return mixed
     */
    public function subscribe(Request $request, WechatUserServices $services)
    {
        return app('json')->success(['subscribe' => (bool)$services->value(['uid' => $request->uid(), 'user_type' => 'wechat'], 'subscribe')]);
    }

    /**
     * 获取提货点自提开启状态
     * @return mixed
     */
    public function getStoreStatus()
    {
        $data['store_status'] = sys_config('store_self_mention', 0);
        return app('json')->success($data);
    }

    /**
     * 获取颜色选择和分类模板选择
     * @param DiyServices $services
     * @param $name
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function colorChange(DiyServices $services, $name)
    {
        $status = (int)$services->getColorChange((string)$name);
        $is_diy = $services->value(['status' => 1, 'is_del' => 0], 'is_diy');
        return app('json')->success(compact('status', 'is_diy'));
    }
}
