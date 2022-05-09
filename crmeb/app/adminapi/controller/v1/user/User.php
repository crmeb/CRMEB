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
namespace app\adminapi\controller\v1\user;

use app\services\user\UserServices;
use app\adminapi\controller\AuthController;
use think\exception\ValidateException;
use think\facade\App;


class User extends AuthController
{
    /**
     * user constructor.
     * @param App $app
     * @param UserServices $services
     */
    public function __construct(App $app, UserServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 显示资源列表头部
     *
     * @return \think\Response
     */
    public function type_header()
    {
        $list = $this->services->typeHead();
        return app('json')->success(compact('list'));
    }


    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['page', 1],
            ['limit', 20],
            ['nickname', ''],
            ['status', ''],
            ['pay_count', ''],
            ['is_promoter', ''],
            ['order', ''],
            ['data', ''],
            ['user_type', ''],
            ['country', ''],
            ['province', ''],
            ['city', ''],
            ['user_time_type', ''],
            ['user_time', ''],
            ['sex', ''],
            [['level', 0], 0],
            [['group_id', 'd'], 0],
            ['label_id', ''],
            ['now_money', 'normal'],
            ['field_key', ''],
            ['isMember', '']
        ]);
        return app('json')->success($this->services->index($where));
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        return app('json')->success($this->services->saveForm());
    }

    /**
     * 添加编辑用户信息时候的信息
     * @param int $uid
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function userSaveInfo($uid = 0)
    {
        $data = $this->services->getUserSaveInfo($uid);
        return app('json')->success($data);
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['real_name', ''],
            ['phone', 0],
            ['birthday', ''],
            ['card_id', ''],
            ['addres', ''],
            ['mark', ''],
            ['pwd', ''],
            ['true_pwd', ''],
            ['level', 0],
            ['group_id', 0],
            ['label_id', []],
            ['spread_open', 1],
            ['is_promoter', 0],
            ['status', 0]
        ]);
        if ($data['phone']) {
            if (!check_phone($data['phone'])) {
                return app('json')->fail('手机号码格式不正确');
            }
            if ($this->services->count(['phone' => $data['phone'], 'is_del' => 0])) {
                return app('json')->fail('手机号已经存在不能添加相同的手机号用户');
            }
            if (trim($data['real_name']) != '') {
                $data['nickname'] = $data['real_name'];
            } else {
                $data['nickname'] = substr_replace($data['phone'], '****', 3, 4);
            }
        }
        if ($data['card_id']) {
            if (!check_card($data['card_id'])) return app('json')->fail('请输入正确的身份证');
        }
        if ($data['pwd']) {
            if (!$data['true_pwd']) {
                return app('json')->fail('请输入确认密码');
            }
            if ($data['pwd'] != $data['true_pwd']) {
                return app('json')->fail('两次输入的密码不一致');
            }
            $data['pwd'] = md5($data['pwd']);
        } else {
            unset($data['pwd']);
        }
        unset($data['true_pwd']);
        $data['avatar'] = sys_config('h5_avatar');
        $data['adminId'] = $this->adminId;
        $data['user_type'] = 'h5';
        $label = $data['label_id'];
        unset($data['label_id']);
        foreach ($label as $k => $v) {
            if (!$v) {
                unset($label[$k]);
            }
        }
        $data['birthday'] = empty($data['birthday']) ? 0 : strtotime($data['birthday']);
        $data['add_time'] = time();
        $this->services->transaction(function () use ($data, $label) {
            $res = true;
            $userInfo = $this->services->save($data);
            if ($label) {
                $res = $this->services->saveSetLabel([$userInfo->uid], $label);
            }
            if ($data['level']) {
                $res = $this->services->saveGiveLevel((int)$userInfo->uid, (int)$data['level']);
            }
            if (!$res) {
                throw new ValidateException('保存添加用户失败');
            }
        });
        return app('json')->success('添加成功');
    }

    /**
     * 显示指定的资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function read($id)
    {
        if (is_string($id)) {
            $id = (int)$id;
        }
        return app('json')->success($this->services->read($id));
    }

    /**
     * 赠送会员等级
     * @param int $uid
     * @return mixed
     * */
    public function give_level($id)
    {
        if (!$id) return app('json')->fail('缺少参数');
        return app('json')->success($this->services->giveLevel((int)$id));
    }

    /*
     * 执行赠送会员等级
     * @param int $uid
     * @return mixed
     * */
    public function save_give_level($id)
    {
        if (!$id) return app('json')->fail('缺少参数');
        list($level_id) = $this->request->postMore([
            ['level_id', 0],
        ], true);
        return app('json')->success($this->services->saveGiveLevel((int)$id, (int)$level_id) ? '赠送成功' : '赠送失败');
    }

    /**
     * 赠送付费会员时长
     * @param int $uid
     * @return mixed
     * */
    public function give_level_time($id)
    {
        if (!$id) return app('json')->fail('缺少参数');
        return app('json')->success($this->services->giveLevelTime((int)$id));
    }

    /*
     * 执行赠送付费会员时长
     * @param int $uid
     * @return mixed
     * */
    public function save_give_level_time($id)
    {
        if (!$id) return app('json')->fail('缺少参数');
        list($days) = $this->request->postMore([
            ['days', 0],
        ], true);
        return app('json')->success($this->services->saveGiveLevelTime((int)$id, (int)$days) ? '赠送成功' : '赠送失败');
    }

    /**
     * 清除会员等级
     * @param int $uid
     * @return json
     */
    public function del_level($id)
    {
        if (!$id) return app('json')->fail('缺少参数');
        return app('json')->success($this->services->cleanUpLevel((int)$id) ? '清除成功' : '清除失败');
    }

    /**
     * 设置会员分组
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function set_group()
    {
        list($uids) = $this->request->postMore([
            ['uids', []],
        ], true);
        if (!$uids) return app('json')->fail('缺少参数');
        return app('json')->success($this->services->setGroup($uids));
    }

    /**
     * 保存会员分组
     * @param $id
     * @return mixed
     */
    public function save_set_group()
    {
        list($group_id, $uids) = $this->request->postMore([
            ['group_id', 0],
            ['uids', ''],
        ], true);
        if (!$uids) return app('json')->fail('缺少参数');
        if (!$group_id) return app('json')->fail('请选择分组');
        $uids = explode(',', $uids);
        return app('json')->success($this->services->saveSetGroup($uids, (int)$group_id) ? '设置分组成功' : '设置分组失败或无改动');
    }

    /**
     * 设置用户标签
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function set_label()
    {
        list($uids) = $this->request->postMore([
            ['uids', []],
        ], true);
        $uid = implode(',', $uids);
        if (!$uid) return app('json')->fail('缺少参数');
        return app('json')->success($this->services->setLabel($uids));
    }

    /**
     * 保存用户标签
     * @return mixed
     */
    public function save_set_label()
    {
        list($lables, $uids) = $this->request->postMore([
            ['label_id', []],
            ['uids', ''],
        ], true);
        if (!$uids) return app('json')->fail('缺少参数');
        if (!$lables) return app('json')->fail('请选择标签');
        $uids = explode(',', $uids);
        return app('json')->success($this->services->saveSetLabel($uids, $lables) ? '设置标签成功' : '设置标签失败');
    }

    /**
     * 编辑其他
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function edit_other($id)
    {
        if (!$id) return app('json')->fail('数据不存在');
        return app('json')->success($this->services->editOther((int)$id));
    }

    /**
     * 执行编辑其他
     * @param int $id
     * @return mixed
     */
    public function update_other($id)
    {
        $data = $this->request->postMore([
            ['money_status', 0],
            ['money', 0],
            ['integration_status', 0],
            ['integration', 0],
        ]);
        if (!$id) return app('json')->fail('数据不存在');
        $data['adminId'] = $this->adminId;
        $data['money'] = (string)$data['money'];
        $data['integration'] = (string)$data['integration'];
        $data['is_other'] = true;
        return app('json')->success($this->services->updateInfo($id, $data) ? '修改成功' : '修改失败');
    }

    /**
     * 修改user表状态
     *
     * @return array
     */
    public function set_status($status, $id)
    {
//        if ($status == '' || $id == 0) return app('json')->fail('参数错误');
//        UserModel::where(['uid' => $id])->update(['status' => $status]);

        return app('json')->success($status == 0 ? '禁用成功' : '解禁成功');
    }

    /**
     * 编辑会员信息
     * @param $id
     * @return mixed|\think\response\Json|void
     */
    public function edit($id)
    {
        if (!$id) return app('json')->fail('数据不存在');
        return app('json')->success($this->services->edit($id));
    }

    public function update($id)
    {
        $data = $this->request->postMore([
            ['money_status', 0],
            ['is_promoter', 0],
            ['real_name', ''],
            ['card_id', ''],
            ['birthday', ''],
            ['mark', ''],
            ['money', 0],
            ['integration_status', 0],
            ['integration', 0],
            ['status', 0],
            ['level', 0],
            ['phone', 0],
            ['addres', ''],
            ['label_id', []],
            ['group_id', 0],
            ['pwd', ''],
            ['true_pwd'],
            ['spread_open', 1]
        ]);
        if ($data['phone']) {
            if (!preg_match("/^1[3456789]\d{9}$/", $data['phone'])) return app('json')->fail('手机号码格式不正确');
        }
        if ($data['card_id']) {
            if (!check_card($data['card_id'])) return app('json')->fail('请输入正确的身份证');
        }
        if ($data['pwd']) {
            if (!$data['true_pwd']) {
                return app('json')->fail('请输入确认密码');
            }
            if ($data['pwd'] != $data['true_pwd']) {
                return app('json')->fail('两次输入的密码不一致');
            }
            $data['pwd'] = md5($data['pwd']);
        } else {
            unset($data['pwd']);
        }
        unset($data['true_pwd']);
        if (!$id) return app('json')->fail('数据不存在');
        $data['adminId'] = $this->adminId;
        $data['money'] = (string)$data['money'];
        $data['integration'] = (string)$data['integration'];
        return app('json')->success($this->services->updateInfo($id, $data) ? '修改成功' : '修改失败');
    }

    /**
     * 获取单个用户信息
     * @param $id 用户id
     * @return mixed
     */
    public function oneUserInfo($id)
    {
        $data = $this->request->getMore([
            ['type', ''],
        ]);
        $id = (int)$id;
        if ($data['type'] == '') return app('json')->fail('缺少参数');
        return app('json')->success($this->services->oneUserInfo($id, $data['type']));
    }

    /**
     * 同步微信粉丝用户
     * @return mixed
     */
    public function syncWechatUsers()
    {
        $this->services->syncWechatUsers();
        return app('json')->success('加入消息队列成功，正在异步执行中');
    }

}
