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
declare (strict_types=1);

namespace app\services\user;

use app\services\BaseServices;
use app\dao\user\UserLevelDao;
use app\services\system\SystemUserLevelServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;

/**
 *
 * Class UserLevelServices
 * @package app\services\user
 * @method getDiscount(int $uid, string $field)
 */
class UserLevelServices extends BaseServices
{

    /**
     * UserLevelServices constructor.
     * @param UserLevelDao $dao
     */
    public function __construct(UserLevelDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 某些条件获取单个
     * @param array $where
     * @param string $field
     * @return mixed
     */
    public function getWhereLevel(array $where, string $field = '*')
    {
        return $this->getOne($where, $field);
    }

    /**
     * 获取一些用户等级信息
     * @param array $uids
     * @param string $field
     * @param string $key
     * @return array
     */
    public function getUsersLevelInfo(array $uids)
    {
        return $this->dao->getColumn([['uid', 'in', $uids]], 'level_id,is_forever,valid_time', 'uid');
    }

    /**
     * 清除用户等级
     * @param $uids
     * @return \crmeb\basic\BaseModel|mixed
     */
    public function delUserLevel($uids)
    {
        $where = [];
        if (is_array($uids)) {
            $where[] = ['uid', 'IN', $uids];
            $re = $this->dao->batchUpdate($uids, ['is_del' => 1, 'status' => 0], 'uid');
        } else {
            $where[] = ['uid', '=', $uids];
            $re = $this->dao->update($uids, ['is_del' => 1, 'status' => 0], 'uid');
        }
        if (!$re)
            throw new AdminException(400671);
        $where[] = ['category', 'IN', ['exp']];
        /** @var UserBillServices $userbillServices */
        $userbillServices = app()->make(UserBillServices::class);
        $userbillServices->update($where, ['status' => -1]);
        return true;
    }

    /**
     * 根据用户uid 获取用户等级详细信息
     * @param int $uid
     * @param string $field
     */
    public function getUerLevelInfoByUid(int $uid, string $field = '')
    {
        $userLevelInfo = $this->dao->getUserLevel($uid);
        $data = [];
        if ($userLevelInfo) {
            $data = ['id' => $userLevelInfo['id'], 'level_id' => $userLevelInfo['level_id'], 'add_time' => $userLevelInfo['add_time']];
            $data['discount'] = $userLevelInfo['levelInfo']['discount'] ?? 0;
            $data['name'] = $userLevelInfo['levelInfo']['name'] ?? '';
            $data['money'] = $userLevelInfo['levelInfo']['money'] ?? 0;
            $data['icon'] = $userLevelInfo['levelInfo']['icon'] ?? '';
            $data['is_pay'] = $userLevelInfo['levelInfo']['is_pay'] ?? 0;
            $data['grade'] = $userLevelInfo['levelInfo']['grade'] ?? 0;
            $data['exp_num'] = $userLevelInfo['levelInfo']['exp_num'] ?? 0;
        }
        if ($field) return $data[$field] ?? '';
        return $data;
    }

    /**
     * 设置用户等级
     * @param $uid 用户uid
     * @param $level_id 等级id
     * @return UserLevel|bool|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function setUserLevel(int $uid, int $level_id, $vipinfo = [])
    {
        /** @var SystemUserLevelServices $systemLevelServices */
        $systemLevelServices = app()->make(SystemUserLevelServices::class);
        if (!$vipinfo) {
            $vipinfo = $systemLevelServices->getLevel($level_id);
            if (!$vipinfo) {
                throw new AdminException(400672);
            }
        }
        /** @var  $user */
        $user = app()->make(UserServices::class);
        $userinfo = $user->getUserInfo($uid);
        //把之前等级作废
        $this->dao->update(['uid' => $uid], ['status' => 0, 'is_del' => 1]);
        //检查是否购买过
        $uservipinfo = $this->getWhereLevel(['uid' => $uid, 'level_id' => $level_id]);
        $data['mark'] = '尊敬的用户' . $userinfo['nickname'] . '在' . date('Y-m-d H:i:s', time()) . '成为了' . $vipinfo['name'];
        $data['add_time'] = time();
        if ($uservipinfo) {
            $data['status'] = 1;
            $data['is_del'] = 0;
            if (!$this->dao->update(['id' => $uservipinfo['id']], $data))
                throw new AdminException(400671);
        } else {
            $data = array_merge($data, [
                'is_forever' => $vipinfo->is_forever,
                'status' => 1,
                'is_del' => 0,
                'grade' => $vipinfo->grade,
                'uid' => $uid,
                'level_id' => $level_id,
                'discount' => $vipinfo->discount,
            ]);
            $data['valid_time'] = 0;
            if (!$this->dao->save($data)) throw new AdminException(100006);
        }
        if ($level_id > $userinfo['level']) {
            $change_exp = $vipinfo['exp_num'] - $userinfo['exp'];
            $pm = 1;
            $type = 'system_exp_add';
            $title = '系统增加经验';
            $mark = '系统增加' . $change_exp . '经验';
        } else {
            $change_exp = $userinfo['exp'] - $vipinfo['exp_num'];
            $pm = 0;
            $type = 'system_exp_sub';
            $title = '系统减少经验';
            $mark = '系统减少' . $change_exp . '经验';
        }
        $bill_data['uid'] = $uid;
        $bill_data['pm'] = $pm;
        $bill_data['title'] = $title;
        $bill_data['category'] = 'exp';
        $bill_data['type'] = $type;
        $bill_data['number'] = $change_exp;
        $bill_data['balance'] = $userinfo['exp'];
        $bill_data['mark'] = $mark;
        $bill_data['status'] = 1;
        $bill_data['add_time'] = time();
        /** @var UserBillServices $userBillService */
        $userBillService = app()->make(UserBillServices::class);
        if (!$userBillService->save($bill_data)) throw new AdminException(100006);
        if (!$user->update(['uid' => $uid], ['level' => $level_id, 'exp' => $vipinfo['exp_num']])) throw new AdminException(100007);
        return true;
    }

    /**
     * 会员列表
     * @param $where
     * @return mixed
     */
    public function getSytemList($where)
    {
        /** @var SystemUserLevelServices $systemLevelServices */
        $systemLevelServices = app()->make(SystemUserLevelServices::class);
        return $systemLevelServices->getLevelList($where);
    }

    /**
     * 获取添加修改需要表单数据
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function edit(int $id)
    {

        if ($id) {
            $vipInfo = app()->make(SystemUserLevelServices::class)->getlevel($id);
            $vipInfo->image = set_file_url($vipInfo->image);
            $vipInfo->icon = set_file_url($vipInfo->icon);
            if (!$vipInfo) {
                throw new AdminException(100026);
            }
            $field[] = Form::hidden('id', $id);
            $msg = '编辑用户等级';
        } else {
            $msg = '添加用户等级';
        }
        $field[] = Form::input('name', '等级名称', isset($vipInfo) ? $vipInfo->name : '')->col(24)->required();
        $field[] = Form::number('grade', '等级', isset($vipInfo) ? $vipInfo->grade : 0)->min(0)->precision(0)->col(8)->required();
        $field[] = Form::number('discount', '享受折扣', isset($vipInfo) ? $vipInfo->discount : 100)->min(0)->max(100)->col(8)->placeholder('输入折扣数100，代表原价，90代表9折')->required();
        $field[] = Form::number('exp_num', '解锁需经验值达到', isset($vipInfo) ? $vipInfo->exp_num : 0)->min(0)->precision(0)->col(8)->required();
        $field[] = Form::frameImage('icon', '图标', Url::buildUrl('admin/widget.images/index', array('fodder' => 'icon')), isset($vipInfo) ? $vipInfo->icon : '')->icon('ios-add')->width('950px')->height('505px')->modal(['footer-hide' => true]);
        $field[] = Form::frameImage('image', '用户等级背景', Url::buildUrl('admin/widget.images/index', array('fodder' => 'image')), isset($vipInfo) ? $vipInfo->image : '')->icon('ios-add')->width('950px')->height('505px')->modal(['footer-hide' => true]);
        $field[] = Form::radio('is_show', '是否显示', isset($vipInfo) ? $vipInfo->is_show : 0)->options([['label' => '显示', 'value' => 1], ['label' => '隐藏', 'value' => 0]])->col(24);
        return create_form($msg, $field, Url::buildUrl('/user/user_level'), 'POST');
    }

    /*
     * 会员等级添加或者修改
     * @param $id 修改的等级id
     * @return json
     * */
    public function save(int $id, array $data)
    {
        /** @var SystemUserLevelServices $systemUserLevel */
        $systemUserLevel = app()->make(SystemUserLevelServices::class);
        $levelOne = $systemUserLevel->getWhereLevel(['is_del' => 0, 'grade' => $data['grade']]);
        $levelTwo = $systemUserLevel->getWhereLevel(['is_del' => 0, 'exp_num' => $data['exp_num']]);
        $levelThree = $systemUserLevel->getWhereLevel(['is_del' => 0, 'name' => $data['name']]);
        $levelPre = $systemUserLevel->getPreLevel($data['grade']);
        $levelNext = $systemUserLevel->getNextLevel($data['grade']);
        if ($levelPre && $data['exp_num'] <= $levelPre['exp_num']) {
            throw new AdminException(400673);
        }
        if ($levelNext && $data['exp_num'] >= $levelNext['exp_num']) {
            throw new AdminException(400674);
        }
        //修改
        if ($id) {
            if (($levelOne && $levelOne['id'] != $id) || ($levelThree && $levelThree['id'] != $id)) {
                throw new AdminException(400675);
            }
            if ($levelTwo && $levelTwo['id'] != $id) {
                throw new AdminException(400676);
            }
            if (!$systemUserLevel->update($id, $data)) {
                throw new AdminException(100007);
            }
            return true;
        } else {
            if ($levelOne || $levelThree) {
                throw new AdminException(400675);
            }
            if ($levelTwo) {
                throw new AdminException(400676);
            }
            //新增
            $data['add_time'] = time();
            if (!$systemUserLevel->save($data)) {
                throw new AdminException(100022);
            }
            return true;
        }
    }

    /**
     * 假删除
     * @param int $id
     * @return mixed
     */
    public function delLevel(int $id)
    {
        /** @var SystemUserLevelServices $systemUserLevel */
        $systemUserLevel = app()->make(SystemUserLevelServices::class);
        $level = $systemUserLevel->getWhereLevel(['id' => $id]);
        if ($level && $level['is_del'] != 1) {
            if (!$systemUserLevel->update($id, ['is_del' => 1]))
                throw new AdminException(100008);
        }
        return 100002;
    }

    /**
     * 设置是否显示
     * @param int $id
     * @param $is_show
     * @return mixed
     */
    public function setShow(int $id, int $is_show)
    {
        /** @var SystemUserLevelServices $systemUserLevel */
        $systemUserLevel = app()->make(SystemUserLevelServices::class);
        if (!$systemUserLevel->getWhereLevel(['id' => $id]))
            throw new AdminException(100026);
        if ($systemUserLevel->update($id, ['is_show' => $is_show])) {
            return 100014;
        } else {
            throw new AdminException(100015);
        }
    }

    /**
     * 快速修改
     * @param int $id
     * @param $is_show
     * @return mixed
     */
    public function setValue(int $id, array $data)
    {
        /** @var SystemUserLevelServices $systemUserLevel */
        $systemUserLevel = app()->make(SystemUserLevelServices::class);
        if (!$systemUserLevel->getWhereLevel(['id' => $id]))
            throw new AdminException(100026);
        if ($systemUserLevel->update($id, [$data['field'] => $data['value']])) {
            return true;
        } else {
            throw new AdminException(100006);
        }
    }

    /**
     * 检测用户会员升级
     * @param $uid
     * @return bool
     */
    public function detection(int $uid)
    {
        //商城会员是否开启
        if (!sys_config('member_func_status')) {
            return true;
        }
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $user = $userServices->getUserInfo($uid);
        if (!$user) {
            throw new ApiException(410284);
        }
        /** @var SystemUserLevelServices $systemUserLevel */
        $systemUserLevel = app()->make(SystemUserLevelServices::class);
        $userAllLevel = $systemUserLevel->getList([['is_del', '=', 0], ['is_show', '=', 1], ['exp_num', '<=', (float)$user['exp']]]);
        if (!$userAllLevel) {
            return true;
        }
        $data = [];
        $data['add_time'] = time();
        $userLevel = $this->dao->getColumn(['uid' => $uid, 'status' => 1, 'is_del' => 0], 'level_id');
        foreach ($userAllLevel as $vipinfo) {
            if (in_array($vipinfo['id'], $userLevel)) {
                continue;
            }
            $data['mark'] = '尊敬的用户' . $user['nickname'] . '在' . date('Y-m-d H:i:s', time()) . '成为了' . $vipinfo['name'];
            $uservip = $this->dao->getOne(['uid' => $uid, 'level_id' => $vipinfo['id']]);
            if ($uservip) {
                //降级在升级情况
                $data['status'] = 1;
                $data['is_del'] = 0;
                if (!$this->dao->update($uservip['id'], $data, 'id')) {
                    throw new ApiException(410285);
                }
            } else {
                $data = array_merge($data, [
                    'is_forever' => $vipinfo['is_forever'],
                    'status' => 1,
                    'is_del' => 0,
                    'grade' => $vipinfo['grade'],
                    'uid' => $uid,
                    'level_id' => $vipinfo['id'],
                    'discount' => $vipinfo['discount'],
                ]);
                if (!$this->dao->save($data)) {
                    throw new ApiException(410285);
                }
            }
            $data['add_time'] += 1;
        }
        if (!$userServices->update($uid, ['level' => end($userAllLevel)['id']], 'uid')) {
            throw new ApiException(410285);
        }
        return true;
    }

    /**
     * 会员等级列表
     * @param int $uid
     */
    public function grade(int $uid)
    {
        //商城会员是否开启
        if (!sys_config('member_func_status')) {
            return [];
        }
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $user = $userServices->getUserInfo($uid);
        if (!$user) {
            throw new ApiException(410284);
        }
        $userLevelInfo = $this->getUerLevelInfoByUid($uid);
        if (empty($userLevelInfo)) {
            $level_id = 0;
        } else {
            $level_id = $userLevelInfo['level_id'];
        }
        /** @var SystemUserLevelServices $systemUserLevel */
        $systemUserLevel = app()->make(SystemUserLevelServices::class);
        return $systemUserLevel->getLevelListAndGrade($level_id);
    }

    /**
     * 获取会员信息
     * @param int $uid
     * @return array[]
     */
    public function getUserLevelInfo(int $uid)
    {
        $data = ['user' => [], 'level_info' => [], 'level_list' => [], 'task' => []];
        //商城会员是否开启
        if (!sys_config('member_func_status')) {
            return $data;
        }
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $user = $userServices->getUserInfo($uid);
        if (!$user) {
            throw new ApiException(410032);
        }
        $data['user'] = $user;
        /** @var SystemUserLevelServices $systemUserLevel */
        $systemUserLevel = app()->make(SystemUserLevelServices::class);
        $levelList = $systemUserLevel->getList(['is_del' => 0, 'is_show' => 1]);
        $i = 0;
        foreach ($levelList as &$level) {
            $level['next_exp_num'] = $levelList[$i + 1]['exp_num'] ?? $level['exp_num'];
            $level['image'] = set_file_url($level['image']);
            $level['icon'] = set_file_url($level['icon']);
            $i++;
        }
        $data['level_list'] = $levelList;
        $data['level_info'] = $this->getUerLevelInfoByUid($uid);
        $data['level_info']['exp'] = $user['exp'] ?? 0;
        /** @var UserBillServices $userBillservices */
        $userBillservices = app()->make(UserBillServices::class);
        $data['level_info']['today_exp'] = $userBillservices->getExpSum($uid, 'today');
        $task = [];
        /** @var UserSignServices $userSignServices */
        $userSignServices = app()->make(UserSignServices::class);
        $task['sign_count'] = $userSignServices->getSignSumDay($uid);
        $task['sign'] = sys_config('sign_give_exp', 0);
        $task['order'] = sys_config('order_give_exp', 0);
        $task['invite'] = sys_config('invite_user_exp', 0);
        $data['task'] = $task;
        return $data;
    }

    /**
     * 经验列表
     * @param int $uid
     * @return array
     */
    public function expList(int $uid)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $user = $userServices->getUserInfo($uid);
        if (!$user) {
            throw new ApiException(410032);
        }
        /** @var UserBillServices $userBill */
        $userBill = app()->make(UserBillServices::class);
        $data = $userBill->getExpList($uid, [], 'id,title,number,pm,add_time');
        $list = $data['list'] ?? [];
        return $list;
    }
}
