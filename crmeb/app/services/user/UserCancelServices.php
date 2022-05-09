<?php

namespace app\services\user;

use app\dao\user\UserCancelDao;
use app\services\BaseServices;
use app\services\wechat\WechatUserServices;
use crmeb\services\CacheService;

class UserCancelServices extends BaseServices
{
    protected $status = ['待审核', '已通过', '已拒绝'];

    /**
     * UserExtractServices constructor.
     * @param UserCancelDao $dao
     */
    public function __construct(UserCancelDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 提交用户注销
     * @param $userInfo
     * @return mixed
     */
    public function SetUserCancel($uid)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        /** @var WechatUserServices $wechatUserServices */
        $wechatUserServices = app()->make(WechatUserServices::class);
        $userServices->update($uid, ['is_del' => 1]);
        $wechatUserServices->update(['uid' => $uid], ['is_del' => 1]);
    }

    /**
     * 获取注销列表
     * @param $where
     * @return array
     */
    public function getCancelList($where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, $page, $limit);
        foreach ($list as &$item) {
            $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
            $item['up_time'] = $item['up_time'] != 0 ? date('Y-m-d H:i:s', $item['add_time']) : '';
            $item['status'] = $this->status[$item['status']];
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 备注
     * @param $id
     * @param $mark
     * @return mixed
     */
    public function serMark($id, $mark)
    {
        return $this->dao->update($id, ['remark' => $mark]);
    }
}
