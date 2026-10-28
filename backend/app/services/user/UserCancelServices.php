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
namespace app\services\user;

use app\dao\user\UserCancelDao;
use crmeb\basic\BaseService;
use crmeb\services\CacheService;

class UserCancelServices extends BaseService
{
    /**
     * @var UserCancelDao
     */
    protected $dao;

    protected $status = ['待审核', '已通过', '已拒绝'];

    /**
     * UserCancelServices constructor.
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
        $userServices->update($uid, ['is_del' => 1]);
        $children = event('DistributionServiceProxy', [
            'service' => 'DistributionRelationServices',
            'action' => 'childUids',
            'args' => [(int)$uid],
        ]);
        if (is_array($children) && is_array($children[0] ?? null)) {
            foreach ($children[0] as $childUid) {
                event('DistributionServiceProxy', [
                    'service' => 'DistributionRelationServices',
                    'action' => 'unbind',
                    'args' => [(int)$childUid],
                ]);
            }
        } else {
            $userServices->update(['spread_uid' => $uid], ['spread_uid' => 0, 'spread_time' => 0]);
        }
        //通过事件代理更新微信用户状态，避免 app 层直接引用 modules
        event('ChannelServiceProxy', ['endpoint' => 'wechat_mp', 'service' => 'WechatUserServices', 'action' => 'update', 'args' => [['uid' => $uid], ['is_del' => 1]]]);
        //通过事件代理删除客服身份，避免 app 层直接引用客服模块
        event('KefuServiceProxy', ['service' => 'StoreServiceServices', 'action' => 'delete', 'args' => [['uid' => $uid]]]);

        $user = $userServices->getUserInfo($uid);

        //触发用户注销事件
        event('UserCancel', [$uid, $user, 'user_cancel']);

        return true;
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
