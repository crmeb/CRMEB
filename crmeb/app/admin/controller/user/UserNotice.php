<?php

namespace app\admin\controller\user;

use app\admin\controller\AuthController;
use crmeb\services\{FormBuilder as Form, UtilService as Util, JsonService as Json};
use crmeb\services\JsonService;
use think\facade\Route as Url;
use app\admin\model\user\UserNotice as UserNoticeModel;
use app\admin\model\user\UserNoticeSee as UserNoticeSeeModel;
use app\admin\model\wechat\WechatUser as UserModel;

/**
 * 用户通知
 * Class UserNotice
 * @package app\admin\controller\user
 */
class UserNotice extends AuthController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            $where = Util::getMore([
                ['page', 1],
                ['limit', 20]
            ]);
            return Json::successlayui(UserNoticeModel::getList($where));
        } else {
            return $this->fetch();
        }
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $f = array();
        $f[] = Form::input('user', '发送人', '系统管理员');
        $f[] = Form::input('title', '通知标题');
        $f[] = Form::input('content', '通知内容')->type('textarea');
        $f[] = Form::radio('type', '消息类型', 1)->options([['label' => '系统消息', 'value' => 1], ['label' => '用户通知', 'value' => 2]]);
        $form = Form::make_post_form('添加用户通知', $f, Url::buildUrl('save'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存新建的资源
     */
    public function save()
    {
        $params = request()->post();
        if (!$params["user"]) return Json::fail('请输入发送人！');
        if (!$params["title"]) return Json::fail('请输入通知标题！');
        if (!$params["content"]) return Json::fail('请输入通知内容！');
        if ($params["type"] == 2) {
            $uids = UserModel::order('uid desc')->column("uid", 'uid');
            $params["uid"] = count($uids) > 0 ? "," . implode(",", $uids) . "," : "";
        }
        $params["add_time"] = time();
        $params["send_time"] = 0;
        UserNoticeModel::create($params);
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
        $notice = UserNoticeModel::get($id);
        if (!$notice) return Json::fail('数据不存在!');
        $f = array();
        $f[] = Form::input('user', '发送人', $notice["user"]);
        $f[] = Form::input('title', '通知标题', $notice["title"]);
        $f[] = Form::input('content', '通知内容', $notice["content"])->type('textarea');
        $f[] = Form::radio('type', '消息类型', $notice["type"])->options([['label' => '系统消息', 'value' => 1], ['label' => '用户通知', 'value' => 2]]);
        $form = Form::make_post_form('编辑通知', $f, Url::buildUrl('update', ["id" => $id]), 2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存新建的资源
     * @param $id
     */
    public function update($id)
    {
        $params = request()->post();
        if (!$params["user"]) return Json::fail('请输入发送人！');
        if (!$params["title"]) return Json::fail('请输入通知标题！');
        if (!$params["content"]) return Json::fail('请输入通知内容！');
        UserNoticeModel::edit($params, $id);
        return Json::successful('修改成功!');
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function send($id)
    {
        UserNoticeModel::edit(array("is_send" => 1, "send_time" => time()), $id);
        return Json::successful('发送成功!');
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!UserNoticeModel::del($id))
            return Json::fail(UserNoticeModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }

    /**
     * 查询发送信息的用户资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function user($id)
    {
        $notice = UserNoticeModel::get($id)->toArray();
        $model = new UserModel;
        $model = $model::alias('A');
        $model = $model->field('A.*');
        if ($notice["type"] == 2) {
            if ($notice["uid"] != "") {
                $uids = explode(",", $notice["uid"]);
                array_splice($uids, 0, 1);
                array_splice($uids, count($uids) - 1, 1);
                $model = $model->where("A.uid", "in", $uids);
            } else {
                $model = $model->where("A.uid", $notice['uid']);
            }
            $model->order('A.uid desc');
        } else {
            $model = $model->join('UserNoticeSee B', 'A.uid = B.uid', 'RIGHT');
            $model = $model->where("B.nid", $notice['id']);
            $model->order('B.add_time desc');
        }
        $this->assign(UserModel::page($model, function ($item, $key) use ($notice) {
            $item["is_see"] = UserNoticeSeeModel::where("uid", $item["uid"])->where("nid", $notice["id"])->count() > 0 ? 1 : 0;
        }));
        $this->assign(compact('notice'));
        return $this->fetch();
    }

    /**
     * 添加发送信息的用户
     *
     * @param $id
     * @return string
     */
    public function user_create($id)
    {
        $where = Util::getMore([
            ['nickname', ''],
            ['data', ''],
        ], $this->request);
        $this->assign('where', $where);
        $this->assign(UserModel::systemPage($where));
        $this->assign(['title' => '添加发送用户', 'save' => Url::buildUrl('user_save', array('id' => $id))]);
        return $this->fetch();
    }

    /**
     * 保存新建的资源
     * @param $id
     */
    public function user_save($id)
    {
        $notice = UserNoticeModel::get($id)->toArray();
        if (!$notice) return Json::fail('通知信息不存在!');
        if ($notice["type"] == 1) return Json::fail('系统通知不能管理用户!');

        //查找当前选中的uid
        $params = request()->post();
        if (isset($params["search"])) {
            $model = new UserModel;
            if ($params['search']['nickname'] !== '') $model = $model->where('nickname', 'LIKE', "%" . $params['search']['nickname'] . "%");
            if ($params['search']['data'] !== '') {
                list($startTime, $endTime) = explode(' - ', $params['search']['data']);
                $model = $model->where('add_time', '>', strtotime($startTime));
                $model = $model->where('add_time', '<', strtotime($endTime));
            }
            $model = $model->order('uid desc');
            $uids = $model->column("uid", 'uid');
        } else {
            $uids = $params["checked_menus"];
        }
        if (count($uids) <= 0) return Json::fail('请选择要添加的用户!');

        //合并原来和现在的uid
        if ($notice["uid"] != "") {
            $now_uids = explode(",", $notice["uid"]);
            array_splice($now_uids, 0, 1);
            array_splice($now_uids, count($now_uids) - 1, 1);
            $now_uids = array_merge($now_uids, $uids);
        } else {
            $now_uids = $uids;
        }

        //编辑合并之后的uid
        $res_uids = UserModel::where("uid", "in", $now_uids)->order('uid desc')->column("uid", 'uid');
        UserNoticeModel::edit(array("uid" => "," . implode(",", $res_uids) . ","), $notice["id"]);
        return Json::successful('添加成功!');
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function user_delete($id, $uid)
    {
        $notice = UserNoticeModel::get($id)->toArray();
        if (!$notice) return Json::fail('通知信息不存在!');
        if ($notice["type"] == 1) return Json::fail('系统通知不能管理用户!');
        if ($notice["uid"] != "") {
            $res_uids = explode(",", $notice["uid"]);
            array_splice($res_uids, 0, 1);
            array_splice($res_uids, count($res_uids) - 1, 1);
        }
        array_splice($res_uids, array_search($uid, $res_uids), 1);
        $value = count($res_uids) > 0 ? "," . implode(",", $res_uids) . "," : "";
        UserNoticeModel::edit(array("uid" => $value), $notice["id"]);
        return Json::successful('删除成功!');
    }

    /**
     * 删除指定的资源
     * @param $id
     */
    public function user_select_delete($id)
    {
        $params = request()->post();
        if (count($params["checked_menus"]) <= 0) return Json::fail('删除数据不能为空!');
        $notice = UserNoticeModel::get($id)->toArray();
        if (!$notice) return Json::fail('通知信息不存在!');

        $res_uids = explode(",", $notice["uid"]);
        array_splice($res_uids, 0, 1);
        array_splice($res_uids, count($res_uids) - 1, 1);
        foreach ($params["checked_menus"] as $key => $value) {
            array_splice($res_uids, array_search($value, $res_uids), 1);
        }
        $value = count($res_uids) > 0 ? "," . implode(",", $res_uids) . "," : "";
        UserNoticeModel::edit(array("uid" => $value), $notice["id"]);
        return Json::successful('删除成功!');
    }

    /**
     * @param $id
     * @return mixed
     */
    public function notice($id)
    {
        $where = Util::getMore([
            ['title', ''],
        ], $this->request);
        $nickname = UserModel::where('uid', 'IN', $id)->column('nickname', 'uid');
        $this->assign('where', $where);
        $this->assign('uid', $id);
        $this->assign('nickname', implode(',', $nickname));
        $this->assign(UserNoticeModel::getUserList($where));
        return $this->fetch();
    }


    /**
     * 给指定用户发送站内信息
     * @param $id
     */
    public function send_user($id = 0, $uid = '')
    {
        if (!$id || $uid == '') return Json::fail('参数错误');
        $uids = UserNoticeModel::where(['id' => $id])->value('uid');
        $uid = rtrim($uids, ',') . "," . $uid . ",";
        UserNoticeModel::edit(array("send_time" => time(), 'uid' => $uid), $id);
        return Json::successful('发送成功!');
    }
}