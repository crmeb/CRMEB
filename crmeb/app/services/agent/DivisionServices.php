<?php

namespace app\services\agent;

use app\services\BaseServices;
use app\services\system\admin\SystemAdminServices;
use app\services\system\admin\SystemRoleServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
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
        $data = $userServices->getDivisionList($where + ['status' => 1], 'uid,nickname,avatar,division_percent,division_end_time,division_status,division_invite');
        foreach ($data['list'] as &$item) {
            $item['division_end_time'] = date('Y-m-d', $item['division_end_time']);
            $item['agent_count'] = $userServices->count([
                $where['division_type'] == 1 ? 'division_id' : 'agent_id' => $item['uid'],
                'division_type' => $where['division_type'] + 1,
                'status' => 1
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
        $data = $userServices->getDivisionList($where + ['status' => 1], 'uid,nickname,avatar,division_percent,division_end_time,division_status');
        foreach ($data['list'] as &$item) {
            $item['division_end_time'] = date('Y-m-d', $item['division_end_time']);
            $item['agent_count'] = $userServices->count([
                $type == 2 ? 'division_id' : 'agent_id' => $item['uid'],
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
        if ($uid) {
            $field[] = Form::number('uid', '用户UID', $userInfo['uid'])->disabled(true)->style(['width' => '173px']);
        } else {
            $field[] = Form::number('uid', '用户UID')->required('请填写用户UID')->style(['width' => '173px']);
        }
        $field[] = Form::hidden('aid', $adminInfo['id'] ?? 0);
        $field[] = Form::number('division_percent', '佣金比例', $userInfo['division_percent'] ?? '')->placeholder('区域代理佣金比例1-100')->info('填写1-100，如填写50代表返佣50%')->style(['width' => '173px'])->min(0)->max(100)->required();
        $field[] = Form::date('division_end_time', '到期时间', ($userInfo['division_end_time'] ?? '') != 0 ? date('Y-m-d H:i:s', $userInfo['division_end_time']) : '')->placeholder('区域代理到期时间')->required();
        $field[] = Form::radio('division_status', '代理状态', $userInfo['division_status'] ?? 1)->options([['label' => '开通', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        $field[] = Form::input('account', '管理账号', $adminInfo['account'] ?? '')->required('请填写管理员账号');
        $field[] = Form::input('pwd', '管理密码')->type('password')->placeholder('请填写管理员密码');
        $field[] = Form::input('conf_pwd', '确认密码')->type('password')->placeholder('请输入确认密码');
        $field[] = Form::input('real_name', '区域代理姓名', $adminInfo['real_name'] ?? '')->required('请输入管理员姓名');
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
        if ((int)$data['uid'] == 0) throw new AdminException(400450);
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
            'spread_time' => 0
        ];
        $adminData = [
            'account' => $data['account'],
            'pwd' => $data['pwd'],
            'conf_pwd' => $data['conf_pwd'],
            'real_name' => $data['real_name'],
            'roles' => $data['roles'],
            'status' => 1,
            'level' => 1,
            'division_id' => $uid
        ];
        return $this->transaction(function () use ($uid, $agentData, $adminData, $aid) {
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
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
        if ($uid) {
            $field[] = Form::number('uid', '用户UID', $userInfo['uid'] ?? '')->disabled(true)->style(['width' => '173px']);
            $field[] = Form::hidden('edit', 1);
        } else {
            $field[] = Form::number('uid', '用户UID')->style(['width' => '173px']);
            $field[] = Form::hidden('edit', 0);
        }
        $field[] = Form::number('division_percent', '佣金比例', $userInfo['division_percent'] ?? '')->placeholder('代理商佣金比例1-100')->info('填写1-100，如填写50代表返佣50%,但是不能高于上级事业部的比例')->style(['width' => '173px'])->min(0)->max(100)->required();
        $field[] = Form::date('division_end_time', '到期时间', ($userInfo['division_end_time'] ?? '') != 0 ? date('Y-m-d H:i:s', $userInfo['division_end_time']) : '')->placeholder('代理商代理到期时间')->required();
        $field[] = Form::radio('division_status', '代理状态', $userInfo['division_status'] ?? 1)->options([['label' => '开通', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        return create_form('代理商', $field, Route::buildUrl('/agent/division/agent/save'), 'POST');
    }

    /**
     * 保存代理商
     * @param $data
     * @return bool
     */
    public function divisionAgentSave($data)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $uid = $data['uid'];
        $userInfo = $userServices->getUserInfo($uid, 'is_division,division_id,agent_id');
        $agentData = [
            'division_status' => $data['division_status'],
            'division_percent' => $data['division_percent'],
            'division_change_time' => time(),
            'division_end_time' => strtotime($data['division_end_time']),
        ];
        $division_info = $userServices->getUserInfo($userInfo['division_id'], 'division_end_time,division_percent');
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
            switch ($type) {
                case 1:
                    /** @var SystemAdminServices $adminService */
                    $adminService = app()->make(SystemAdminServices::class);
                    $adminService->delete(['division_id' => $uid]);
                    break;
            }
            $data = [
                'division_type' => 0,
                'division_status' => 0,
                'is_division' => 0,
                'division_id' => 0,
                'is_agent' => 0,
                'agent_id' => 0,
                'is_staff' => 0,
                'staff_id' => 0,
                'division_change_time' => time()
            ];
            $userServices->update($uid, $data);

            //删除申请代理商记录
            /** @var DivisionAgentApplyServices $divisionApply */
            $divisionApply = app()->make(DivisionAgentApplyServices::class);
            $divisionApply->update(['uid' => $uid], ['is_del' => 1]);
        });
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
            if ($userInfo['is_division'] == 1 && $userInfo['division_end_time'] > time()) {
                /** 自己是事业部 */
                if ($isSelfBrokerage) {
                    $storeBrokerageOne = $storeBrokerageRatio;
                    $storeBrokerageTwo = 0;
                    $staffPercent = 0;
                    $agentPercent = 0;
                    $divisionPercent = 0;
                } else {
                    $storeBrokerageOne = 0;
                    $storeBrokerageTwo = 0;
                    $staffPercent = 0;
                    $agentPercent = 0;
                    $divisionPercent = 0;
                }
            } elseif ($userInfo['is_agent'] == 1 && $userInfo['division_end_time'] > time()) {
                /** 自己是代理商 */
                $divisionInfo = $userServices->get($userInfo['division_id']);
                if ($isSelfBrokerage) {
                    $storeBrokerageOne = $storeBrokerageRatio;
                    $storeBrokerageTwo = 0;
                    $staffPercent = 0;
                    $agentPercent = 0;
                    $divisionPercent = $divisionInfo['division_percent'] - $storeBrokerageOne;
                } else {
                    $storeBrokerageOne = 0;
                    $storeBrokerageTwo = 0;
                    $staffPercent = 0;
                    $agentPercent = 0;
                    $divisionPercent = $divisionInfo['division_percent'];
                }
            } elseif ($userInfo['is_staff'] == 1 && $userInfo['division_end_time'] > time()) { // 自己是员工
                /** 自己是员工 */
                $agentInfo = $userServices->get($userInfo['agent_id']);
                $divisionInfo = $userServices->get($userInfo['division_id']);
                if ($isSelfBrokerage) {
                    $storeBrokerageOne = $storeBrokerageRatio;
                    $storeBrokerageTwo = 0;
                    $staffPercent = 0;
                    $agentPercent = $agentInfo['division_percent'] - $storeBrokerageOne;
                    $divisionPercent = $divisionInfo['division_percent'] - $agentInfo['division_percent'];
                } else {
                    $storeBrokerageOne = 0;
                    $storeBrokerageTwo = 0;
                    $staffPercent = 0;
                    $agentPercent = $agentInfo['division_percent'];
                    $divisionPercent = $divisionInfo['division_percent'] - $agentInfo['division_percent'];
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
                        if ($isSelfBrokerage) {
                            $storeBrokerageOne = $storeBrokerageRatio;
                            $storeBrokerageTwo = $staffInfo['division_percent'] - $storeBrokerageOne;
                            $staffPercent = 0;
                            $agentPercent = $agentInfo['division_percent'] - $staffInfo['division_percent'];
                            $divisionPercent = $divisionInfo['division_percent'] - $agentInfo['division_percent'];
                        } else {
                            $storeBrokerageOne = $staffInfo['division_percent'];
                            $storeBrokerageTwo = 0;
                            $staffPercent = 0;
                            $agentPercent = $agentInfo['division_percent'] - $staffInfo['division_percent'];
                            $divisionPercent = $divisionInfo['division_percent'] - $agentInfo['division_percent'];
                        }
                    } elseif ($userServices->value(['uid' => $userInfo['spread_uid']], 'spread_uid') == $userInfo['staff_id']) {
                        /** 员工间接下级 */
                        if ($isSelfBrokerage) {
                            $storeBrokerageOne = $storeBrokerageRatio;
                            $storeBrokerageTwo = $storeBrokerageRatioTwo;
                            $staffPercent = 0;
                            $agentPercent = $agentInfo['division_percent'] - $storeBrokerageOne - $storeBrokerageTwo;
                            $divisionPercent = $divisionInfo['division_percent'] - $agentInfo['division_percent'];
                        } else {
                            $storeBrokerageOne = $storeBrokerageRatio;
                            $storeBrokerageTwo = 0;
                            $staffPercent = $staffInfo['division_percent'] - $storeBrokerageOne;
                            $agentPercent = $agentInfo['division_percent'] - $staffInfo['division_percent'];
                            $divisionPercent = $divisionInfo['division_percent'] - $agentInfo['division_percent'];
                        }
                    } else {
                        /** 和员工的关系超过两级 */
                        $storeBrokerageOne = $storeBrokerageRatio;
                        $storeBrokerageTwo = $storeBrokerageRatioTwo;
                        $staffPercent = 0;
                        $agentPercent = $agentInfo['division_percent'] - $storeBrokerageOne - $storeBrokerageTwo;
                        $divisionPercent = $divisionInfo['division_percent'] - $agentInfo['division_percent'];
                    }
                } elseif ($userInfo['agent_id']) {
                    /** 该用户为代理商推广 */
                    if ($userInfo['agent_id'] == $userInfo['spread_uid']) {
                        /** 代理商直接下级 */
                        if ($isSelfBrokerage) {
                            $storeBrokerageOne = $storeBrokerageRatio;
                            $storeBrokerageTwo = 0;
                            $staffPercent = 0;
                            $agentPercent = $agentInfo['division_percent'] - $storeBrokerageOne;
                            $divisionPercent = $divisionInfo['division_percent'] - $agentInfo['division_percent'];
                        } else {
                            $storeBrokerageOne = 0;
                            $storeBrokerageTwo = 0;
                            $staffPercent = 0;
                            $agentPercent = $agentInfo['division_percent'];
                            $divisionPercent = $divisionInfo['division_percent'] - $agentInfo['division_percent'];
                        }
                    } elseif ($userServices->value(['uid' => $userInfo['spread_uid']], 'spread_uid') == $userInfo['agent_id']) {
                        /** 代理商间接下级 */
                        if ($isSelfBrokerage) {
                            $storeBrokerageOne = $storeBrokerageRatio;
                            $storeBrokerageTwo = $storeBrokerageRatioTwo;
                            $staffPercent = 0;
                            $agentPercent = $agentInfo['division_percent'] - $storeBrokerageOne - $storeBrokerageTwo;
                            $divisionPercent = $divisionInfo['division_percent'] - $agentInfo['division_percent'];
                        } else {
                            $storeBrokerageOne = $storeBrokerageRatio;
                            $storeBrokerageTwo = 0;
                            $staffPercent = 0;
                            $agentPercent = $agentInfo['division_percent'] - $storeBrokerageOne;
                            $divisionPercent = $divisionInfo['division_percent'] - $agentInfo['division_percent'];
                        }
                    } else {
                        /** 和代理商的关系超过两级 */
                        $storeBrokerageOne = $storeBrokerageRatio;
                        $storeBrokerageTwo = $storeBrokerageRatioTwo;
                        $staffPercent = 0;
                        $agentPercent = $agentInfo['division_percent'] - $storeBrokerageRatio - $storeBrokerageTwo;
                        $divisionPercent = $divisionInfo['division_percent'] - $agentInfo['division_percent'];
                    }
                } elseif ($userInfo['division_id']) {
                    /** 该用户为事业部推广 */
                    if ($userInfo['division_id'] == $userInfo['spread_uid']) {
                        /** 事业部直接下级 */
                        if ($isSelfBrokerage) {
                            $storeBrokerageOne = $storeBrokerageRatio;
                            $storeBrokerageTwo = 0;
                            $staffPercent = 0;
                            $agentPercent = 0;
                            $divisionPercent = $divisionInfo['division_percent'] - $storeBrokerageOne;
                        } else {
                            $storeBrokerageOne = 0;
                            $storeBrokerageTwo = 0;
                            $staffPercent = 0;
                            $agentPercent = 0;
                            $divisionPercent = $divisionInfo['division_percent'];
                        }
                    } elseif ($userServices->value(['uid' => $userInfo['spread_uid']], 'spread_uid') == $userInfo['division_id']) {
                        /** 事业部间接下级 */
                        if ($isSelfBrokerage) {
                            $storeBrokerageOne = $storeBrokerageRatio;
                            $storeBrokerageTwo = $storeBrokerageRatioTwo;
                            $staffPercent = 0;
                            $agentPercent = 0;
                            $divisionPercent = $divisionInfo['division_percent'] - $storeBrokerageOne - $storeBrokerageTwo;
                        } else {
                            $storeBrokerageOne = $storeBrokerageRatio;
                            $storeBrokerageTwo = 0;
                            $staffPercent = 0;
                            $agentPercent = 0;
                            $divisionPercent = $divisionInfo['division_percent'] - $storeBrokerageOne;
                        }
                    } else {
                        /** 和事业部的关系超过两级 */
                        $storeBrokerageOne = $storeBrokerageRatio;
                        $storeBrokerageTwo = $storeBrokerageRatioTwo;
                        $staffPercent = 0;
                        $agentPercent = 0;
                        $divisionPercent = $divisionInfo['division_percent'] - $storeBrokerageOne - $storeBrokerageTwo;
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
        return [max($storeBrokerageOne, 0), max($storeBrokerageTwo, 0),  max($staffPercent, 0), max($agentPercent, 0), max($divisionPercent, 0)];
    }
}
