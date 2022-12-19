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
namespace app\api\controller\v1\activity;

use app\Request;
use app\services\activity\combination\StoreCombinationServices;
use app\services\activity\combination\StorePinkServices;
use app\services\other\QrcodeServices;

/**
 * 拼团类
 * Class StoreCombinationController
 * @package app\api\controller\activity
 */
class StoreCombinationController
{
    protected $services;

    public function __construct(StoreCombinationServices $services)
    {
        $this->services = $services;
    }

    /**
     * 拼团列表
     * @return mixed
     */
    public function lst()
    {
        $list = $this->services->getCombinationList();
        return app('json')->success(get_thumb_water($list));
    }


    /**
     * 拼团商品详情
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function detail(Request $request, $id)
    {
        $data = $this->services->combinationDetail($request, $id);
        return app('json')->success($data);
    }

    /**
     * 拼团 开团
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function pink(Request $request, $id)
    {
        $data = $this->services->getPinkInfo($request, (int)$id);
        return app('json')->success($data);
    }

    /**
     * 拼团 取消开团
     * @param Request $request
     * @return mixed
     */
    public function remove(Request $request)
    {
        list($id, $cid) = $request->postMore([
            ['id', 0],
            ['cid', 0],
        ], true);
        if (!$id || !$cid) return app('json')->fail(100100);
        /** @var StorePinkServices $pinkService */
        $pinkService = app()->make(StorePinkServices::class);
        $pinkService->removePink($request->uid(), $cid, $id);
        return app('json')->success(100010);
    }


    /**
     * 拼团海报
     * @param Request $request
     * @return mixed
     */
    public function poster(Request $request)
    {
        list($pinkId, $from) = $request->postMore([
            ['id', 0],
            ['from', 'wechat']
        ], true);
        if (!$pinkId) return app('json')->fail(100100);
        $user = $request->user();
        /** @var StorePinkServices $pinkService */
        $pinkService = app()->make(StorePinkServices::class);
        $res = $pinkService->getPinkPoster($pinkId, $from, $user);
        return app('json')->success(['url' => $res]);
    }

    /**
     * 获取拼团海报详情
     * @param Request $request
     * @param StorePinkServices $services
     * @param $id
     * @return mixed
     */
    public function posterInfo(Request $request, StorePinkServices $services, $id)
    {
        return app('json')->success($services->posterInfo((int)$id, $request->user()));
    }

    /**
     * 获取秒杀小程序二维码
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function code(Request $request, $id)
    {
        /** @var QrcodeServices $qrcodeService */
        $qrcodeService = app()->make(QrcodeServices::class);
        $url = $qrcodeService->getRoutineQrcodePath($id, $request->uid(), 1);
        if ($url) {
            return app('json')->success(['code' => $url]);
        } else {
            return app('json')->success(['code' => '']);
        }
    }

    /**
     * 获取拼团列表轮播图
     */
    public function banner_list()
    {
        $banner = sys_data('combination_banner') ?? [];
        return app('json')->success($banner);
    }
}
