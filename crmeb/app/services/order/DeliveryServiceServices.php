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

namespace app\services\order;


use app\dao\order\DeliveryServiceDao;
use app\services\BaseServices;
use app\services\kefu\service\StoreServiceLogServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder;


/**
 * 配送
 * Class DeliveryServiceServices
 * @package app\services\order
 * @method getStoreServiceOrderNotice() 获取接受通知的客服
 */
class DeliveryServiceServices extends BaseServices
{
    /**
     * 创建form表单
     * @var Form
     */
    protected $builder;

    /**构造方法
     * DeliveryServiceServices constructor.
     * @param DeliveryServiceDao $dao
     * @param FormBuilder $builder
     */
    public function __construct(DeliveryServiceDao $dao, FormBuilder $builder)
    {
        $this->dao = $dao;
        $this->builder = $builder;
    }

    /**
     * 获取配送员列表
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
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     *获取配送员列表
     */
    public function getDeliveryList()
    {
        [$page, $limit] = $this->getPageValue();
        [$list, $count] = $this->dao->getList($page, $limit);
        return compact('list', 'count');
    }

    /**
     * 创建配送员表单
     * @param array $formData
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function createServiceForm(array $formData = [])
    {
        if ($formData) {
            $field[] = $this->builder->frameImage('avatar', '配送员头像', $this->url('admin/widget.images/index', ['fodder' => 'avatar'], true), $formData['avatar'] ?? '')->icon('ios-add')->width('950px')->height('505px')->modal(['footer-hide' => true]);
        } else {
            $field[] = $this->builder->frameImage('image', '商城用户', $this->url('admin/system.user/list', ['fodder' => 'image'], true))->icon('ios-add')->width('950px')->height('505px')->modal(['footer-hide' => true])->Props(['srcKey' => 'image']);
            $field[] = $this->builder->hidden('uid', 0);
            $field[] = $this->builder->hidden('avatar', '');
        }
        $field[] = $this->builder->input('nickname', '配送员名称', $formData['nickname'] ?? '')->required('请填写名称')->col(24);
        $field[] = $this->builder->input('phone', '手机号码', $formData['phone'] ?? '')->required('请填写电话')->col(24)->maxlength(11);
        $field[] = $this->builder->radio('status', '配送员状态', $formData['status'] ?? 1)->options([['value' => 1, 'label' => '显示'], ['value' => 0, 'label' => '隐藏']]);
        return $field;
    }

    /**
     * 创建配送员获取表单
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function create()
    {
        return create_form('添加配送员', $this->createServiceForm(), $this->url('/order/delivery/save'), 'POST');
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
            throw new AdminException(100026);
        }
        return create_form('编辑配送员', $this->createServiceForm($serviceInfo->toArray()), $this->url('/order/delivery/update/' . $id), 'PUT');
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
     * 检查用户是否是配送员
     * @param int $uid
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkoutIsService(int $uid)
    {
        return (bool)$this->dao->count(['uid' => $uid, 'status' => 1]);
    }

    /**
     * 保存新建的资源
     * @param array $data
     * @return void
     */
    public function saveDeliveryService(array $data)
    {
        if ($data['image'] == '') throw new AdminException(400466);
        $data['uid'] = $data['image']['uid'];
        /** @var UserServices $userService */
        $userService = app()->make(UserServices::class);
        $userInfo = $userService->get($data['uid']);
        if ($data['phone'] == '') {
            if (!$userInfo['phone']) {
                throw new AdminException(400132);
            } else {
                $data['phone'] = $userInfo['phone'];
            }
        } else {
            if (!check_phone($data['phone'])) {
                throw new AdminException(400252);
            }
        }
        if ($data['nickname'] == '') $data['nickname'] = $userInfo['nickname'];
        $data['avatar'] = $data['image']['image'];
        if ($this->dao->count(['uid' => $data['uid']])) {
            throw new AdminException(400467);
        }
        if ($this->dao->count(['phone' => $data['phone']])) {
            throw new AdminException(400468);
        }
        unset($data['image']);
        $data['add_time'] = time();
        $res = $this->dao->save($data);
        if (!$res) throw new AdminException(100006);
        return true;
    }

    /**
     * 更新资源
     * @param int $id
     * @param array $data
     * @return void
     */
    public function updateDeliveryService(int $id, array $data)
    {
        $delivery = $this->dao->get($id);
        if (!$delivery) {
            throw new AdminException(100026);
        }
        if ($data["nickname"] == '') {
            throw new AdminException(400469);
        }
        if (!$data['phone']) {
            throw new AdminException(400132);
        }
        if (!check_phone($data['phone'])) {
            throw new AdminException(400252);
        }
        if ($delivery['phone'] != $data['phone'] && $this->dao->count(['phone' => $data['phone']])) {
            throw new AdminException(400468);
        }
        $res = $this->dao->update($id, $data);
        if (!$res) throw new AdminException(100007);
        return true;
    }
}
