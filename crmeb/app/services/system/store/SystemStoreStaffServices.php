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

namespace app\services\system\store;


use app\dao\system\store\SystemStoreStaffDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder;

/**
 * 门店店员
 * Class SystemStoreStaffServices
 * @package app\services\system\store
 * @mixin SystemStoreStaffDao
 */
class SystemStoreStaffServices extends BaseServices
{
    /**
     * @var FormBuilder
     */
    protected $builder;

    /**
     * 构造方法
     * SystemStoreStaffServices constructor.
     * @param SystemStoreStaffDao $dao
     * @param FormBuilder $builder
     */
    public function __construct(SystemStoreStaffDao $dao, FormBuilder $builder)
    {
        $this->dao = $dao;
        $this->builder = $builder;
    }

    /**
     * 判断是否是有权限核销的店员
     * @param $uid
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function verifyStatus($uid)
    {
        return (bool)$this->dao->getOne(['uid' => $uid, 'status' => 1, 'verify_status' => 1]);
    }

    /**
     * 获取店员列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getStoreStaffList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getStoreStaffList($where, $page, $limit);
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 获取select选择框中的门店列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getStoreSelectFormData()
    {
        /** @var SystemStoreServices $service */
        $service = app()->make(SystemStoreServices::class);
        $menus = [];
        foreach ($service->getStore() as $menu) {
            $menus[] = ['value' => $menu['id'], 'label' => $menu['name']];
        }
        return $menus;
    }

    /**
     * 获取核销员表单
     * @param array $formData
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function createStoreStaffForm(array $formData = [])
    {
        if ($formData) {
            $field[] = $this->builder->frameImage('image', '更换头像', $this->url('admin/widget.images/index', array('fodder' => 'image'),true), $formData['avatar'] ?? '')->icon('ios-add')->width('950px')->height('505px')->modal(['footer-hide' => true]);
        } else {
            $field[] = $this->builder->frameImage('image', '商城用户', $this->url('admin/system.User/list', ['fodder' => 'image'], true))->icon('ios-add')->width('950px')->height('505px')->modal(['footer-hide' => true])->Props(['srcKey' => 'image']);
        }
        $field[] = $this->builder->hidden('uid', $formData['uid'] ?? 0);
        $field[] = $this->builder->hidden('avatar', $formData['avatar'] ?? '');
        $field[] = $this->builder->select('store_id', '所属提货点', ($formData['store_id'] ?? 0))->setOptions($this->getStoreSelectFormData())->filterable(true);
        $field[] = $this->builder->input('staff_name', '核销员名称', $formData['staff_name'] ?? '')->col(24)->required();
        $field[] = $this->builder->input('phone', '手机号码', $formData['phone'] ?? '')->col(24)->required();
        $field[] = $this->builder->radio('verify_status', '核销开关', $formData['verify_status'] ?? 1)->options([['value' => 1, 'label' => '开启'], ['value' => 0, 'label' => '关闭']]);
        $field[] = $this->builder->radio('status', '状态', $formData['status'] ?? 1)->options([['value' => 1, 'label' => '开启'], ['value' => 0, 'label' => '关闭']]);
        return $field;
    }

    /**
     * 添加核销员表单
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function createForm()
    {
        return create_form('添加核销员', $this->createStoreStaffForm(), $this->url('/merchant/store_staff/save/0'));
    }

    /**
     * 编辑核销员form表单
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function updateForm(int $id)
    {
        $storeStaff = $this->dao->get($id);
        if (!$storeStaff) {
            throw new AdminException(100026);
        }
        return create_form('修改核销员', $this->createStoreStaffForm($storeStaff->toArray()), $this->url('/merchant/store_staff/save/' . $id));
    }

}
