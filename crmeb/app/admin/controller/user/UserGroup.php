<?php
/**
 *
 * @author: wuhaotian<442384644@qq.com>
 * @day: 2019/12/07
 */

namespace app\admin\controller\user;

use app\admin\controller\AuthController;
use app\admin\model\user\UserGroup as GroupModel;
use crmeb\services\JsonService;
use crmeb\services\UtilService;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;

/**
 * Class UserGroup
 * @package app\admin\controller\user
 */
class UserGroup extends AuthController
{
    /**
     * 会员分组页面
     * @return string
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 分组列表
     */
    public function groupList()
    {
        $where = UtilService::getMore([
            ['page', 1],
            ['limit', 20],
        ]);
        return JsonService::successlayui(GroupModel::getList($where));
    }

    /**
     * 添加/修改分组页面
     * @param int $id
     * @return string
     */
    public function addGroup($id = 0)
    {
        $group = GroupModel::get($id);
        $f = array();
        if (!$group) {
            $f[] = Form::input('group_name', '分组名称', '');
        } else {
            $f[] = Form::input('group_name', '分组名称', $group->getData('group_name'));
        }
        $form = Form::make_post_form('添加用户通知', $f, Url::buildUrl('saveGroup', array('id' => $id)));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 添加/修改
     * @param int $id
     */
    public function saveGroup($id = 0)
    {
        $data = UtilService::postMore([
            ['group_name', ''],
        ]);
        if ($id) {
            if (GroupModel::where('id', $id)->update($data)) {
                return JsonService::success('修改成功');
            } else {
                return JsonService::fail('修改失败或者您没有修改什么！');
            }
        } else {
            if ($res = GroupModel::create($data)) {
                return JsonService::success('保存成功', ['id' => $res->id]);
            } else {
                return JsonService::fail('保存失败！');
            }
        }
    }

    /**
     * 删除
     * @param $id
     * @throws \Exception
     */
    public function delete($id)
    {
        if (!$id) return $this->failed('数据不存在');
        if (!GroupModel::be(['id' => $id])) return $this->failed('产品数据不存在');
        if (!GroupModel::where('id', $id)->delete())
            return JsonService::fail(GroupModel::getErrorInfo('恢复失败,请稍候再试!'));
        else
            return JsonService::successful('恢复门店成功!');
    }
}