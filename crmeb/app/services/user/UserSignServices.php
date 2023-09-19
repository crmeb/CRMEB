<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\services\user;

use app\services\BaseServices;
use app\dao\user\UserSignDao;
use app\services\system\SystemSignRewardServices;
use app\services\user\member\MemberCardServices;
use crmeb\exceptions\ApiException;
use think\facade\Log;

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
     * @param int $uid
     * @param string $type
     * @return bool
     * @throws \ReflectionException
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/8
     */
    public function getIsSign(int $uid, string $type = 'today')
    {
        return (bool)$this->dao->count(['uid' => $uid, 'time' => $type]);
    }

    /**
     * 获取用户累计签到次数
     * @param int $uid
     * @return int
     * @throws \ReflectionException
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/8
     */
    public function getSignSumDay(int $uid)
    {
        return $this->dao->count(['uid' => $uid]);
    }

    /**
     * 设置签到数据
     * @param $uid
     * @param string $title
     * @param int $number
     * @param int $integral_balance
     * @param int $exp_banlance
     * @param int $exp_num
     * @return bool
     * @throws \think\Exception
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/8
     */
    public function setSignData($uid, $title = '', $number = 0, $integral_balance = 0, $exp_banlance = 0, $exp_num = 0)
    {
        $data = [];
        $data['uid'] = $uid;
        $data['title'] = $title;
        $data['number'] = $number;
        $data['balance'] = $integral_balance + $number;
        $data['add_time'] = time();
        if (!$this->dao->save($data)) {
            throw new ApiException(410290);
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
            $data['balance'] = $exp_banlance + $exp_num;
            $data['pm'] = 1;
            $data['status'] = 1;
            if (!$userBill->save($data)) {
                throw new ApiException(410291);
            }
            //检测会员等级
            try {
                //用户升级事件
                event('UserLevelListener', [$uid]);
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
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/8
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
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);

        //检测用户是否存在
        $user = $userServices->getUserInfo($uid);
        if (!$user) {
            throw new ApiException(410032);
        }

        //检测今天是否已经签到
        if ($this->getIsSign($uid, 'today')) {
            throw new ApiException(410293);
        }
        $title = '签到奖励';
        //检测昨天是否签到，如果没有签到，连续签到清0
        if (!$this->getIsSign($uid, 'yesterday')) $user->sign_num = 0;

        //获取签到周期配置，如果周签到，每周一清空连续签到记录，如果月签到，每月一日清空连续签到记录
        $sign_mode = sys_config('sign_mode', -1);
        if ($sign_mode == 1 && date('w') == 1) $user->sign_num = 0;
        if ($sign_mode == 0 && date('d') == 1) $user->sign_num = 0;

        //连续签到天数
        $user->sign_num += 1;
        $continuousDays = $user->sign_num;
        //累积签到天数
        $cumulativeDays = $this->dao->getCumulativeDays($sign_mode, $uid);

        //基础签到奖励
        $sign_point = sys_config('sign_give_point', 0);
        $sign_exp = sys_config('member_func_status', 1) ? sys_config('sign_give_exp', 0) : 0;

        //连续签到和累积签到奖励
        $signRewardsServices = app()->make(SystemSignRewardServices::class);
        [$continuousStatus, $continuousRewardPoint, $continuousRewardExp] = $signRewardsServices->getSignRewards(0, $continuousDays);
        [$cumulativeStatus, $cumulativeRewardPoint, $cumulativeRewardExp] = $signRewardsServices->getSignRewards(1, $cumulativeDays);
        if ($continuousStatus && $cumulativeStatus) {
            $sign_point = $continuousRewardPoint + $cumulativeRewardPoint;
            $sign_exp = $continuousRewardExp + $cumulativeRewardExp;
        } elseif ($continuousStatus) {
            $sign_point = $continuousRewardPoint;
            $sign_exp = $continuousRewardExp;
        } elseif ($cumulativeStatus) {
            $sign_point = $cumulativeRewardPoint;
            $sign_exp = $cumulativeRewardExp;
        }

        //会员签到积分会员奖励
        if ($user->is_money_level > 0) {
            //看是否开启签到积分翻倍奖励
            /** @var MemberCardServices $memberCardService */
            $memberCardService = app()->make(MemberCardServices::class);
            $sign_rule_number = $memberCardService->isOpenMemberCard('sign');
            if ($sign_rule_number) {
                $up_num = (int)$sign_rule_number * $sign_point - $sign_point;
                $sign_point = (int)$sign_rule_number * $sign_point;
                if (!$this->getIsSign($uid, 'yesterday')) $title = '签到奖励(SVIP+' . $up_num . ')';
            }
        }

        //增加签到数据
        $this->transaction(function () use ($uid, $title, $sign_point, $user, $sign_exp) {
            $this->setSignData($uid, $title, $sign_point, $user['integral'], (int)$user['exp'], $sign_exp);
            $user->integral = (int)$user->integral + (int)$sign_point;
            if ($sign_exp) $user->exp = (int)$user->exp + (int)$sign_exp;
            if (!$user->save()) {
                throw new ApiException(410287);
            }
        });
        return $sign_point;
    }

    /**
     * 签到用户信息
     * @param int $uid
     * @param $sign
     * @param $integral
     * @param $all
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/8
     */
    public function signUser(int $uid, $sign, $integral, $all)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $user = $userServices->getUserInfo($uid);
        if (!$user) {
            throw new ApiException(100026);
        }
        //是否统计签到
        if ($sign || $all) {
            $user['sum_sgin_day'] = $this->getSignSumDay($user['uid']);
            $user['is_day_sgin'] = false;
            $user['is_YesterDay_sgin'] = $this->getIsSign($user['uid'], 'yesterday');
            if (!$user['is_day_sgin'] && !$user['is_YesterDay_sgin']) {
                $user['sign_num'] = 0;
            }
        }
        //是否统计积分使用情况
        if ($integral || $all) {
            /** @var UserBillServices $userBill */
            $userBill = app()->make(UserBillServices::class);
            $user['sum_integral'] = intval($userBill->getRecordCount($user['uid'], 'integral', 'sign,system_add,gain,lottery_add,product_gain,pay_product_integral_back'));
            $user['deduction_integral'] = intval($userBill->getRecordCount($user['uid'], 'integral', 'deduction,lottery_use,order_deduction', '', true) ?? 0);
            $user['today_integral'] = intval($userBill->getRecordCount($user['uid'], 'integral', 'sign,system_add,gain,product_gain,lottery_add,pay_product_integral_back', 'today'));
            /** @var UserBillServices $userBillServices */
            $userBillServices = app()->make(UserBillServices::class);
            $user['frozen_integral'] = $userBillServices->getBillSum(['uid' => $user['uid'], 'is_frozen' => 1]);
        }
        unset($user['pwd']);
        if (!$user['is_promoter']) {
            $user['is_promoter'] = (int)sys_config('store_brokerage_statu') == 2;
        }
        return $user->hidden(['account', 'real_name', 'birthday', 'card_id', 'mark', 'partner_id', 'group_id', 'add_time', 'add_ip', 'phone', 'last_time', 'last_ip', 'spread_uid', 'spread_time', 'user_type', 'status', 'level', 'clean_time', 'addres'])->toArray();
    }

    /**
     * 获取签到
     * @param $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/8
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

    /**
     * 返回签到列表数据
     * @param $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/8
     */
    public function signConfig($uid)
    {
        //获取周签到还是月签到
        $signMode = (int)sys_config('sign_mode', 1);

        //获取签到列表
        $startDate = $signMode == 1 ? strtotime('this week Monday') : strtotime('first day of this month midnight');
        $endDate = $signMode == 1 ? strtotime('this week Sunday') : strtotime('last day of this month midnight');
        $dateList = range($startDate, $endDate, 86400);

        //获取已经签到的列表
        $list = $this->dao->getUserSignList($signMode, $uid);

        //获取累积签到和连续签到
        $cumulativeSignDays = $this->dao->getCumulativeDays($signMode, $uid);
        $continuousSignDays = app()->make(UserServices::class)->value($uid, 'sign_num');

        //获取累积签到和连续签到奖励
        $nextCumulativeSignRewardList = app()->make(SystemSignRewardServices::class)->selectList(['type' => 1], '*', 1, 200, 'days asc')->toArray();
        $nextContinuousSignRewardList = app()->make(SystemSignRewardServices::class)->selectList(['type' => 0], '*', 1, 200, 'days asc')->toArray();

        //下一次连续签到奖励还需签到几天
        $nextContinuousDays = 0;
        foreach ($nextContinuousSignRewardList as $continuousNext) {
            if ($continuousSignDays < $continuousNext['days']) {
                $nextContinuousDays = $continuousNext['days'] - $continuousSignDays > 0 ?: 1;
                break;
            }
        }
        $nextCumulativeDays = 0;
        foreach ($nextCumulativeSignRewardList as $cumulativeNext) {
            if ($cumulativeSignDays < $cumulativeNext['days']) {
                $nextCumulativeDays = $cumulativeNext['days'] - $cumulativeSignDays > 0 ?: 1;
                break;
            }
        }

        //整理签到列表数据
        $signList = [];
        $i = 0;
        $checkSign = $this->getIsSign($uid, 'today');
        foreach ($dateList as $key => $time) {
            $day = date('m.d', $time);
            if ($day[0] == '0') $day = substr($day, 1);
            $signList[$key]['day'] = $day;
            $signList[$key]['is_sign'] = false;
            $signList[$key]['type'] = 0;

            //判断当前签到日期
            $signList[$key]['sign_day'] = date('Y-m-d', $time) == date('Y-m-d', time());

            //判断今日是否签到
            foreach ($list as $value) {
                if (date('Y-m-d', $time) == date('Y-m-d', $value['add_time'])) {
                    $signList[$key]['is_sign'] = true;
                    break;
                }
            }

            //处理处理签到类型展示，type 0已签到，1积分，2经验，3连续，4累积
            $signList[$key]['type'] = sys_config('sign_give_point', 0) == 0 && sys_config('member_func_status', 1) == 1 && sys_config('sign_give_exp', 0) > 0 ? 2 : 1;
            $signList[$key]['point'] = (int)sys_config('sign_give_point');
            if (date('Y-m-d', $time) >= date('Y-m-d', time())) {
                foreach ($nextContinuousSignRewardList as $continuous) {
                    if (($continuous['days'] - $continuousSignDays) == $i) {
                        $signList[$key]['type'] = 3;
                        $signList[$key]['point'] = $continuous['point'];
                    }
                }
                foreach ($nextCumulativeSignRewardList as $cumulative) {
                    if (($cumulative['days'] - $cumulativeSignDays) == $i) {
                        $signList[$key]['type'] = 4;
                        $signList[$key]['point'] = $cumulative['point'];
                    }
                }
                $i++;
            }
        }

        //格式化签到数据
        $signList = array_chunk($signList, 7);

        //获取用户签到提醒状态
        $signRemindStatus = app()->make(UserServices::class)->value($uid, 'sign_remind');

        //是否显示签到提醒开关
        $signRemindSwitch = (int)sys_config('sign_remind', 0);

        //签到功能是否关闭
        $signStatus = (int)sys_config('sign_status', 0);

        return compact('signList', 'continuousSignDays', 'cumulativeSignDays', 'nextContinuousDays', 'nextCumulativeDays', 'signMode', 'checkSign', 'signRemindStatus', 'signRemindSwitch', 'signStatus');
    }

    /**
     * 签到提醒设置
     * @param $uid
     * @param $status
     * @return bool
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/9
     */
    public function setSignRemind($uid, $status)
    {
        app()->make(UserServices::class)->update($uid, ['sign_remind' => $status]);
        return true;
    }
}
