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

namespace app\kefuapi\controller;


use app\Request;
use app\services\system\attachment\SystemAttachmentServices;
use app\services\user\UserGroupServices;
use crmeb\services\CacheService;
use app\services\other\UploadService;
use think\facade\App;
use app\services\kefu\UserServices;
use app\services\user\UserLabelCateServices;
use app\services\user\UserLabelRelationServices;
use app\services\kefu\service\StoreServiceRecordServices;
use think\facade\Config;
use think\facade\Cache;

/**
 * Class User
 * @package app\kefuapi\controller
 */
class User extends AuthController
{
    /**
     * User constructor.
     * @param App $app
     * @param StoreServiceRecordServices $services
     */
    public function __construct(App $app, StoreServiceRecordServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取当前客服和用户的聊天记录
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function recordList(string $nickname = '', $is_tourist = 0)
    {
        return app('json')->success($this->services->getServiceList($this->kefuInfo['uid'], $nickname, (int)$is_tourist));
    }

    /**
     * 获取用户信息
     * @param UserServices $services
     * @param $uid
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function userInfo(UserServices $services, $uid)
    {
        return app('json')->success($services->getUserInfo((int)$uid));
    }

    /**
     * 标签分类
     * @param UserLabelCateServices $services
     * @return mixed
     */
    public function getUserLabel(UserLabelCateServices $services, $uid)
    {
        return app('json')->success($services->getUserLabel((int)$uid));
    }

    /**
     * 获取用户分组
     * @param UserGroupServices $services
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserGroup(UserGroupServices $services)
    {
        return app('json')->success($services->getGroupList());
    }

    /**
     * 设置分组
     * @param UserGroupServices $services
     * @param UserServices $userServices
     * @param $uid
     * @param $id
     * @return mixed
     */
    public function setUserGroup(UserGroupServices $services, UserServices $userServices, $uid, $id)
    {
        if (!$services->count(['id' => $id])) {
            return app('json')->fail(100026);
        }
        if (!($userInfo = $userServices->get($uid))) {
            return app('json')->fail(410113);
        }
        if ($userInfo->group_id == $id) {
            return app('json')->fail(410103);
        }
        $userInfo->group_id = $id;
        if ($userInfo->save()) {
            return app('json')->success(100014);
        } else {
            return app('json')->fail(100015);
        }
    }

    /**
     * 设置用户标签
     * @param UserLabelRelationServices $services
     * @param $uid
     * @return mixed
     */
    public function setUserLabel(UserLabelRelationServices $services, $uid)
    {
        [$labels, $unLabelIds] = $this->request->postMore([
            ['label_ids', []],
            ['un_label_ids', []]
        ], true);
        if (!count($labels) && !count($unLabelIds)) {
            return app('json')->fail(410104);
        }
        if ($services->setUserLable($uid, $labels) && $services->unUserLabel($uid, $unLabelIds)) {
            return app('json')->success(100014);
        } else {
            return app('json')->fail(100015);
        }
    }

    /**
     * 退出登陆
     * @return mixed
     */
    public function logout()
    {
        $key = trim(ltrim($this->request->header(Config::get('cookie.token_name')), 'Bearer'));
        CacheService::delete(md5($key));
        return app('json')->success();
    }

    /**
     * 图片上传
     * @param Request $request
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function upload(Request $request, SystemAttachmentServices $services)
    {
        $data = $request->postMore([
            ['filename', 'file'],
        ]);
        if (!$data['filename']) return app('json')->fail(100100);
        if (Cache::has('start_uploads_' . $request->kefuId()) && Cache::get('start_uploads_' . $request->kefuId()) >= 100) return app('json')->fail('非法操作');
        $upload = UploadService::init();
        $info = $upload->to('store/comment')->validate()->move($data['filename']);
        if ($info === false) {
            return app('json')->fail($upload->getError());
        }
        $res = $upload->getUploadInfo();
        $services->attachmentAdd($res['name'], $res['size'], $res['type'], $res['dir'], $res['thumb_path'], 1, (int)sys_config('upload_type', 1), $res['time'], 2);
        if (Cache::has('start_uploads_' . $request->kefuId()))
            $start_uploads = (int)Cache::get('start_uploads_' . $request->kefuId());
        else
            $start_uploads = 0;
        $start_uploads++;
        Cache::set('start_uploads_' . $request->kefuId(), $start_uploads, 86400);
        $res['dir'] = path_to_url($res['dir']);
        if (strpos($res['dir'], 'http') === false) $res['dir'] = $request->domain() . $res['dir'];
        return app('json')->success(410091, ['name' => $res['name'], 'url' => $res['dir']]);
    }

}
