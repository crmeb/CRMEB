<?php

namespace app\services\agent;

use app\services\BaseServices;
use app\services\order\StoreOrderServices;
use app\services\other\QrcodeServices;
use app\services\system\admin\SystemAdminServices;
use app\services\system\admin\SystemRoleServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;
use crmeb\services\FormBuilder as Form;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Route;

class DivisionServices extends BaseServices
{
    /**
     * 获取事业部/代理/员工列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getDivisionList(array $where = [])
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $data = $userServices->getDivisionList($where + ['status' => 1], 'uid,nickname,avatar,division_name,division_percent,division_end_time,division_status,division_invite');
        foreach ($data['list'] as &$item) {
            $item['division_end_time'] = date('Y-m-d', $item['division_end_time']);
            $item['agent_count'] = $userServices->count([
                $where['division_type'] == 1 ? 'division_id' : 'agent_id' => $item['uid'],
                'division_type' => $where['division_type'] + 1,
                'status' => 1,
                'is_del' => 0
            ]);
            unset($item['label']);
        }
        return $data;
    }

    /**
     * 下级列表
     * @param $type
     * @param $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function divisionDownList($type, $uid)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $where = [
            $type == 2 ? 'division_id' : 'agent_id' => $uid,
            'division_type' => $type
        ];
        $where['status'] = 1;
        $where['is_del'] = 0;
        $data = $userServices->getDivisionList($where, 'uid,nickname,avatar,division_name,division_percent,division_end_time,division_status');
        foreach ($data['list'] as &$item) {
            $item['agent_count'] = $userServices->count([
                'agent_id' => $item['uid'],
                'division_type' => $type + 1,
                'status' => 1
            ]);
            unset($item['label']);
        }
        return $data;
    }

    /**
     * 添加编辑事业部表单
     * @param $uid
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function getDivisionForm($uid)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        /** @var SystemAdminServices $adminService */
        $adminService = app()->make(SystemAdminServices::class);
        $userInfo = $userServices->getUserInfo($uid);
        if ($uid && !$userInfo) throw new AdminException(100100);
        if ($uid) {
            $adminInfo = $adminService->getInfo(['division_id' => $uid])->toArray();
            if (isset($adminInfo['roles'])) {
                foreach ($adminInfo['roles'] as &$item) {
                    $item = intval($item);
                }
            }
        }
        $field = [];
        $title = '事业部';
        $field[] = Form::input('division_name', '事业部名称', $userInfo['division_name'] ?? '')->required('请输入事业部名称');
        if ($uid) {
            $field[] = Form::hidden('uid', $uid);
        } else {
            $field[] = Form::frameImage('image', '关联用户', $this->url(config('app.admin_prefix', 'admin') . '/system.user/list', ['fodder' => 'image'], true))->icon('el-icon-user')->width('950px')->height('560px')->Props(['srcKey' => 'image', 'footer' => false]);
        }
        $field[] = Form::hidden('aid', $adminInfo['id'] ?? 0);
        $field[] = Form::number('division_percent', '佣金比例', $userInfo['division_percent'] ?? '')->placeholder('区域代理佣金比例1-100')->info('填写1-100，如填写50代表返佣50%')->style(['width' => '173px'])->min(0)->max(100)->required();
        $field[] = Form::date('division_end_time', '到期时间', ($userInfo['division_end_time'] ?? '') != 0 ? date('Y-m-d H:i:s', $userInfo['division_end_time']) : '')->placeholder('区域代理到期时间');
        $field[] = Form::radio('division_status', '代理状态', $userInfo['division_status'] ?? 1)->options([['label' => '开通', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        $field[] = Form::input('account', '管理账号', $adminInfo['account'] ?? '')->required('请填写管理员账号');
        $field[] = Form::input('pwd', '管理密码')->type('password')->placeholder('请填写管理员密码');
        $field[] = Form::input('conf_pwd', '确认密码')->type('password')->placeholder('请输入确认密码');
        /** @var SystemRoleServices $service */
        $service = app()->make(SystemRoleServices::class);
        $options = $service->getRoleFormSelect(1);
        $field[] = Form::select('roles', '管理员身份', $adminInfo['roles'] ?? [])->setOptions(Form::setOptions($options))->multiple(true)->required('请选择管理员身份');
        return create_form($title, $field, Route::buildUrl('/agent/division/save'), 'POST');
    }

    /**
     * 保存事业部数据
     * @param $data
     * @return mixed
     */
    public function divisionSave($data)
    {
        if ((int)$data['uid'] == 0) $data['uid'] = $data['image']['uid'];
        if ((int)$data['uid'] == 0) throw new AdminException(400450);
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        if ($data['aid'] == 0) {
            $userInfo = $userServices->getUserInfo($data['uid'], 'is_division,is_agent,is_staff');
            if (!$userInfo) throw new AdminException('用户不存在');
            if ($userInfo['is_division']) throw new AdminException('此用户是事业部，请勿重复添加');
            if ($userInfo['is_agent']) throw new AdminException('此用户是代理商，无法添加为事业部');
            if ($userInfo['is_staff']) throw new AdminException('此用户是下级员工，无法添加为事业部');
        }
        $uid = $data['uid'];
        $aid = $data['aid'];
        $agentData = [
            'division_percent' => $data['division_percent'],
            'division_end_time' => strtotime($data['division_end_time']),
            'division_change_time' => time(),
            'is_division' => 1,
            'is_agent' => 0,
            'is_staff' => 0,
            'division_id' => $uid,
            'agent_id' => 0,
            'staff_id' => 0,
            'division_type' => 1,
            'division_status' => $data['division_status'],
            'spread_uid' => 0,
            'spread_time' => 0,
            'division_name' => $data['division_name'],
            'is_promoter' => 1
        ];
        $adminData = [
            'account' => $data['account'],
            'pwd' => $data['pwd'],
            'conf_pwd' => $data['conf_pwd'],
            'real_name' => $data['division_name'],
            'roles' => $data['roles'],
            'status' => 1,
            'level' => 1,
            'division_id' => $uid
        ];
        return $this->transaction(function () use ($uid, $agentData, $adminData, $aid, $userServices) {
            $agentData['division_invite'] = $userServices->value(['uid' => $uid], 'division_invite') ?: rand(10000000, 99999999);
            $userServices->update($uid, $agentData);

            /** @var SystemAdminServices $adminService */
            $adminService = app()->make(SystemAdminServices::class);
            if (!$aid) {
                if ($adminData['pwd']) {
                    if (!$adminData['conf_pwd']) throw new AdminException(400263);
                    if ($adminData['pwd'] != $adminData['conf_pwd']) throw new AdminException(400264);
                    $adminService->create($adminData);
                } else {
                    throw new AdminException(400263);
                }
            } else {
                $adminInfo = $adminService->get($aid);
                if (!$adminInfo)
                    throw new AdminException(400451);
                if ($adminInfo->is_del) {
                    throw new AdminException(400452);
                }
                if (!$adminData['real_name'])
                    throw new AdminException(400453);
                if ($adminData['pwd']) {
                    if (!$adminData['conf_pwd']) throw new AdminException(400263);
                    if ($adminData['pwd'] != $adminData['conf_pwd']) throw new AdminException(400264);
                    $adminInfo->pwd = $this->passwordHash($adminData['pwd']);
                }
                $adminInfo->real_name = $adminData['real_name'];
                $adminInfo->account = $adminData['account'];
                $adminInfo->roles = implode(',', $adminData['roles']);
                if ($adminInfo->save())
                    return true;
                else
                    return false;
            }
            return true;
        });
    }

//    /**
//     * 生成邀请码
//     * @return false|string
//     */
//    public function getDivisionInvite()
//    {
//        /** @var UserServices $userServices */
//        $userServices = app()->make(UserServices::class);
//        list($msec, $sec) = explode(' ', microtime());
//        $num = time() + mt_rand(10, 999999) . '' . substr($msec, 2, 3);//生成随机数
//        if (strlen($num) < 12)
//            $num = str_pad((string)$num, 8, 0, STR_PAD_RIGHT);
//        else
//            $num = substr($num, 0, 8);
//        if ($userServices->count(['division_invite' => $num])) {
//            return $this->getDivisionInvite();
//        }
//        return $num;
//    }

    /**
     * 添加编辑代理商
     * @param $uid
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function getDivisionAgentForm($uid)
    {
        /** @var UserServices $userService */
        $userService = app()->make(UserServices::class);
        $userInfo = $userService->get($uid);
        if ($uid && !$userInfo) throw new AdminException(400214);
        $field = [];
        $options = [];
        $divisionList = $userService->getDivisionList(['status' => 1, 'division_type' => 1], 'uid,division_name');
        foreach ($divisionList['list'] as $item) {
            $options[] = ['value' => $item['uid'], 'label' => $item['division_name']];
        }
        $field[] = Form::input('division_name', '代理商名称', $userInfo['division_name'] ?? '')->required('请输入代理商名称');
        if ($uid) {
            $field[] = Form::hidden('uid', $uid);
            $field[] = Form::hidden('edit', 1);
            $field[] = Form::hidden('division_id', $userInfo['division_id']);
        } else {
            $field[] = Form::select('division_id', '上级事业部', '')->setOptions(Form::setOptions($options))->filterable(1);
            $field[] = Form::frameImage('image', '关联用户', $this->url(config('app.admin_prefix', 'admin') . '/system.user/list', ['fodder' => 'image'], true))->icon('el-icon-user')->width('950px')->height('560px')->Props(['srcKey' => 'image', 'footer' => false]);
            $field[] = Form::hidden('edit', 0);
        }
        $field[] = Form::number('division_percent', '佣金比例', $userInfo['division_percent'] ?? '')->placeholder('代理商佣金比例1-100')->info('填写1-100，如填写50代表返佣50%,但是不能高于上级事业部的比例')->style(['width' => '173px'])->min(0)->max(100)->required();
        $field[] = Form::date('division_end_time', '到期时间', ($userInfo['division_end_time'] ?? '') != 0 ? date('Y-m-d H:i:s', $userInfo['division_end_time']) : '')->placeholder('代理商代理到期时间');
        $field[] = Form::radio('division_status', '代理状态', $userInfo['division_status'] ?? 1)->options([['label' => '开通', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        return create_form('代理商', $field, Route::buildUrl('/agent/division/agent/save'), 'POST');
    }

    /**
     * 保存代理商
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function divisionAgentSave($data)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $uid = $data['uid'];
        $agentData = [
            'spread_uid' => $data['division_id'],
            'spread_time' => time(),
            'division_id' => $data['division_id'],
            'division_status' => $data['division_status'],
            'division_percent' => $data['division_percent'],
            'division_change_time' => time(),
            'division_end_time' => strtotime($data['division_end_time']),
            'division_type' => 2,
            'is_agent' => 1,
            'agent_id' => $uid,
            'is_staff' => 0,
            'staff_id' => 0,
            'division_name' => $data['division_name'],
            'is_promoter' => 1
        ];
        $division_info = $userServices->getUserInfo($data['division_id'], 'division_end_time,division_percent');
        if ($division_info) {
            if ($agentData['division_percent'] > $division_info['division_percent']) throw new AdminException(400448);
            if ($agentData['division_end_time'] > $division_info['division_end_time']) throw new AdminException(400449);
        }
        $res = $userServices->update($uid, $agentData);
        if ($res) return true;
        throw new AdminException('保存失败');
    }

    /**
     * 修改状态
     * @param $status
     * @param $uid
     * @return bool
     */
    public function setDivisionStatus($status, $uid)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        /** @var SystemAdminServices $adminServices */
        $adminServices = app()->make(SystemAdminServices::class);
        $res = $userServices->update($uid, ['division_status' => $status]);
        $res = $res && $adminServices->update(['division_id' => $uid], ['status' => $status]);
        if ($res) {
            return true;
        } else {
            throw new AdminException(100005);
        }
    }

    /**
     * 删除事业部/代理商
     * @param $type
     * @param $uid
     * @return mixed
     */
    public function delDivision($type, $uid)
    {
        return $this->transaction(function () use ($type, $uid) {
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $userInfo = $userServices->getUserInfo($uid);
            if (!$userInfo) throw new AdminException('用户不存在');
            $userInfo = $userInfo->toArray();
            $data = [
                'division_name' => '',
                'division_type' => 0,
                'division_status' => 0,
                'is_division' => 0,
                'is_agent' => 0,
                'is_staff' => 0,
                'division_id' => 0,
                'agent_id' => 0,
                'staff_id' => 0,
                'division_percent' => 0,
                'division_end_time' => 0,
                'division_change_time' => time(),
                'division_invite' => 0
            ];
            $userServices->update(['uid' => $uid], $data);
            if ($userInfo['division_type'] == 1) {
                app()->make(SystemAdminServices::class)->delete(['division_id' => $uid]);
                $userServices->update(['division_id' => $uid], $data);
            } elseif ($userInfo['division_type'] == 2) {
                app()->make(DivisionAgentApplyServices::class)->delete(['uid' => $uid]);
                $userServices->update(['agent_id' => $uid], $data);
            } elseif ($userInfo['division_type'] == 3) {
                $userServices->update(['staff_id' => $uid], $data);
            }
        });
    }

    /**
     * 后台添加员工
     * @param $uid
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2024/1/22
     */
    public function getDivisionStaffForm($uid)
    {
        $field = [];
        $field[] = Form::frameImage('image', '员工', $this->url(config('app.admin_prefix', 'admin') . '/system.user/list', ['fodder' => 'image'], true))->icon('el-icon-user')->width('950px')->height('560px')->Props(['srcKey' => 'image', 'footer' => false]);
        $field[] = Form::number('division_percent', '佣金比例', '')->placeholder('员工佣金比例1-100')->info('填写1-100，如填写50代表返佣50%,但是不能高于上级代理商的比例')->style(['width' => '173px'])->min(0)->max(100)->required();
        $field[] = Form::hidden('agent_id', $uid);
        return create_form('员工', $field, Route::buildUrl('/agent/division/staff/save'), 'POST');
    }

    /**
     * 保存员工
     * @param $data
     * @return true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2024/1/22
     */
    public function divisionStaffSave($data)
    {
        $data['uid'] = $data['image']['uid'];
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->getUserInfo($data['uid'], 'is_division,is_agent,is_staff,division_id,agent_id,staff_id,division_end_time,division_percent');
        if (!$userInfo) throw new AdminException('用户不存在');
        if ($userInfo['is_division']) throw new AdminException('此用户是事业部，无法绑定为员工');
        if ($userInfo['is_agent']) throw new AdminException('此用户是代理商，无法绑定为员工');
        if ($userInfo['is_staff'] && $userInfo['agent_id'] == $data['agent_id']) throw new AdminException('此用户是您的员工，请勿重复添加');
        $agentInfo = $userServices->getUserInfo($data['agent_id'], 'division_id,agent_id,division_end_time,division_percent');
        $staffData = [
            'spread_uid' => $data['agent_id'],
            'spread_time' => time(),
            'division_type' => 3,
            'division_status' => 1,
            'is_staff' => 1,
            'division_id' => $agentInfo['division_id'],
            'agent_id' => $agentInfo['agent_id'],
            'staff_id' => $data['uid'],
            'division_percent' => $data['division_percent'],
            'division_change_time' => time(),
            'division_end_time' => $agentInfo['division_end_time'],
            'is_promoter' => 1
        ];
        if ($staffData['division_percent'] > $agentInfo['division_percent']) throw new AdminException(400448);
        if ($userInfo['agent_id'] != 0 && $userInfo['agent_id'] != $agentInfo['agent_id']) {
            $userServices->update(['staff_id' => $userInfo['uid'], 'spread_uid' => $userInfo['uid']], ['spread_uid' => $agentInfo['agent_id'], 'staff_id' => 0]);
            $userServices->update(['staff_id' => $userInfo['uid'], 'not_spread_uid' => $userInfo['uid']], ['staff_id' => 0]);
        }
        $res = $userServices->update($data['uid'], $staffData);
        if ($res) return true;
        throw new AdminException('保存失败');
    }

    /**
     * 扫码绑定员工
     * @param $uid
     * @param int $agentId
     * @param int $agentCode
     * @return string
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2024/2/2
     */
    public function agentSpreadStaff($uid, int $agentId = 0, int $agentCode = 0)
    {
        if ($agentCode && !$agentId) {
            /** @var QrcodeServices $qrCode */
            $qrCode = app()->make(QrcodeServices::class);
            if ($info = $qrCode->getOne(['id' => $agentCode, 'third_type' => 'agent', 'status' => 1])) {
                $agentId = $info['third_id'];
            }
        }
        if (!$agentId) return false;
        if ($uid == $agentId) return '自己不能推荐自己';
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $agentInfo = $userServices->getUserInfo($agentId, 'division_id,agent_id,division_end_time,division_percent');
        if (!$agentInfo) return '上级用户不存在';
        $userInfo = $userServices->getUserInfo($uid, 'is_division,is_agent,is_staff,division_id,agent_id,staff_id,division_end_time,division_percent');
        if (!$userInfo) return '用户不存在';
        if ($userInfo['is_division']) return '您是事业部,不能绑定成为别人的员工';
        if ($userInfo['is_agent']) return '您是代理商,不能绑定成为别人的员工';
        $staffData = [
            'spread_uid' => $agentId,
            'spread_time' => time(),
            'division_type' => 3,
            'division_status' => 1,
            'is_staff' => 1,
            'division_id' => $agentInfo['division_id'],
            'agent_id' => $agentInfo['agent_id'],
            'staff_id' => $uid,
            'division_change_time' => time(),
            'is_promoter' => 1
        ];
        if ($userInfo['agent_id'] != 0 && $userInfo['agent_id'] != $agentInfo['agent_id']) {
            $userServices->update(['staff_id' => $userInfo['uid'], 'spread_uid' => $userInfo['uid']], ['spread_uid' => $agentInfo['agent_id'], 'staff_id' => 0]);
            $userServices->update(['staff_id' => $userInfo['uid'], 'not_spread_uid' => $userInfo['uid']], ['staff_id' => 0]);
        }
        $res = $userServices->update($uid, $staffData);
        if ($res) return '绑定员工成功';
        return '绑定员工失败';
    }

    /**
     * 获取返佣比例佣金比例
     * 当前方法会将获得的佣金逐步的递减
     * @param $uid
     * @param $storeBrokerageRatio
     * @param $storeBrokerageRatioTwo
     * @param $isSelfBrokerage
     * @return array
     */
    public function getDivisionPercent($uid, $storeBrokerageRatio, $storeBrokerageRatioTwo, $isSelfBrokerage)
    {
        $division_open = (int)sys_config('division_status', 1);
        if (!$division_open) {
            /** 代理商关闭 */
            $storeBrokerageOne = $storeBrokerageRatio;
            $storeBrokerageTwo = $storeBrokerageRatioTwo;
            $staffPercent = 0;
            $agentPercent = 0;
            $divisionPercent = 0;
        } else {
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $userInfo = $userServices->get($uid);
            if ($userInfo['is_division'] == 1) {
                /** 自己是事业部 */
                $storeBrokerageOne = 0;
                $storeBrokerageTwo = 0;
                $staffPercent = 0;
                $agentPercent = 0;
                if ($userInfo['division_status'] == 1 && $userInfo['division_end_time'] > time()) {
                    $divisionPercent = $isSelfBrokerage ? $userInfo['division_percent'] : 0;
                } else {
                    $divisionPercent = 0;
                }
            } elseif ($userInfo['is_agent'] == 1) {
                /** 自己是代理商 */
                $divisionInfo = $userServices->get($userInfo['division_id']);
                $storeBrokerageOne = 0;
                $storeBrokerageTwo = 0;
                $staffPercent = 0;
                if ($userInfo['division_status'] == 1 && $userInfo['division_end_time'] > time()) {
                    $agentPercent = $isSelfBrokerage ? $userInfo['division_percent'] : 0;
                } else {
                    $agentPercent = 0;
                }
                if ($divisionInfo['division_status'] == 1 && $divisionInfo['division_end_time'] > time()) {
                    $divisionPercent = bcsub($divisionInfo['division_percent'], $agentPercent, 2);
                    $divisionPercent = $divisionPercent < 0 ? 0 : $divisionPercent;
                } else {
                    $divisionPercent = 0;
                }
            } elseif ($userInfo['is_staff'] == 1) { // 自己是员工
                /** 自己是员工 */
                $agentInfo = $userServices->get($userInfo['agent_id']);
                $divisionInfo = $userServices->get($userInfo['division_id']);
                $storeBrokerageOne = 0;
                $storeBrokerageTwo = 0;
                if ($userInfo['division_status'] == 1 && $userInfo['division_end_time'] > time()) {
                    $staffPercent = $isSelfBrokerage ? $userInfo['division_percent'] : 0;
                } else {
                    $staffPercent = 0;
                }
                if ($agentInfo['division_status'] == 1 && $agentInfo['division_end_time'] > time()) {
                    $agentPercent = bcsub($agentInfo['division_percent'], $staffPercent, 2);
                    $agentPercent = $agentPercent < 0 ? 0 : $agentPercent;
                } else {
                    $agentPercent = 0;
                }
                if ($divisionInfo['division_status'] == 1 && $divisionInfo['division_end_time'] > time()) {
                    $divisionPercent = bcsub($divisionInfo['division_percent'], bcadd($staffPercent, $agentPercent, 2), 2);
                    $divisionPercent = $divisionPercent < 0 ? 0 : $divisionPercent;
                } else {
                    $divisionPercent = 0;
                }
            } else {
                /** 自己是普通用户 */
                $staffInfo = $userServices->get($userInfo['staff_id']);
                $agentInfo = $userServices->get($userInfo['agent_id']);
                $divisionInfo = $userServices->get($userInfo['division_id']);
                if ($userInfo['staff_id']) {
                    /** 该用户为员工推广 */
                    if ($userInfo['staff_id'] == $userInfo['spread_uid']) {
                        /** 员工直接下级 */
                        $storeBrokerageOne = $isSelfBrokerage ? $storeBrokerageRatio : 0;
                        $storeBrokerageTwo = 0;
                        if ($staffInfo['division_status'] == 1 && $staffInfo['division_end_time'] > time()) {
                            $staffPercent = bcsub($staffInfo['division_percent'], $storeBrokerageOne, 2);
                            $staffPercent = $staffPercent < 0 ? 0 : $staffPercent;
                        } else {
                            $staffPercent = 0;
                        }
                        if ($agentInfo['division_status'] == 1 && $agentInfo['division_end_time'] > time()) {
                            $agentPercent = bcsub($agentInfo['division_percent'], bcadd($storeBrokerageOne, $staffPercent, 2), 2);
                            $agentPercent = $agentPercent < 0 ? 0 : $agentPercent;
                        } else {
                            $agentPercent = 0;
                        }
                        if ($divisionInfo['division_status'] == 1 && $divisionInfo['division_end_time'] > time()) {
                            $divisionPercent = bcsub($divisionInfo['division_percent'], bcadd(bcadd($storeBrokerageOne, $staffPercent, 2), $agentPercent, 2), 2);
                            $divisionPercent = $divisionPercent < 0 ? 0 : $divisionPercent;
                        } else {
                            $divisionPercent = 0;
                        }
                    } else {
                        $storeBrokerageOne = $storeBrokerageRatio;
                        $storeBrokerageTwo = $userServices->value(['uid' => $userInfo['spread_uid']], 'spread_uid') == $userInfo['staff_id'] && !$isSelfBrokerage ? 0 : $storeBrokerageRatioTwo;
                        $brokerageOneTwo = bcadd($storeBrokerageOne, $storeBrokerageTwo, 2);
                        if ($staffInfo['division_status'] == 1 && $staffInfo['division_end_time'] > time()) {
                            $staffPercent = bcsub($staffInfo['division_percent'], $brokerageOneTwo, 2);
                            $staffPercent = $staffPercent < 0 ? 0 : $staffPercent;
                        } else {
                            $staffPercent = 0;
                        }
                        if ($agentInfo['division_status'] == 1 && $agentInfo['division_end_time'] > time()) {
                            $agentPercent = bcsub($agentInfo['division_percent'], bcadd($brokerageOneTwo, $staffPercent, 2), 2);
                            $agentPercent = $agentPercent < 0 ? 0 : $agentPercent;
                        } else {
                            $agentPercent = 0;
                        }
                        if ($divisionInfo['division_status'] == 1 && $divisionInfo['division_end_time'] > time()) {
                            $divisionPercent = bcsub($divisionInfo['division_percent'], bcadd(bcadd($brokerageOneTwo, $staffPercent, 2), $agentPercent, 2), 2);
                            $divisionPercent = $divisionPercent < 0 ? 0 : $divisionPercent;
                        } else {
                            $divisionPercent = 0;
                        }
                    }
                } elseif ($userInfo['agent_id']) {
                    /** 该用户为代理商推广 */
                    if ($userInfo['agent_id'] == $userInfo['spread_uid']) {
                        $storeBrokerageOne = $isSelfBrokerage ? $storeBrokerageRatio : 0;
                        $storeBrokerageTwo = 0;
                        $staffPercent = 0;
                        if ($agentInfo['division_status'] == 1 && $agentInfo['division_end_time'] > time()) {
                            $agentPercent = bcsub($agentInfo['division_percent'], $storeBrokerageOne, 2);
                            $agentPercent = $agentPercent < 0 ? 0 : $agentPercent;
                        } else {
                            $agentPercent = 0;
                        }
                        if ($divisionInfo['division_status'] == 1 && $divisionInfo['division_end_time'] > time()) {
                            $divisionPercent = bcsub($divisionInfo['division_percent'], bcadd($storeBrokerageOne, $agentPercent, 2), 2);
                            $divisionPercent = $divisionPercent < 0 ? 0 : $divisionPercent;
                        } else {
                            $divisionPercent = 0;
                        }
                    } else {
                        $storeBrokerageOne = $storeBrokerageRatio;
                        $storeBrokerageTwo = $userServices->value(['uid' => $userInfo['spread_uid']], 'spread_uid') == $userInfo['agent_id'] && !$isSelfBrokerage ? 0 : $storeBrokerageRatioTwo;
                        $brokerageOneTwo = bcadd($storeBrokerageOne, $storeBrokerageTwo, 2);
                        $staffPercent = 0;
                        if ($agentInfo['division_status'] == 1 && $agentInfo['division_end_time'] > time()) {
                            $agentPercent = bcsub($agentInfo['division_percent'], $brokerageOneTwo, 2);
                            $agentPercent = $agentPercent < 0 ? 0 : $agentPercent;
                        } else {
                            $agentPercent = 0;
                        }
                        if ($divisionInfo['division_status'] == 1 && $divisionInfo['division_end_time'] > time()) {
                            $divisionPercent = bcsub($divisionInfo['division_percent'], bcadd($brokerageOneTwo, $agentPercent, 2), 2);
                            $divisionPercent = $divisionPercent < 0 ? 0 : $divisionPercent;
                        } else {
                            $divisionPercent = 0;
                        }
                    }
                } elseif ($userInfo['division_id']) {
                    /** 该用户为事业部推广 */
                    if ($userInfo['division_id'] == $userInfo['spread_uid']) {
                        /** 事业部直接下级 */
                        $storeBrokerageOne = $isSelfBrokerage ? $storeBrokerageRatio : 0;
                        $storeBrokerageTwo = 0;
                        $staffPercent = 0;
                        $agentPercent = 0;
                        if ($divisionInfo['division_status'] == 1 && $divisionInfo['division_end_time'] > time()) {
                            $divisionPercent = bcsub($divisionInfo['division_percent'], $storeBrokerageOne, 2);
                            $divisionPercent = $divisionPercent < 0 ? 0 : $divisionPercent;
                        } else {
                            $divisionPercent = 0;
                        }
                    } else {
                        $storeBrokerageOne = $storeBrokerageRatio;
                        $storeBrokerageTwo = $userServices->value(['uid' => $userInfo['spread_uid']], 'spread_uid') == $userInfo['division_id'] && !$isSelfBrokerage ? 0 : $storeBrokerageRatioTwo;
                        $brokerageOneTwo = bcadd($storeBrokerageOne, $storeBrokerageTwo, 2);
                        $staffPercent = 0;
                        $agentPercent = 0;
                        if ($divisionInfo['division_status'] == 1 && $divisionInfo['division_end_time'] > time()) {
                            $divisionPercent = bcsub($divisionInfo['division_percent'], $brokerageOneTwo, 2);
                            $divisionPercent = $divisionPercent < 0 ? 0 : $divisionPercent;
                        } else {
                            $divisionPercent = 0;
                        }
                    }
                } else {
                    /** 没有任何代理商关系 */
                    $storeBrokerageOne = $storeBrokerageRatio;
                    $storeBrokerageTwo = $storeBrokerageRatioTwo;
                    $staffPercent = 0;
                    $agentPercent = 0;
                    $divisionPercent = 0;
                }
            }
        }
        return [max($storeBrokerageOne, 0), max($storeBrokerageTwo, 0), max($staffPercent, 0), max($agentPercent, 0), max($divisionPercent, 0)];
    }

    /**
     * 事业部统计
     * @param $type
     * @param $time
     * @param $page
     * @param $limit
     * @param $sort
     * @param $order
     * @return mixed
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2025/4/8
     */
    public function divisionStatistics($type, $time, $page, $limit, $sort, $order)
    {
        switch ($type) {
            case 1:
                $field = 'division_id';
                break;
            case 2:
                $field = 'agent_id';
                break;
            case 3:
                $field = 'staff_id';
                break;
            default:
                $field = 'division_id';
        }
        $data = app()->make(StoreOrderServices::class)->divisionStatistics($field, $time, $page, $limit, $sort, $order);
        $uids = array_column($data['list'], $field);
        $userInfos = app()->make(UserServices::class)->getColumn(['uid' => $uids], 'uid,nickname,avatar', 'uid');
        foreach ($data['list'] as $key => &$val) {
            $val['uid'] = $val[$field];
            $val['name'] = $userInfos[$val[$field]]['nickname'];
            $val['avatar'] = set_file_url($userInfos[$val[$field]]['avatar']);
        }
        return $data;
    }
}
