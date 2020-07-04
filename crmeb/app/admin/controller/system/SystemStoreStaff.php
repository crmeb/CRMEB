<?php

namespace app\admin\controller\system;

use app\admin\controller\AuthController;
use app\admin\model\system\SystemStore as StoreModel;
use crmeb\services\FormBuilder as Form;
use crmeb\services\JsonService;
use crmeb\services\JsonService as Json;
use app\admin\model\system\SystemStoreStaff as StaffModel;
use app\admin\model\system\SystemStore;
use crmeb\services\UtilService;
use think\facade\Route as Url;

/**
 * 店员管理
 * Class StoreStaff
 * @package app\store_admin\controller\store
 */
class SystemStoreStaff extends AuthController
{
    /**
     * 店员列表
     */
    public function list()
    {
        $where = UtilService::getMore([
            ['page', 1],
            ['limit', 20],
            ['name', ''],
            ['store_id', '']
        ]);
        return JsonService::successlayui(StaffModel::lst($where));
    }

    /**
     * 门店设置
     * @return string
     */
    public function index()
    {
        $store_list = StoreModel::dropList();
        $this->assign('store_list', $store_list);
        return $this->fetch();
    }

    /**
     * 店员添加
     * @param int $id
     * @return string
     */
    public function create()
    {
        $field = [
            Form::frameImageOne('image', '商城用户', Url::buildUrl('admin/system.SystemStoreStaff/select', array('fodder' => 'image')))->icon('plus')->width('100%')->height('500px'),
            Form::hidden('uid', 0),
            Form::hidden('avatar', ''),
            Form::select('store_id', '所属门店')->setOptions(function () {
                $list = SystemStore::dropList();
//                $menus[] = ['value' => 0, 'label' => '顶级分类'];
                $menus = [];
                foreach ($list as $menu) {
                    $menus[] = ['value' => $menu['id'], 'label' => $menu['name']];
                }
                return $menus;
            })->filterable(1),
            Form::input('staff_name', '店员名称')->col(Form::col(24)),
            Form::input('phone', '手机号码')->col(Form::col(24)),
            Form::radio('verify_status', '核销开关', 1)->options([['value' => 1, 'label' => '开启'], ['value' => 0, 'label' => '关闭']]),
            Form::radio('status', '状态', 1)->options([['value' => 1, 'label' => '开启'], ['value' => 0, 'label' => '关闭']])
        ];
        $form = Form::make_post_form('添加评论', $field, Url::buildUrl('save'), 2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 选择用户
     * @param int $id
     */
    public function select()
    {
        return $this->fetch();
    }

    /**
     * 编辑表单
     * @param $id
     * @return string|void
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public function edit($id)
    {
        $service = StaffModel::get($id);
        if (!$service) return Json::fail('数据不存在!');
        $f = [
            Form::frameImageOne('image', '商城用户', Url::buildUrl('admin/system.SystemStoreStaff/select', array('fodder' => 'image')), $service['avatar'])->icon('plus')->width('100%')->height('500px'),
            Form::hidden('uid', $service['uid']),
            Form::hidden('avatar', $service['avatar']),
            Form::select('store_id', '所属门店', (string)$service->getData('store_id'))->setOptions(function () {
                $list = SystemStore::dropList();
//                $menus[] = ['value' => 0, 'label' => '顶级分类'];
                foreach ($list as $menu) {
                    $menus[] = ['value' => $menu['id'], 'label' => $menu['name']];
                }
                return $menus;
            })->filterable(1),
            Form::input('staff_name', '店员名称', $service['staff_name'])->col(Form::col(24)),
            Form::input('phone', '手机号码', $service['phone'])->col(Form::col(24)),
            Form::radio('verify_status', '核销开关', $service['verify_status'])->options([['value' => 1, 'label' => '开启'], ['value' => 0, 'label' => '关闭']]),
            Form::radio('status', '状态', $service['status'])->options([['value' => 1, 'label' => '开启'], ['value' => 0, 'label' => '关闭']])
        ];

        $form = Form::make_post_form('修改数据', $f, Url::buildUrl('save', compact('id')));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 删除店员
     * @param $id
     */
    public function delete($id)
    {
        if (!$id) return $this->failed('数据不存在');
        if (!StaffModel::be(['id' => $id])) return $this->failed('数据不存在');
        if (!StaffModel::del($id))
            return Json::fail(StaffModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }

    /**
     * 设置单个店员是否开启
     * @param string $is_show
     * @param string $id
     * @return json
     */
    public function set_show($is_show = '', $id = '')
    {
        ($is_show == '' || $id == '') && JsonService::fail('缺少参数');
        $res = StaffModel::where(['id' => $id])->update(['status' => (int)$is_show]);
        if ($res) {
            return JsonService::successful($is_show == 1 ? '开启成功' : '关闭成功');
        } else {
            return JsonService::fail($is_show == 1 ? '开启失败' : '关闭失败');
        }
    }

    /**
     * 保存店员信息
     */
    public function save($id = 0)
    {
        $data = UtilService::postMore([
            ['uid', 0],
            ['avatar', ''],
            ['store_id', ''],
            ['staff_name', ''],
            ['phone', ''],
            ['verify_status', 1],
            ['status', 1],
        ]);
        if (!$id) {
            if (StaffModel::where('uid', $data['uid'])->count()) return Json::fail('添加的店员用户已存在!');
        }
        if ($data['uid'] == 0) return Json::fail('请选择用户');
        if ($data['store_id'] == '') return Json::fail('请选择所属门店');
        if ($id) {
            $res = StaffModel::edit($data, $id);
            if ($res) {
                return Json::successful('编辑成功');
            } else {
                return Json::fail('编辑失败');
            }
        } else {
            $data['add_time'] = time();
            $res = StaffModel::create($data);
            if ($res) {
                return Json::successful('店员添加成功');
            } else {
                return Json::fail('店员添加失败，请稍后再试');
            }
        }
    }

    /**
     * 获取user表
     * @param int $page
     * @param int $limit
     * @param string $nickname
     */
    public function get_user_list($page = 0, $limit = 10, $nickname = '')
    {
        return Json::successlayui(StaffModel::getUserList($page, $limit, $nickname));
    }

}