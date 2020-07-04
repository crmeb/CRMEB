<?php

namespace app\admin\controller\setting;

use app\admin\controller\AuthController;
use crmeb\services\{FormBuilder as Form, JsonService as Json, UtilService as Util};
use app\admin\model\system\{SystemRole, SystemAdmin as AdminModel};
use think\facade\Route as Url;

/**
 * 管理员列表控制器
 * Class SystemAdmin
 * @package app\admin\controller\system
 */
class SystemAdmin extends AuthController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $admin = $this->adminInfo;
        $where = Util::getMore([
            ['name', ''],
            ['roles', ''],
            ['level', bcadd($admin->level, 1, 0)]
        ]);
        $this->assign('where', $where);
        $this->assign('role', SystemRole::getRole(bcadd($admin->level, 1, 0)));
        $this->assign(AdminModel::systemPage($where));
        return $this->fetch();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $admin = $this->adminInfo;
        $f = array();
        $f[] = Form::input('account', '管理员账号');
        $f[] = Form::input('pwd', '管理员密码')->type('password');
        $f[] = Form::input('conf_pwd', '确认密码')->type('password');
        $f[] = Form::input('real_name', '管理员姓名');
        $f[] = Form::select('roles', '管理员身份')->setOptions(function () use ($admin) {
            $list = SystemRole::getRole(bcadd($admin->level, 1, 0));
            $options = [];
            foreach ($list as $id => $roleName) {
                $options[] = ['label' => $roleName, 'value' => $id];
            }
            return $options;
        })->multiple(1);
        $f[] = Form::radio('status', '状态', 1)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        $form = Form::make_post_form('添加管理员', $f, Url::buildUrl('save'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save()
    {
        $data = Util::postMore([
            'account',
            'conf_pwd',
            'pwd',
            'real_name',
            ['roles', []],
            ['status', 0]
        ]);
        if (!$data['account']) return Json::fail('请输入管理员账号');
        if (!$data['roles']) return Json::fail('请选择至少一个管理员身份');
        if (!$data['pwd']) return Json::fail('请输入管理员登陆密码');
        if ($data['pwd'] != $data['conf_pwd']) return Json::fail('两次输入密码不想同');
        if (AdminModel::be($data['account'], 'account')) return Json::fail('管理员账号已存在');
        $data['pwd'] = md5($data['pwd']);
        $data['add_time'] = time();
        unset($data['conf_pwd']);
        $data['level'] = $this->adminInfo['level'] + 1;
        $data['add_time'] = time();
        if (!AdminModel::create($data)) return Json::fail('添加管理员失败');
        return Json::successful('添加管理员成功!');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        if (!$id) return $this->failed('参数错误');
        $admin = AdminModel::get($id);
        if (!$admin) return Json::fail('数据不存在!');
        $f = array();
        $f[] = Form::input('account', '管理员账号', $admin->account);
        $f[] = Form::input('pwd', '管理员密码')->type('password');
        $f[] = Form::input('conf_pwd', '确认密码')->type('password');
        $f[] = Form::input('real_name', '管理员姓名', $admin->real_name);
        $f[] = Form::select('roles', '管理员身份', explode(',', $admin->roles))->setOptions(function () use ($admin) {
            $list = SystemRole::getRole($admin->level);
            $options = [];
            foreach ($list as $id => $roleName) {
                $options[] = ['label' => $roleName, 'value' => $id];
            }
            return $options;
        })->multiple(1);
        $f[] = Form::radio('status', '状态', 1)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        $form = Form::make_post_form('编辑管理员', $f, Url::buildUrl('update', compact('id')));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存更新的资源
     *
     * @param \think\Request $request
     * @param int $id
     * @return \think\Response
     */
    public function update($id)
    {
        $data = Util::postMore([
            'account',
            'conf_pwd',
            'pwd',
            'real_name',
            ['roles', []],
            ['status', 0]
        ]);
        if (!$data['account']) return Json::fail('请输入管理员账号');
        if (!$data['roles']) return Json::fail('请选择至少一个管理员身份');
        if (!$data['pwd'])
            unset($data['pwd']);
        else {
            if (isset($data['pwd']) && $data['pwd'] != $data['conf_pwd']) return Json::fail('两次输入密码不想同');
            $data['pwd'] = md5($data['pwd']);
        }
        if (AdminModel::where('account', $data['account'])->where('id', '<>', $id)->count()) return Json::fail('管理员账号已存在');
        unset($data['conf_pwd']);
        if (!AdminModel::edit($data, $id)) return Json::fail('修改失败');
        return Json::successful('修改成功!');
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!$id)
            return Json::fail('删除失败!');
        if (AdminModel::edit(['is_del' => 1, 'status' => 0], $id, 'id'))
            return Json::successful('删除成功!');
        else
            return Json::fail('删除失败!');
    }

    /**
     * 个人资料 展示
     * @return string
     */
    public function admin_info()
    {
        $adminInfo = $this->adminInfo;//获取当前登录的管理员
        $this->assign('adminInfo', $adminInfo);
        return $this->fetch();
    }

    /**
     * 保存信息
     */
    public function setAdminInfo()
    {
        $adminInfo = $this->adminInfo;//获取当前登录的管理员
        if ($this->request->isPost()) {
            $data = Util::postMore([
                ['new_pwd', ''],
                ['new_pwd_ok', ''],
                ['pwd', ''],
                'real_name',
            ]);
            if ($data['pwd'] != '') {
                $pwd = md5($data['pwd']);
                if ($adminInfo['pwd'] != $pwd) return Json::fail('原始密码错误');
            }
            if ($data['new_pwd'] != '') {
                if (!$data['new_pwd_ok']) return Json::fail('请输入确认新密码');
                if ($data['new_pwd'] != $data['new_pwd_ok']) return Json::fail('俩次密码不一样');
            }
            if ($data['pwd'] != '' && $data['new_pwd'] != '') {
                $data['pwd'] = md5($data['new_pwd']);
            } else {
                unset($data['pwd']);
            }
            unset($data['new_pwd']);
            unset($data['new_pwd_ok']);
            if (!AdminModel::edit($data, $adminInfo['id'])) return Json::fail('修改失败');
            return Json::successful('修改成功!,请重新登录');
        }
    }
}
