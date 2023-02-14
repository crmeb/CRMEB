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
     * 用户列表
     * @return mixed
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
     * 添加用户表单
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function create()
    {
        return app('json')->success($this->services->saveForm());
    }

    /**
     * 添加编辑用户信息时候的信息
     * @param $uid
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
     * 保存新建用户
     * @return mixed
     * @throws \think\Exception
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
        if (!$data['real_name']) {
            return app('json')->fail(410245);
        }
        if (!$data['phone']) {
            return app('json')->fail(410245);
        }
        if (!check_phone($data['phone'])) {
            return app('json')->fail(400252);
        }
        if ($this->services->count(['phone' => $data['phone'], 'is_del' => 0])) {
            return app('json')->fail(400314);
        }
        $data['nickname'] = $data['real_name'];
        if ($data['card_id']) {
            if (!check_card($data['card_id'])) return app('json')->fail(400315);
        }
        if ($data['pwd']) {
            if (!$data['true_pwd']) {
                return app('json')->fail(400263);
            }
            if ($data['pwd'] != $data['true_pwd']) {
                return app('json')->fail(400264);
            }
            if (strlen($data['pwd']) < 6 || strlen($data['pwd']) > 32) {
                return app('json')->fail(400762);
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
            $this->services->rewardNewUser((int)$userInfo->uid);
            if ($label) {
                $res = $this->services->saveSetLabel([$userInfo->uid], $label);
            }
            if ($data['level']) {
                $res = $this->services->saveGiveLevel((int)$userInfo->uid, (int)$data['level']);
            }
            if (!$res) {
                return app('json')->fail(100006);
            }
        });
        return app('json')->success(100021);
    }

    /**
     * 获取用户账户详情
     * @param $id
     * @return mixed
     */
    public function read($id)
    {
        if (is_string($id)) {
            $id = (int)$id;
        }
        return app('json')->success($this->services->read($id));
    }

    /**
     * 赠送会员等级表单
     * @param $id
     * @return mixed
     */
    public function give_level($id)
    {
        if (!$id) return app('json')->fail(100100);
        return app('json')->success($this->services->giveLevel((int)$id));
    }

    /**
     * 执行赠送会员等级
     * @param $id
     * @return mixed
     */
    public function save_give_level($id)
    {
        if (!$id) return app('json')->fail(100100);
        list($level_id) = $this->request->postMore([
            ['level_id', 0],
        ], true);
        return app('json')->success($this->services->saveGiveLevel((int)$id, (int)$level_id) ? 400218 : 400219);
    }

    /**
     * 赠送付费会员时长表单
     * @param $id
     * @return mixed
     */
    public function give_level_time($id)
    {
        if (!$id) return app('json')->fail(100100);
        return app('json')->success($this->services->giveLevelTime((int)$id));
    }

    /**
     * 执行赠送付费会员时长
     * @param $id
     * @return mixed
     */
    public function save_give_level_time($id)
    {
        if (!$id) return app('json')->fail(100100);
        list($days) = $this->request->postMore([
            ['days', 0],
        ], true);
        return app('json')->success($this->services->saveGiveLevelTime((int)$id, (int)$days) ? 400218 : 400219);
    }

    /**
     * 清除会员等级
     * @param $id
     * @return mixed
     */
    public function del_level($id)
    {
        if (!$id) return app('json')->fail(100100);
        return app('json')->success($this->services->cleanUpLevel((int)$id) ? 400185 : 400186);
    }

    /**
     * 设置会员分组
     * @return mixed
     */
    public function set_group()
    {
        list($uids) = $this->request->postMore([
            ['uids', []],
        ], true);
        if (!$uids) return app('json')->fail(100100);
        return app('json')->success($this->services->setGroup($uids));
    }

    /**
     * 保存会员分组
     * @return mixed
     */
    public function save_set_group()
    {
        list($group_id, $uids) = $this->request->postMore([
            ['group_id', 0],
            ['uids', ''],
        ], true);
        if (!$uids) return app('json')->fail(100100);
        if (!$group_id) return app('json')->fail(400316);
        $uids = explode(',', $uids);
        return app('json')->success($this->services->saveSetGroup($uids, (int)$group_id) ? 100014 : 100015);
    }

    /**
     * 设置用户标签
     * @return mixed
     */
    public function set_label()
    {
        list($uids) = $this->request->postMore([
            ['uids', []],
        ], true);
        $uid = implode(',', $uids);
        if (!$uid) return app('json')->fail(100100);
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
        if (!$uids) return app('json')->fail(100100);
        if (!$lables) return app('json')->fail(400317);
        $uids = explode(',', $uids);
        return app('json')->success($this->services->saveSetLabel($uids, $lables) ? 100014 : 100015);
    }

    /**
     * 编辑其他
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function edit_other($id)
    {
        if (!$id) return app('json')->fail(100026);
        return app('json')->success($this->services->editOther((int)$id));
    }

    /**
     * 执行编辑其他
     * @param $id
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function update_other($id)
    {
        $data = $this->request->postMore([
            ['money_status', 0],
            ['money', 0],
            ['integration_status', 0],
            ['integration', 0],
        ]);
        if (!$id) return app('json')->fail(100100);
        $data['adminId'] = $this->adminId;
        $data['money'] = (string)$data['money'];
        $data['integration'] = (string)$data['integration'];
        $data['is_other'] = true;
        return app('json')->success($this->services->updateInfo($id, $data) ? 100001 : 100007);
    }

    /**
     * 编辑会员信息
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function edit($id)
    {
        if (!$id) return app('json')->fail(100100);
        return app('json')->success($this->services->edit($id));
    }

    /**
     * 修改用户
     * @param $id
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
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
        if (!$id) return app('json')->fail(100100);
        if ($data['phone']) {
            if (!preg_match("/^1[3456789]\d{9}$/", $data['phone'])) return app('json')->fail(400252);
        }
        if ($data['card_id']) {
            if (!check_card($data['card_id'])) return app('json')->fail(400315);
        }
        if ($data['pwd']) {
            if (!$data['true_pwd']) {
                return app('json')->fail(400263);
            }
            if ($data['pwd'] != $data['true_pwd']) {
                return app('json')->fail(400264);
            }
            if (strlen($data['pwd']) < 6 || strlen($data['pwd']) > 32) {
                return app('json')->fail(400762);
            }
            $data['pwd'] = md5($data['pwd']);
        } else {
            unset($data['pwd']);
        }
        unset($data['true_pwd']);
        $data['adminId'] = $this->adminId;
        $data['money'] = (string)$data['money'];
        $data['integration'] = (string)$data['integration'];
        return app('json')->success($this->services->updateInfo($id, $data) ? 100001 : 100007);
    }

    /**
     * 获取单个用户信息
     * @param $id
     * @return mixed
     */
    public function oneUserInfo($id)
    {
        $data = $this->request->getMore([
            ['type', ''],
        ]);
        $id = (int)$id;
        if ($data['type'] == '') return app('json')->fail(100100);
        return app('json')->success($this->services->oneUserInfo($id, $data['type']));
    }

    /**
     * 同步微信粉丝用户
     * @return mixed
     */
    public function syncWechatUsers()
    {
        $this->services->syncWechatUsers();
        return app('json')->success(400318);
    }

}
