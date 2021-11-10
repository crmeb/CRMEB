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
declare (strict_types=1);

namespace app\services\user;

use app\services\BaseServices;
use app\dao\user\UserSignDao;
use think\facade\Log;
use think\exception\ValidateException;

/**
 *
 * Class UserSignServices
 * @package app\services\user
 */
class UserSignServices extends BaseServices
{

    /**
     * UserSignServices constructor.
     * @param UserSignDao $dao
     */
    public function __construct(UserSignDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取用户是否签到
     * @param $uid
     * @return bool
     */
    public function getIsSign(int $uid, string $type = 'today')
    {
        return $this->dao->count(['uid' => $uid, 'time' => $type]) ? true : false;
    }

    /**
     * 获取用户累计签到次数
     * @Parma int $uid 用户id
     * @return int
     * */
    public function getSignSumDay(int $uid)
    {
        return $this->dao->count(['uid' => $uid]);
    }

    /**
     * 设置签到数据
     * @param int $uid 用户uid
     * @param string $title 签到说明
     * @param int $number 签到获得积分
     * @param int $balance 签到前剩余积分
     * @return object
     * */
    public function setSignData($uid, $title = '', $number = 0, $integral_balance = 0, $exp_banlance = 0, $exp_num = 0)
    {
        $data = [];
        $data['uid'] = $uid;
        $data['title'] = $title;
        $data['number'] = $number;
        $data['balance'] = $integral_balance;
        $data['add_time'] = time();
        if (!$this->dao->save($data)) {
            throw new ValidateException('添加签到数据失败');
        }
        /** @var UserBillServices $userBill */
        $userBill = app()->make(UserBillServices::class);
        $data['mark'] = $title;
        $userBill->incomeIntegral($uid, 'sign', $data);

        if ($exp_num) {
            $data['number'] = $exp_num;
            $data['category'] = 'exp';
            $data['type'] = 'sign';
            $data['title'] = $data['mark'] = '签到奖励';
            $data['balance'] = $exp_banlance;
            $data['pm'] = 1;
            $data['status'] = 1;
            if (!$userBill->save($data)) {
                throw new ValidateException('赠送经验失败');
            }
            //检测会员等级
            try {
                //用户升级事件
                event('user.userLevel', [$uid]);
            } catch (\Throwable $e) {
                Log::error('会员等级升级失败,失败原因:' . $e->getMessage());
            }
        }
        return true;
    }

    /**
     * 获取用户签到列表
     * @param int $uid
     * @param string $field
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserSignList(int $uid, string $field = '*')
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList(['uid' => $uid], $field, $page, $limit);
        foreach ($list as &$item) {
            $item['add_time'] = $item['add_time'] ? date('Y-m-d', $item['add_time']) : '';
        }
        return $list;
    }

    /**
     * 用户签到
     * @param $uid
     * @return bool|int|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function sign(int $uid)
    {
        $sign_list = \crmeb\services\GroupDataService::getData('sign_day_num') ?: [];
        if (!count($sign_list)) {
            throw new ValidateException('请先配置签到天数');
        }
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $user = $userServices->getUserInfo($uid);
        if (!$user) {
            throw new ValidateException('用户不存在');
        }
        if ($this->getIsSign($uid, 'today')) {
            throw new ValidateException('已经签到');
        }
        $sign_num = 0;
        $user_sign_num = $user['sign_num'];
        //检测昨天是否签到
        if ($this->getIsSign($uid, 'yesterday')) {
            if ($user->sign_num > (count($sign_list) - 1)) {
                $user->sign_num = 0;
            }
        } else {
            $user->sign_num = 0;
        }
        foreach ($sign_list as $key => $item) {
            if ($key == $user->sign_num) {
                $sign_num = $item['sign_num'];
                break;
            }
        }
        //会员签到积分会员奖励
        if ($user->is_money_level > 0) {
            //看是否开启签到积分翻倍奖励
            /** @var MemberCardServices $memberCardService */
            $memberCardService = app()->make(MemberCardServices::class);
            $sign_rule_number = $memberCardService->isOpenMemberCard('sign');
            if ($sign_rule_number) {
                $sign_num = (int)$sign_rule_number * $sign_num;
            }

        }
        $user->sign_num += 1;
        if ($user->sign_num == count($sign_list)) {
            $title = '连续签到奖励';
        } else {
            $title = '签到奖励';
        }
        //用户等级是否开启
        $exp_num = 0;
        if (sys_config('member_func_status', 1)) {
            $exp_num = sys_config('sign_give_exp');
        }
        //增加签到数据
        $this->transaction(function () use ($uid, $title, $sign_num, $user, $exp_num) {
            $this->setSignData($uid, $title, $sign_num, $user['integral'], (int)$user['exp'], $exp_num);
            $user->integral = (int)$user->integral + (int)$sign_num;
            if ($exp_num) $user->exp = (int)$user->exp + (int)$exp_num;
            if (!$user->save()) {
                throw new ValidateException('修改用户信息失败');
            }
        });
        return $sign_num;
    }

    /**
     * 签到用户信息
     * @param int $uid
     * @param $sign
     * @param $integral
     * @param $all
     * @return mixed
     */
    public function signUser(int $uid, $sign, $integral, $all)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $user = $userServices->getUserInfo($uid);
        if (!$user) {
            throw new ValidateException('数据不存在');
        }
        //是否统计签到
        if ($sign || $all) {
            $user['sum_sgin_day'] = $this->getSignSumDay($user['uid']);
            $user['is_day_sgin'] = $this->getIsSign($user['uid']);
            $user['is_YesterDay_sgin'] = $this->getIsSign($user['uid'], 'yesterday');
            if (!$user['is_day_sgin'] && !$user['is_YesterDay_sgin']) {
                $user['sign_num'] = 0;
            }
        }
        //是否统计积分使用情况
        if ($integral || $all) {
            /** @var UserBillServices $userBill */
            $userBill = app()->make(UserBillServices::class);
            $user['sum_integral'] = intval($userBill->getRecordCount($user['uid'], 'integral', 'sign,system_add,gain,lottery_add'));
            $user['deduction_integral'] = intval($userBill->getRecordCount($user['uid'], 'integral', 'deduction,lottery_use', '', true) ?? 0);
            $user['today_integral'] = intval($userBill->getRecordCount($user['uid'], 'integral', 'sign,system_add,gain,lottery_add', 'today'));
        }
        unset($user['pwd']);
        if (!$user['is_promoter']) {
            $user['is_promoter'] = (int)sys_config('store_brokerage_statu') == 2 ? true : false;
        }
        return $user->hidden(['account', 'real_name', 'birthday', 'card_id', 'mark', 'partner_id', 'group_id', 'add_time', 'add_ip', 'phone', 'last_time', 'last_ip', 'spread_uid', 'spread_time', 'user_type', 'status', 'level', 'clean_time', 'addres'])->toArray();
    }


    /**
     * 获取签到
     * @param $uid
     * @return array
     */
    public function getSignMonthList($uid)
    {
        [$page, $limit] = $this->getPageValue();
        $data = $this->dao->getListGroup(['uid' => $uid], 'FROM_UNIXTIME(add_time,"%Y-%m") as time,group_concat(id SEPARATOR ",") ids', $page, $limit, 'time');
        $list = [];
        if ($data) {
            $ids = array_unique(array_column($data, 'ids'));
            $dataIdsList = $this->dao->getList(['id' => $ids], 'FROM_UNIXTIME(add_time,"%Y-%m-%d") as add_time,title,number,id,uid', 0, 0);
            foreach ($data as $item) {
                $value['month'] = $item['time'];
                $value['list'] = array_merge(array_filter($dataIdsList, function ($val) use ($item) {
                    if (in_array($val['id'], explode(',', $item['ids']))) {
                        return $val;
                    }
                }));
                array_push($list, $value);
            }
        }
        return $list;
    }
}
