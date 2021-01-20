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
namespace app\api\controller\v1\user;

use app\Request;
use app\services\user\UserServices;
use crmeb\services\CacheService;


/**
 * 用户类
 * Class UserController
 * @package app\api\controller\store
 */
class UserController
{
    protected $services = NUll;

    /**
     * UserController constructor.
     * @param UserServices $services
     */
    public function __construct(UserServices $services)
    {
        $this->services = $services;
    }

    /**
     * 获取用户信息
     * @param Request $request
     * @return mixed
     */
    public function userInfo(Request $request)
    {
        $info = $request->user()->toArray();
        return app('json')->success($this->services->userInfo($info));
    }

    /**
     * 用户资金统计
     * @param Request $request
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function balance(Request $request)
    {
        $uid = (int)$request->uid();
        return app('json')->successful($this->services->balance($uid));
    }

    /**
     * 个人中心
     * @param Request $request
     * @return mixed
     */
    public function user(Request $request)
    {
        $user = $request->user()->toArray();
        return app('json')->success($this->services->personalHome($user, $request->tokenData()));
    }

    /**
     * 添加点赞
     *
     * @param Request $request
     * @return mixed
     */
//    public function like_add(Request $request)
//    {
//        list($id, $category) = UtilService::postMore([['id',0], ['category','product']], $request, true);
//        if(!$id || !is_numeric($id))  return app('json')->fail('参数错误');
//        $res = StoreProductRelation::productRelation($id,$request->uid(),'like',$category);
//        if(!$res) return  app('json')->fail(StoreProductRelation::getErrorInfo());
//        else return app('json')->successful();
//    }

    /**
     * 取消点赞
     *
     * @param Request $request
     * @return mixed
     */
//    public function like_del(Request $request)
//    {
//        list($id, $category) = UtilService::postMore([['id',0], ['category','product']], $request, true);
//        if(!$id || !is_numeric($id)) return app('json')->fail('参数错误');
//        $res = StoreProductRelation::unProductRelation($id, $request->uid(),'like',$category);
//        if(!$res) return app('json')->fail(StoreProductRelation::getErrorInfo());
//        else return app('json')->successful();
//    }


    /**
     * 用户修改信息
     * @param Request $request
     * @return mixed
     */
    public function edit(Request $request)
    {
        list($avatar, $nickname) = $request->postMore([
            ['avatar', ''],
            ['nickname', ''],
        ], true);
        if (!$avatar && $nickname == '') {
            return app('json')->fail('请输入昵称或者选择头像');
        }
        $uid = (int)$request->uid();
        if ($this->services->eidtNickname($uid, ['avatar' => $avatar, 'nickname' => $nickname])) {
            return app('json')->successful('修改成功');
        }
        return app('json')->fail('修改失败');
    }

    /**
     * 推广人排行
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function rank(Request $request)
    {
        $data = $request->getMore([
            ['page', ''],
            ['limit', ''],
            ['type', '']
        ]);
        return app('json')->success($this->services->getRankList($data));
    }

    /**
     * 添加访问记录
     * @param Request $request
     * @return mixed
     */
    public function set_visit(Request $request)
    {
        $data = $request->postMore([
            ['url', ''],
            ['stay_time', 0]
        ]);
        if ($data['url'] == '') return app('json')->fail('未获取页面路径');
        $data['uid'] = (int)$request->uid();
        $data['ip'] = $request->ip();
        if ($this->services->setVisit($data)) {
            return app('json')->success('添加访问记录成功');
        } else {
            return app('json')->fail('添加访问记录失败');
        }
    }

    /**
     * 静默绑定推广人
     * @param Request $request
     * @return mixed
     */
    public function spread(Request $request)
    {
        [$spreadUid, $code] = $request->postMore([
            ['puid', 0],
            ['code', 0]
        ], true);
        $uid = (int)$request->uid();
        $this->services->spread($uid, (int)$spreadUid, $code);
        return app('json')->success();
    }

    /**
     * 推荐用户
     * @param Request $request
     * @return mixed
     *
     * grade == 0  获取一级推荐人
     * grade == 1  获取二级推荐人
     *
     * keyword 会员名称查询
     *
     * sort  childCount ASC/DESC  团队排序   numberCount ASC/DESC  金额排序  orderCount  ASC/DESC  订单排序
     */
    public function spread_people(Request $request)
    {
        $spreadInfo = $request->postMore([
            ['grade', 0],
            ['keyword', ''],
            ['sort', ''],
        ]);
        if (!in_array($spreadInfo['grade'], [0, 1])) {
            return app('json')->fail('等级错误');
        }
        $uid = $request->uid();
        return app('json')->successful($this->services->getUserSpreadGrade($uid, $spreadInfo['grade'], $spreadInfo['sort'], $spreadInfo['keyword']));
    }

}
