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
namespace app\services\message\service;


use app\dao\service\StoreServiceDao;
use app\services\BaseServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder;
use crmeb\traits\ServicesTrait;
use think\exception\ValidateException;

/**
 * 客服
 * Class StoreServiceServices
 * @package app\services\message\service
 * @method getStoreServiceOrderNotice() 获取接受通知的客服
 */
class StoreServiceServices extends BaseServices
{
    use ServicesTrait;

    /**
     * 创建form表单
     * @var Form
     */
    protected $builder;

    /**
     * 构造方法
     * StoreServiceServices constructor.
     * @param StoreServiceDao $dao
     */
    public function __construct(StoreServiceDao $dao, FormBuilder $builder)
    {
        $this->dao = $dao;
        $this->builder = $builder;
    }

    /**
     * 获取客服列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getServiceList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getServiceList($where, $page, $limit);
        $this->updateNonExistentService(array_column($list, 'uid'));
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * @param array $uids
     * @return bool
     */
    public function updateNonExistentService(array $uids = [])
    {
        if (!$uids) {
            return true;
        }
        /** @var UserServices $services */
        $services = app()->make(UserServices::class);
        $userUids = $services->getColumn([['uid', 'in', $uids]], 'uid');
        $unUids = array_diff($uids, $userUids);
        return $this->dao->deleteNonExistentService($unUids);
    }

    /**
     * 创建客服表单
     * @param array $formData
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function createServiceForm(array $formData = [])
    {
        if ($formData) {
            $field[] = $this->builder->frameImage('avatar', '客服头像', $this->url('admin/widget.images/index', ['fodder' => 'avatar'], true), $formData['avatar'] ?? '')->icon('ios-add')->width('950px')->height('420px');
        } else {
            $field[] = $this->builder->frameImage('image', '商城用户', $this->url('admin/system.user/list', ['fodder' => 'image'], true))->icon('ios-add')->width('950px')->height('550px')->Props(['srcKey' => 'image']);
            $field[] = $this->builder->hidden('uid', 0);
            $field[] = $this->builder->hidden('avatar', '');
        }
        $field[] = $this->builder->input('nickname', '客服名称', $formData['nickname'] ?? '')->col(24)->required();
        $field[] = $this->builder->input('phone', '手机号码', $formData['phone'] ?? '')->col(24)->required();
        if ($formData) {
            $field[] = $this->builder->input('account', '登录账号', $formData['account'] ?? '')->col(24)->required();
            $field[] = $this->builder->input('password', '登录密码')->type('password')->col(24);
            $field[] = $this->builder->input('true_password', '确认密码')->type('password')->col(24);
        } else {
            $field[] = $this->builder->input('account', '登录账号')->col(24)->required();
            $field[] = $this->builder->input('password', '登录密码')->type('password')->col(24)->required();
            $field[] = $this->builder->input('true_password', '确认密码')->type('password')->col(24)->required();
        }
        $field[] = $this->builder->switches('status', '客服状态', (int)($formData['status'] ?? 1))->appendControl(1, [
            $this->builder->switches('customer', '手机订单管理', $formData['customer'] ?? 0)->falseValue(0)->trueValue(1)->openStr('打开')->closeStr('关闭')->size('large'),
            $this->builder->switches('notify', '订单通知', $formData['notify'] ?? 0)->falseValue(0)->trueValue(1)->openStr('打开')->closeStr('关闭')->size('large'),
        ])->falseValue(0)->trueValue(1)->openStr('开启')->closeStr('关闭')->size('large');
        return $field;
    }

    /**
     * 创建客服获取表单
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function create()
    {
        return create_form('添加客服', $this->createServiceForm(), $this->url('/app/wechat/kefu'), 'POST');
    }

    /**
     * 编辑获取表单
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function edit(int $id)
    {
        $serviceInfo = $this->dao->get($id);
        if (!$serviceInfo) {
            throw new AdminException('数据不存在!');
        }
        return create_form('编辑客服', $this->createServiceForm($serviceInfo->toArray()), $this->url('/app/wechat/kefu/' . $id), 'PUT');
    }

    /**
     * 获取某人的聊天记录用户列表
     * @param int $uid
     * @return array|array[]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getChatUser(int $uid)
    {
        /** @var StoreServiceLogServices $serviceLog */
        $serviceLog = app()->make(StoreServiceLogServices::class);
        /** @var UserServices $serviceUser */
        $serviceUser = app()->make(UserServices::class);
        $uids = $serviceLog->getChatUserIds($uid);
        if (!$uids) {
            return [];
        }
        return $serviceUser->getUserList(['uid' => $uids], 'nickname,uid,avatar as headimgurl');
    }

    /**
     * 检查用户是否是客服
     * @param int $uid
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkoutIsService(int $uid)
    {
        return $this->dao->count(['uid' => $uid, 'status' => 1, 'customer' => 1]) ? true : false;
    }

    /**
     * 查询聊天记录和获取客服uid
     * @param int $uid 当前用户uid
     * @param int $uidTo 上翻页id
     * @param int $limit 展示条数
     * @param int $toUid 客服uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRecord(int $uid, int $uidTo, int $limit = 10, int $toUid = 0)
    {
        if (!$toUid) {
            $serviceInfoList = $this->getServiceList(['status' => 1, 'online' => 1]);
            if (!count($serviceInfoList)) {
                throw new ValidateException('暂无客服人员在线，请稍后联系');
            }
            $uids = array_column($serviceInfoList['list'], 'uid');
            if (!$uids) {
                throw new ValidateException('暂无客服人员在线，请稍后联系');
            }
            /** @var StoreServiceRecordServices $recordServices */
            $recordServices = app()->make(StoreServiceRecordServices::class);
            //上次聊天客服优先对话
            $toUid = $recordServices->getLatelyMsgUid(['to_uid' => $uid], 'user_id');
            //如果上次聊天的客不在当前客服中从新
            if (!in_array($toUid, $uids)) {
                $toUid = 0;
            }
            if (!$toUid) {
                $toUid = $uids[array_rand($uids)] ?? 0;
            }
            if (!$toUid) {
                throw new ValidateException('暂无客服人员在线，请稍后联系');
            }
        }
        $userInfo = $this->dao->get(['uid' => $toUid], ['nickname', 'avatar']);
        if (!$userInfo) {
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $userInfo = $userServices->get(['uid' => $toUid], ['nickname', 'avatar']);
            if (!$userInfo) {
                $userInfo['nickname'] = '';
                $userInfo['avatar'] = '';
            }
        }
        /** @var StoreServiceLogServices $logServices */
        $logServices = app()->make(StoreServiceLogServices::class);
        $result = ['serviceList' => [], 'uid' => $toUid, 'nickname' => $userInfo['nickname'], 'avatar' => $userInfo['avatar']];
        $serviceLogList = $logServices->getServiceChatList(['chat' => [$uid, $toUid], 'is_tourist' => 0], $limit, $uidTo);
        $result['serviceList'] = array_reverse($logServices->tidyChat($serviceLogList));
        return $result;
    }
}
