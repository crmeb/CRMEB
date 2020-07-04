<?php

namespace app\admin\controller\wechat;

use app\admin\controller\AuthController;
use app\admin\model\system\SystemAttachment;
use crmeb\services\{
    CacheService,
    FormBuilder as Form,
    UtilService as Util,
    JsonService as Json
};
use think\facade\Route as Url;
use app\admin\model\wechat\{
    StoreService as ServiceModel, StoreServiceLog as StoreServiceLog, WechatUser as UserModel
};

/**
 * 客服管理
 * Class StoreService
 * @package app\admin\controller\store
 */
class StoreService extends AuthController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $this->assign(ServiceModel::getList(0));
        return $this->fetch();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $where = Util::getMore([
            ['nickname', ''],
            ['data', ''],
            ['tagid_list', ''],
            ['groupid', '-1'],
            ['sex', ''],
            ['export', ''],
            ['stair', ''],
            ['second', ''],
            ['order_stair', ''],
            ['order_second', ''],
            ['subscribe', ''],
            ['now_money', ''],
            ['is_promoter', ''],
        ], $this->request);
        $this->assign('where', $where);
        $this->assign(UserModel::systemPage($where));
        $this->assign(['title' => '添加客服', 'save' => Url::buildUrl('save')]);
        return $this->fetch();
    }

    /**
     * 保存新建的资源
     */
    public function save()
    {
        $params = request()->post();
        if (count($params["checked_menus"]) <= 0) return Json::fail('请选择要添加的用户!');
        if (ServiceModel::where('mer_id', 0)->where(array("uid" => array("in", implode(',', $params["checked_menus"]))))->count()) return Json::fail('添加用户中存在已有的客服!');
        foreach ($params["checked_menus"] as $key => $value) {
            $now_user = UserModel::get($value);
            $data[$key]["mer_id"] = 0;
            $data[$key]["uid"] = $now_user["uid"];
            $data[$key]["avatar"] = $now_user["headimgurl"];
            $data[$key]["nickname"] = $now_user["nickname"];
            $data[$key]["add_time"] = time();
        }
        ServiceModel::setAll($data);
        return Json::successful('添加成功!');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $service = ServiceModel::get($id);
        if (!$service) return Json::fail('数据不存在!');
        $f = array();
        $f[] = Form::frameImageOne('avatar', '客服头像', Url::buildUrl('admin/widget.images/index', array('fodder' => 'avatar')), $service['avatar'])->icon('image')->width('100%')->height('500px');
        $f[] = Form::input('nickname', '客服名称', $service["nickname"]);
        $f[] = Form::radio('customer', '统计管理', $service['customer'])->options([['value' => 1, 'label' => '开启'], ['value' => 0, 'label' => '关闭']]);
        $f[] = Form::radio('notify', '订单通知', $service['notify'])->options([['value' => 1, 'label' => '开启'], ['value' => 0, 'label' => '关闭']]);
        $f[] = Form::radio('status', '客服状态', $service['status'])->options([['value' => 1, 'label' => '在线'], ['value' => 0, 'label' => '离线']]);
        $form = Form::make_post_form('修改数据', $f, Url::buildUrl('update', compact('id')));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function update($id)
    {
        $params = request()->post();
        if (empty($params["nickname"])) return Json::fail("客服名称不能为空！");
        $data = array("avatar" => $params["avatar"]
        , "nickname" => $params["nickname"]
        , 'status' => $params['status']
        , 'notify' => $params['notify']
        , 'customer' => $params['customer']
        );
        ServiceModel::edit($data, $id);
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
        if (!ServiceModel::del($id))
            return Json::fail(ServiceModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function chat_user($id)
    {
        $now_service = ServiceModel::get($id);
        if (!$now_service) return Json::fail('数据不存在!');
        $list = ServiceModel::getChatUser($now_service->toArray(), 0);
        $this->assign(compact('list', 'now_service'));
        return $this->fetch();
    }

    /**
     * @param int $uid
     * @param int $to_uid
     * @return string
     */
    public function chat_list()
    {
        $where = Util::getMore([['uid', 0], ['to_uid', 0], ['id', 0]]);
        if ($where['uid'])
            CacheService::set('admin_chat_list' . $this->adminId, $where);
        $where = CacheService::get('admin_chat_list' . $this->adminId);
        $this->assign(StoreServiceLog::getChatList($where, 0));
        $this->assign('where', $where);
        return $this->fetch();
    }
}
