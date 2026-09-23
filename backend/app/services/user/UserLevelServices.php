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
declare (strict_types=1);

namespace app\services\user;

use crmeb\basic\BaseService;
use app\dao\user\UserLevelDao;
use app\services\system\SystemUserLevelServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;
use crmeb\utils\FormBuilder as Form;
use think\facade\Route as Url;
use app\model\system\SystemUserTask;

/**
 *
 * Class UserLevelServices
 * @package app\services\user
 * @method getDiscount(int $uid, string $field)
 */
class UserLevelServices extends BaseService
{
    /**
     * 获取会员等级任务列表
     * v8.x 起等级任务表（eb_system_user_task）已随任务体系下线，等级成长由经验值体系驱动，
     * 此处返回与旧版前端约定的空任务结构，保持接口兼容，避免调用已删除的数据表
     * @param int $id 等级 ID
     * @param int $uid 用户 UID
     * @return array
     */
    public function getTaskList($id, $uid)
    {
        return ['task' => [], 'reach_count' => 0];
    }
    /**
     * @var UserLevelDao
     */
    protected $dao;

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
            throw new AdminException('修改用户等级信息失败');
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
                throw new AdminException('用户等级不存在');
            }
        }
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userinfo = $userServices->getUserInfo($uid);
        $oldExp = $userinfo['exp'];
        
        // 使用事务保障数据一致性
        return $this->transaction(function () use (
            $uid, $level_id, $vipinfo, $userinfo, $oldExp, $userServices
        ) {
            // 把之前等级作废
            $this->dao->update(['uid' => $uid], ['status' => 0, 'is_del' => 1]);
            
            // 检查是否购买过
            $uservipinfo = $this->getWhereLevel(['uid' => $uid, 'level_id' => $level_id]);
            $data['mark'] = '尊敬的用户' . $userinfo['nickname'] . '在' . date('Y-m-d H:i:s', time()) . '成为了' . $vipinfo['name'];
            $data['add_time'] = time();
            
            if ($uservipinfo) {
                $data['status'] = 1;
                $data['is_del'] = 0;
                if (!$this->dao->update(['id' => $uservipinfo['id']], $data))
                    throw new AdminException('修改用户等级信息失败');
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
                if (!$this->dao->save($data)) throw new AdminException('保存失败');
            }
            
            // 计算经验变动
            $change_exp = $vipinfo['exp_num'] - $oldExp;
            
            // 使用统一方法处理经验变动（增加或减少）
            if ($change_exp != 0) {
                /** @var UserBillServices $userBillService */
                $userBillService = app()->make(UserBillServices::class);
                
                if ($change_exp > 0) {
                    // 增加经验
                    $userBillService->billExpIncome('system_add_exp', $uid, $change_exp, 0, '管理员调整等级增加经验');
                } else {
                    // 减少经验
                    $userBillService->billExpIncome('system_sub_exp', $uid, abs($change_exp), 0, '管理员调整等级减少经验');
                }
            }
            
            // 更新用户等级（经验由 billExpIncome 自动更新，等级变更通知由 UserExpChangeListener 统一处理）
            if (!$userServices->update(['uid' => $uid], ['level' => $level_id])) 
                throw new AdminException('修改失败');
            
            return true;
        });
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
                throw new AdminException('数据不存在');
            }
            $field[] = Form::hidden('id', $id);
            $msg = '编辑用户等级';
        } else {
            $msg = '添加用户等级';
        }
        $field[] = Form::input('name', '等级名称', isset($vipInfo) ? $vipInfo->name : '')->maxlength(10)->col(24)->required();
        $field[] = Form::number('grade', '等级', isset($vipInfo) ? $vipInfo->grade : 0)->min(0)->precision(0)->required();
        $field[] = Form::number('discount', '享受折扣', isset($vipInfo) ? $vipInfo->discount : 100)->min(0)->max(100)->placeholder('输入折扣数100，代表原价，90代表9折')->required();
        $field[] = Form::number('exp_num', '解锁经验值', isset($vipInfo) ? $vipInfo->exp_num : 0)->min(0)->precision(0)->required();
        $field[] = Form::frameImage('icon', '图标', Url::buildUrl(config('app.admin_prefix', 'admin') . '/widget.images/index', array('fodder' => 'icon')), isset($vipInfo) ? $vipInfo->icon : '')->icon('el-icon-picture-outline')->width('950px')->height('560px')->props(['footer' => false])->required();
        $field[] = Form::frameImage('image', '用户等级背景', Url::buildUrl(config('app.admin_prefix', 'admin') . '/widget.images/index', array('fodder' => 'image')), isset($vipInfo) ? $vipInfo->image : '')->icon('el-icon-picture-outline')->width('950px')->height('560px')->props(['footer' => false])->required();
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
            throw new AdminException('用户等级经验必须大于上一等级设置的经验');
        }
        if ($levelNext && $data['exp_num'] >= $levelNext['exp_num']) {
            throw new AdminException('用户等级经验必须小于下一等级设置的经验');
        }
        //修改
        if ($id) {
            if (($levelOne && $levelOne['id'] != $id) || ($levelThree && $levelThree['id'] != $id)) {
                throw new AdminException('已检测到您设置过的用户等级，此等级不可重复');
            }
            if ($levelTwo && $levelTwo['id'] != $id) {
                throw new AdminException('已检测到您设置过该用户等级经验值，经验值不可重复');
            }
            if (!$systemUserLevel->update($id, $data)) {
                throw new AdminException('修改失败');
            }
            return true;
        } else {
            if ($levelOne || $levelThree) {
                throw new AdminException('已检测到您设置过的用户等级，此等级不可重复');
            }
            if ($levelTwo) {
                throw new AdminException('已检测到您设置过该用户等级经验值，经验值不可重复');
            }
            //新增
            $data['add_time'] = time();
            if (!$systemUserLevel->save($data)) {
                throw new AdminException('添加失败');
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
                throw new AdminException('删除失败');
        }
        return '删除成功';
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
            throw new AdminException('数据不存在');
        if ($systemUserLevel->update($id, ['is_show' => $is_show])) {
            return '设置成功';
        } else {
            throw new AdminException('设置失败');
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
            throw new AdminException('数据不存在');
        if ($systemUserLevel->update($id, [$data['field'] => $data['value']])) {
            return true;
        } else {
            throw new AdminException('保存失败');
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
            throw new ApiException('没有此用户，无法检测升级用户等级');
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
                    throw new ApiException('检测升级失败');
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
                    throw new ApiException('检测升级失败');
                }
            }
            $data['add_time'] += 1;
        }
        if (!$userServices->update($uid, ['level' => end($userAllLevel)['id']], 'uid')) {
            throw new ApiException('检测升级失败');
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
            throw new ApiException('没有此用户，无法检测升级用户等级');
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
            throw new ApiException('用户不存在');
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
        //通过事件代理查询签到累计天数，避免 app 层直接引用 modules
        $results = event('MarketingServiceProxy', ['module' => 'signin', 'service' => 'UserSignServices', 'action' => 'getSignSumDay', 'args' => [$uid]]);
        $task['sign_count'] = proxy_event_result($results, 0);
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
            throw new ApiException('用户不存在');
        }
        /** @var UserBillServices $userBill */
        $userBill = app()->make(UserBillServices::class);
        $data = $userBill->getExpList($uid, [], 'id,title,number,pm,add_time');
        $list = $data['list'] ?? [];
        return $list;
    }
}
