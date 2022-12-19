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

use app\services\activity\bargain\StoreBargainServices;
use app\services\activity\bargain\StoreBargainUserHelpServices;
use app\services\activity\bargain\StoreBargainUserServices;
use app\Request;
use app\services\user\UserServices;

/**
 * 砍价商品类
 * Class StoreBargainController
 * @package app\api\controller\activity
 */
class StoreBargainController
{
    protected $services;

    public function __construct(StoreBargainServices $services)
    {
        $this->services = $services;
    }

    /**
     * 砍价列表顶部图
     * @return mixed
     */
    public function config()
    {
        $lovely = sys_data('routine_lovely') ?? [];//banner图
        $info = $lovely[2] ?? [];
        return app('json')->success($info);
    }

    /**
     * 砍价商品列表
     * @param Request $request
     * @return mixed
     */
    public function lst(Request $request)
    {
        $bargainList = $this->services->getBargainList();
        return app('json')->success(get_thumb_water($bargainList));
    }

    /**
     * 砍价详情和当前登录人信息
     * @param Request $request
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function detail(Request $request, $id)
    {
        list($bargainUid) = $request->getMore([
            ['bargainUid', 0]
        ], true);
        $data = $this->services->getBargain($request, $id, (int)$bargainUid);
        return app('json')->success($data);
    }

    /**
     * 砍价 观看/分享/参与次数
     * @param Request $request
     * @return mixed
     */
    public function share(Request $request)
    {
        /** @var StoreBargainUserHelpServices $bargainUserHelpService */
        $bargainUserHelpService = app()->make(StoreBargainUserHelpServices::class);
        list($bargainId) = $request->postMore([['bargainId', 0]], true);
        $data['lookCount'] = $this->services->sum([], 'look');//TODO 观看人数
        $data['userCount'] = $bargainUserHelpService->count([]);//TODO 参与人数
        if (!$bargainId) return app('json')->success($data);
        $this->services->addBargain($bargainId, 'share');
        $data['shareCount'] = $this->services->sum([], 'share');//TODO 分享人数
        return app('json')->success($data);
    }

    /**
     * 砍价开启
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function start(Request $request)
    {
        list($bargainId) = $request->postMore([
            ['bargainId', 0]
        ], true);
        return app('json')->success($this->services->setBargain($request->uid(), $bargainId));
    }

    /**
     * 砍价 帮助好友砍价
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function help(Request $request)
    {
        list($bargainId, $bargainUserUid) = $request->postMore([
            ['bargainId', 0],
            ['bargainUserUid', 0]
        ], true);
        return app('json')->success($this->services->setHelpBargain($request->uid(), $bargainId, $bargainUserUid));
    }

    /**
     * 砍价 砍价帮
     * @param Request $request
     * @return mixed
     */
    public function help_list(Request $request)
    {
        list($bargainId, $bargainUserUid) = $request->postMore([
            ['bargainId', 0],
            ['bargainUserUid', 0],
        ], true);
        /** @var StoreBargainUserServices $bargainUser */
        $bargainUser = app()->make(StoreBargainUserServices::class);
        $bargainUserTableId = $bargainUser->getBargainUserTableId($bargainId, $bargainUserUid);

        /** @var StoreBargainUserHelpServices $bargainUserHelp */
        $bargainUserHelp = app()->make(StoreBargainUserHelpServices::class);
        [$page, $limit] = $this->services->getPageValue();
        $storeBargainUserHelp = $bargainUserHelp->getHelpList($bargainUserTableId, $page, $limit);
        return app('json')->success($storeBargainUserHelp);
    }

    /**
     * 砍价 开启砍价用户信息
     * @param Request $request
     * @return mixed
     */
    public function start_user(Request $request)
    {
        list($bargainId, $bargainUserUid) = $request->postMore([
            ['bargainId', 0],
            ['bargainUserUid', 0],
        ], true);
        if (!$bargainId || !$bargainUserUid) return app('json')->fail(100100);
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->getUserInfo((int)$bargainUserUid);
        if (!$userInfo) {
            return app('json')->fail(410044);
        }
        return app('json')->success(['nickname' => $userInfo['nickname'], 'avatar' => $userInfo['avatar']]);
    }

    /**
     * 砍价列表(已参与)
     * @param Request $request
     * @return mixed
     */
    public function user_list(Request $request)
    {
        $uid = $request->uid();
        /** @var StoreBargainUserServices $bargainUser */
        $bargainUser = app()->make(StoreBargainUserServices::class);
        $bargainUser->editBargainUserStatus($uid);// TODO 判断过期砍价活动
        $list = $bargainUser->getBargainUserAll($uid);
        if (count($list)) return app('json')->success(get_thumb_water($list));
        else return app('json')->success([]);
    }

    /**
     * 砍价取消
     * @param Request $request
     * @return mixed
     */
    public function user_cancel(Request $request)
    {
        list($bargainId) = $request->postMore([['bargainId', 0]], true);
        if (!$bargainId) return app('json')->fail(100100);
        /** @var StoreBargainUserServices $bargainUser */
        $bargainUser = app()->make(StoreBargainUserServices::class);
        $res = $bargainUser->cancelBargain($bargainId, $request->uid());
        if ($res) return app('json')->success(100019);
        else return app('json')->success(100020);
    }

    /**
     * 砍价海报
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function poster(Request $request)
    {
        [$bargainId, $from] = $request->postMore([
            ['bargainId', ''],
            ['from', 'wechat']
        ], true);
        $posterUrl = $this->services->poster($bargainId, $request->user(), $from);
        if ($posterUrl) {
            return app('json')->success(['url' => $posterUrl]);
        } else {
            return app('json')->fail(410172);
        }
    }

    /**
     * 获取分享海报信息
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function posterInfo(Request $request, $bargainId)
    {
        return app('json')->success($this->services->posterInfo((int)$bargainId, $request->user()));
    }
}
