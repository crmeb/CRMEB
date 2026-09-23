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

namespace app\services\personnel;


use app\dao\personnel\ServicePersonnelDao;
use crmeb\basic\BaseService;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use crmeb\utils\FormBuilder;


/**
 * 服务人员管理
 * 适用于配送上门、上门服务等场景的人员管理
 */
class ServicePersonnelServices extends BaseService
{
    /**
     * @var ServicePersonnelDao
     */
    protected $dao;

    /**
     * 创建form表单
     * @var FormBuilder
     */
    protected $builder;

    /**构造方法
     * ServicePersonnelServices constructor.
     * @param ServicePersonnelDao $dao
     * @param FormBuilder $builder
     */
    public function __construct(ServicePersonnelDao $dao, FormBuilder $builder)
    {
        $this->dao = $dao;
        $this->builder = $builder;
    }

    /**
     * 获取服务人员列表
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
     *获取服务人员列表
     */
    public function getDeliveryList()
    {
        [$page, $limit] = $this->getPageValue();
        [$list, $count] = $this->dao->getList($page, $limit);
        return compact('list', 'count');
    }

    /**
     * 创建服务人员表单
     * @param array $formData
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function createServiceForm(array $formData = [])
    {
        if ($formData) {
            $field[] = $this->builder->frameImage('avatar', '服务人员头像', $this->url(config('app.admin_prefix', 'admin') . '/widget.images/index', ['fodder' => 'avatar'], true), $formData['avatar'] ?? '')->icon('el-icon-user')->width('950px')->height('560px')->props(['footer' => false]);
        } else {
            $field[] = $this->builder->frameImage('image', '商城用户', $this->url(config('app.admin_prefix', 'admin') . '/system.user/list', ['fodder' => 'image'], true))->icon('el-icon-user')->width('950px')->height('560px')->Props(['srcKey' => 'image', 'footer' => false]);
            $field[] = $this->builder->hidden('uid', 0);
            $field[] = $this->builder->hidden('avatar', '');
        }
        $field[] = $this->builder->input('nickname', '服务人员名称', $formData['nickname'] ?? '')->required('请填写名称')->col(24);
        $field[] = $this->builder->input('phone', '手机号码', $formData['phone'] ?? '')->required('请填写电话')->col(24)->maxlength(11);
        $field[] = $this->builder->radio('status', '服务人员状态', $formData['status'] ?? 1)->options([['value' => 1, 'label' => '显示'], ['value' => 0, 'label' => '隐藏']]);
        return $field;
    }

    /**
     * 创建服务人员获取表单
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function create()
    {
        return create_form('添加服务人员', $this->createServiceForm(), $this->url('/setting/service_personnel/save'), 'POST');
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
            throw new AdminException('数据不存在');
        }
        return create_form('编辑服务人员', $this->createServiceForm($serviceInfo->toArray()), $this->url('/setting/service_personnel/' . $id), 'PUT');
    }

    /**
     * 检查用户是否是服务人员
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
        if ($data['image'] === '') throw new AdminException('请选择用户');
        $data['uid'] = $data['image']['uid'];
        /** @var UserServices $userService */
        $userService = app()->make(UserServices::class);
        $userInfo = $userService->get($data['uid']);
        if ($data['phone'] === '') {
            if (!$userInfo['phone']) {
                throw new AdminException('请填写手机号');
            } else {
                $data['phone'] = $userInfo['phone'];
            }
        } else {
            if (!check_phone($data['phone'])) {
                throw new AdminException('手机号格式错误');
            }
        }
        if ($data['nickname'] === '') $data['nickname'] = $userInfo['nickname'];
        $data['avatar'] = $data['image']['image'] ?? $userInfo['avatar'] ?? '';
        if ($this->dao->count(['uid' => $data['uid']])) {
            throw new AdminException('服务人员已存在');
        }
        if ($this->dao->count(['phone' => $data['phone']])) {
            throw new AdminException('同一个手机号的服务人员只能添加一个');
        }
        unset($data['image']);
        $data['add_time'] = time();
        $res = $this->dao->save($data);
        if (!$res) throw new AdminException('保存失败');
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
            throw new AdminException('数据不存在');
        }
        if ($data["nickname"] === '') {
            throw new AdminException('服务人员名称不能为空');
        }
        if (!$data['phone']) {
            throw new AdminException('请填写手机号');
        }
        if (!check_phone($data['phone'])) {
            throw new AdminException('手机号格式错误');
        }
        if ($delivery['phone'] != $data['phone'] && $this->dao->count(['phone' => $data['phone']])) {
            throw new AdminException('同一个手机号的服务人员只能添加一个');
        }
        $res = $this->dao->update($id, $data);
        if (!$res) throw new AdminException('修改失败');
        return true;
    }
}
