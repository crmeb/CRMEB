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
namespace app\adminapi\controller\user;

use app\services\user\UserServices;
use app\adminapi\controller\AuthController;
use think\exception\ValidateException;
use think\facade\App;

class UserController extends AuthController
{
    /**
     * @var UserServices
     */
    protected $services;
    
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
            ['isMember', ''],
            ['balance', []],
            ['integral', []],
            ['before_pay_time', ''],
            ['pay_count_num', []],
            ['pay_count_money', []],
            ['recharge_count', []],
            ['agent_level', 0],
        ]);
        $where['label_id'] = toIntArray($where['label_id']);
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
            ['email', ''],
            ['birthday', ''],
            ['card_id', ''],
            ['addres', ''],
            ['mark', ''],
            ['pwd', ''],
            ['true_pwd', ''],
            ['level', 0],
            ['group_id', 0],
            ['label_id', []],
            ['status', 0]
        ]);
        if (!$data['real_name']) {
            return app('json')->fail('请填写姓名和电话');
        }
        if (!$data['phone']) {
            return app('json')->fail('请填写姓名和电话');
        }
        if (!check_phone($data['phone'])) {
            return app('json')->fail('手机号格式错误');
        }
        if ($data['email'] && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return app('json')->fail('邮箱格式错误');
        }
        if ($this->services->count(['phone' => $data['phone'], 'is_del' => 0])) {
            return app('json')->fail('手机号已经存在');
        }
        $data['nickname'] = $data['real_name'];
        if ($data['card_id']) {
            if (!check_card($data['card_id'])) return app('json')->fail('请输入正确的身份证');
        }
        if (!$data['pwd']) {
            return app('json')->fail('请输入密码');
        }
        if (!$data['true_pwd']) {
            return app('json')->fail('请输入确认密码');
        }
        if ($data['pwd'] != $data['true_pwd']) {
            return app('json')->fail('两次输入的密码不一致');
        }
        if (strlen($data['pwd']) < 6 || strlen($data['pwd']) > 32) {
            return app('json')->fail('账号密码必须是在6到32位之间');
        }
        $data['pwd'] = md5($data['pwd']);
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
        $uid = 0;
        $this->services->transaction(function () use ($data, $label, &$uid) {
            $res = true;
            $userInfo = $this->services->save($data);
            $uid = (int)$userInfo->uid;
            if ($label) {
                $res = $res && $this->services->saveSetLabel([$uid], $label);
            }
            if ($data['level']) {
                $res = $res && $this->services->saveGiveLevel($uid, (int)$data['level']);
            }
            if (!$res) {
                return app('json')->fail('保存失败');
            }
        });
        // 触发用户注册事件，新人礼插件监听此事件发放奖励
        if ($uid) {
            event('UserRegister', [0, 'h5', $data['nickname'], $uid, 1]);
        }
        return app('json')->success('添加成功');
    }

    /**
     * 获取用户账户详情
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
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
    public function giveLevel($id)
    {
        if (!$id) return app('json')->fail('参数错误');
        return app('json')->success($this->services->giveLevel((int)$id));
    }

    /**
     * 执行赠送会员等级
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveGiveLevel($id)
    {
        if (!$id) return app('json')->fail('参数错误');
        list($level_id) = $this->request->postMore([
            ['level_id', 0],
        ], true);
        return app('json')->success($this->services->saveGiveLevel((int)$id, (int)$level_id) ? '赠送成功' : '赠送失败');
    }

    /**
     * 赠送付费会员时长表单
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function giveLevelTime($id)
    {
        if (!$id) return app('json')->fail('参数错误');
        return app('json')->success($this->services->giveLevelTime((int)$id));
    }

    /**
     * 执行赠送付费会员时长
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveGiveLevelTime($id)
    {
        if (!$id) return app('json')->fail('参数错误');
        list($days) = $this->request->postMore([
            ['days', 0],
        ], true);
        return app('json')->success($this->services->saveGiveLevelTime((int)$id, (int)$days) ? '赠送成功' : '赠送失败');
    }

    /**
     * 清除会员等级
     * @param $id
     * @return mixed
     */
    public function delLevel($id)
    {
        if (!$id) return app('json')->fail('参数错误');
        return app('json')->success($this->services->cleanUpLevel((int)$id) ? '清除成功' : '清除失败');
    }

    /**
     * 设置会员分组
     * @return mixed
     */
    public function setGroup()
    {
        list($uids) = $this->request->postMore([
            ['uids', []],
        ], true);
        if (!$uids) return app('json')->fail('参数错误');
        return app('json')->success($this->services->setGroup($uids));
    }

    /**
     * 保存会员分组
     * @return mixed
     */
    public function saveSetGroup()
    {
        list($group_id, $uids) = $this->request->postMore([
            ['group_id', 0],
            ['uids', ''],
        ], true);
        if (!$uids) return app('json')->fail('参数错误');
        if (!$group_id) return app('json')->fail('请选择分组');
        $uids = explode(',', $uids);
        return app('json')->success($this->services->saveSetGroup($uids, (int)$group_id) ? '设置成功' : '设置失败');
    }

    /**
     * 设置用户标签
     * @return mixed
     */
    public function setLabel()
    {
        list($uids) = $this->request->postMore([
            ['uids', []],
        ], true);
        $uid = implode(',', $uids);
        if (!$uid) return app('json')->fail('参数错误');
        return app('json')->success($this->services->setLabel($uids));
    }

    /**
     * 保存用户标签
     * @return mixed
     */
    public function saveSetLabel()
    {
        list($labels, $uids, $label_type) = $this->request->postMore([
            ['label_id', []],
            ['uids', ''],
            ['label_type', 0],
        ], true);
        if (!$uids) return app('json')->fail('参数错误');
        if (!$labels) return app('json')->fail('请选择标签');
        $uids = explode(',', $uids);
        return app('json')->success($this->services->saveSetLabel($uids, $labels, $label_type) ? '设置成功' : '设置失败');
    }

    /**
     * 编辑其他
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function editOther($id, $type)
    {
        if (!$id) return app('json')->fail('数据不存在');
        return app('json')->success($this->services->editOther((int)$id, $type));
    }

    /**
     * 执行编辑其他
     * @param $id
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function updateOther($id)
    {
        $data = $this->request->postMore([
            ['money_status', 0],
            ['money', 0],
            ['integration_status', 0],
            ['integration', 0],
            ['mark', ''],
        ]);
        if (!$id) return app('json')->fail('参数错误');
        $data['adminId'] = $this->adminId;
        $data['money'] = (string)$data['money'];
        $data['integration'] = (string)$data['integration'];
        $data['is_other'] = true;
        return app('json')->success($this->services->updateInfo($id, $data) ? '修改成功' : '修改失败');
    }

    /**
     * 编辑会员信息
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function edit($id)
    {
        if (!$id) return app('json')->fail('参数错误');
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
            ['real_name', ''],
            ['email', ''],
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
            ['true_pwd']
        ]);
        if (!$id) return app('json')->fail('参数错误');
        if (!$data['real_name']) {
            return app('json')->fail('请填写姓名和电话');
        }
        if (!$data['phone']) {
            return app('json')->fail('请填写姓名和电话');
        }
        if ($data['phone']) {
            if (!preg_match("/^1[3456789]\d{9}$/", $data['phone'])) return app('json')->fail('手机号格式错误');
        }
        if ($data['email'] && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return app('json')->fail('邮箱格式错误');
        }
        if ($this->services->count(['phone' => $data['phone'], 'is_del' => 0, 'not_uid' => $id])) {
            return app('json')->fail('手机号已经存在');
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
            if (strlen($data['pwd']) < 6 || strlen($data['pwd']) > 32) {
                return app('json')->fail('账号密码必须是在6到32位之间');
            }
            $data['pwd'] = md5($data['pwd']);
        } else {
            unset($data['pwd']);
        }
        unset($data['true_pwd']);
        $data['adminId'] = $this->adminId;
        $data['money'] = (string)$data['money'];
        $data['integration'] = (string)$data['integration'];
        return app('json')->success($this->services->updateInfo($id, $data) ? '修改成功' : '修改失败');
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
        if ($data['type'] === '') return app('json')->fail('参数错误');
        return app('json')->success($this->services->oneUserInfo($id, $data['type']));
    }

    /**
     * 同步微信粉丝用户
     * @return mixed
     */
    public function syncWechatUsers()
    {
        //通过事件触发渠道模块处理粉丝同步，避免 app 层直接引用 modules
        event('WechatFanSync');
        return app('json')->success('加入消息队列成功');
    }

}
