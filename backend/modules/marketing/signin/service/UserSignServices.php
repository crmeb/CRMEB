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

namespace modules\marketing\signin\service;

use crmeb\basic\BaseService;
use modules\marketing\signin\dao\UserSignDao;
use app\services\user\UserServices;
use app\services\user\UserBillServices;
use app\services\user\member\MemberCardServices;
use crmeb\exceptions\ApiException;
use crmeb\services\PluginCacheService;
use think\exception\ValidateException;

/**
 * 用户签到服务
 * Class UserSignServices
 * @package modules\marketing\signin\service
 */
class UserSignServices extends BaseService
{
    /**
     * @var UserSignDao
     */
    protected $dao;

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
        if ($type === 'today' || $type === 'yesterday') {
            return $this->dao->hasSignOnDate($uid, (int)strtotime($type, time()));
        }
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
        if (!(int)sys_config('sign_status', 0)) {
            throw new ApiException('签到功能未开启');
        }
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        return $this->transaction(function () use ($uid, $userServices) {
            // 先锁用户行再判重，文件缓存模式下也只能发放一次奖励。
            $user = $userServices->getDb()->where('uid', $uid)->lock(true)
                ->field('uid,integral,sign_num,is_money_level,is_ever_level,overdue_time')->find();
            if ($uid <= 0 || !$user) {
                throw new ApiException('用户不存在');
            }
            $now = time();
            if ($this->dao->hasSignOnDate($uid, $now)) {
                throw new ApiException('已经签到');
            }
            $yesterdaySigned = $this->dao->hasSignOnDate($uid, (int)strtotime('yesterday', $now));
            $mode = (int)sys_config('sign_mode', -1);
            $continuousDays = $this->normalizeContinuousDays((int)$user['sign_num'], false, $yesterdaySigned, $mode, $now) + 1;
            $cumulativeDays = $this->dao->getCumulativeDays($mode, $uid, $now) + 1;
            if (!$userServices->offMemberLevel($uid, $user)) {
                $user['is_money_level'] = 0;
            }
            $multiplier = $this->getMemberMultiplier($user, $now);
            $reward = $this->calculateSignReward($continuousDays, $cumulativeDays, $multiplier);
            $title = '签到奖励';
            if ($multiplier > 1 && !$yesterdaySigned) {
                $title .= '(SVIP+' . ($reward['point'] - intdiv($reward['point'], $multiplier)) . ')';
            }
            if (!$this->dao->save([
                'uid' => $uid,
                'title' => $title,
                'number' => $reward['point'],
                'balance' => $user['integral'] + $reward['point'],
                'add_time' => $now,
            ])) {
                throw new \RuntimeException('添加签到数据失败');
            }
            if (!$userServices->update($uid, ['sign_num' => $continuousDays])) {
                throw new \RuntimeException('修改用户信息失败');
            }
            /** @var UserBillServices $userBillServices */
            $userBillServices = app()->make(UserBillServices::class);
            if ($reward['point'] > 0 && !$userBillServices->billIntegralIncome('sign', $uid, $reward['point'], 0, $title)) {
                throw new \RuntimeException('签到积分发放失败');
            }
            if ($reward['exp'] > 0 && !$userBillServices->billExpIncome('sign_exp', $uid, $reward['exp'], 0, '签到奖励')) {
                throw new \RuntimeException('签到经验发放失败');
            }
            return $reward['point'];
        });
    }

    /**
     * 获取签到周期起点，无周期时返回零
     * @param int $mode
     * @param int $timestamp
     * @return int
     */
    protected function getPeriodStart(int $mode, int $timestamp): int
    {
        if ($mode === 1) {
            return (int)strtotime('monday this week midnight', $timestamp);
        }
        if ($mode === 0) {
            return (int)strtotime('first day of this month midnight', $timestamp);
        }
        return 0;
    }

    /**
     * 按断签与周期边界归一化连续签到天数
     * @param int $storedDays
     * @param bool $todaySigned
     * @param bool $yesterdaySigned
     * @param int $mode
     * @param int $timestamp
     * @return int
     */
    protected function normalizeContinuousDays(int $storedDays, bool $todaySigned, bool $yesterdaySigned, int $mode, int $timestamp): int
    {
        if (!$todaySigned && !$yesterdaySigned) {
            return 0;
        }
        $lastDay = (int)strtotime($todaySigned ? 'today' : 'yesterday', $timestamp);
        $start = $this->getPeriodStart($mode, $timestamp);
        $days = max(1, $storedDays);
        return $start ? min($days, max(0, (int)(($lastDay - $start) / 86400) + 1)) : $days;
    }

    /**
     * 仅有效付费会员享受签到积分倍率
     * @param mixed $user
     * @param int $timestamp
     * @param int|null $configuredMultiplier 已读取的权益倍率，避免日历逐日查询
     * @return int
     */
    protected function getMemberMultiplier($user, int $timestamp, ?int $configuredMultiplier = null): int
    {
        if (!$user || (int)$user['is_money_level'] <= 0
            || (!(int)$user['is_ever_level'] && (int)$user['overdue_time'] < $timestamp)) {
            return 1;
        }
        return $configuredMultiplier ?? max(1, (int)app()->make(MemberCardServices::class)->isOpenMemberCard('sign'));
    }

    /**
     * 统一计算实际签到和日历预测奖励，达标奖励替代基础奖励
     * @param int $continuousDays
     * @param int $cumulativeDays
     * @param int $multiplier
     * @param array|null $rewardMap 日历批量加载的奖励映射
     * @return array
     */
    protected function calculateSignReward(int $continuousDays, int $cumulativeDays, int $multiplier = 1, ?array $rewardMap = null): array
    {
        $point = max(0, (int)sys_config('sign_give_point', 0));
        $exp = max(0, (int)sys_config('sign_give_exp', 0));
        $type = $point === 0 && $exp > 0 && (int)sys_config('member_func_status', 1) ? 2 : 1;
        $matched = false;
        foreach ([0 => $continuousDays, 1 => $cumulativeDays] as $rewardType => $days) {
            if ($rewardMap === null) {
                [$exists, $rewardPoint, $rewardExp] = app()->make(SystemSignRewardServices::class)->getSignRewards($rewardType, $days);
            } else {
                $rule = $rewardMap[$rewardType][$days] ?? null;
                [$exists, $rewardPoint, $rewardExp] = [$rule !== null, $rule['point'] ?? 0, $rule['exp'] ?? 0];
            }
            if ($exists) {
                if (!$matched) {
                    $point = $exp = 0;
                }
                $point += max(0, (int)$rewardPoint);
                $exp += max(0, (int)$rewardExp);
                $type = $rewardType === 0 ? 3 : 4;
                $matched = true;
            }
        }
        $point *= $multiplier;
        $exp = (int)sys_config('member_func_status', 1) ? $exp : 0;
        return compact('point', 'exp', 'type');
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
            throw new ApiException('数据不存在');
        }
        //是否统计签到
        if ($sign || $all) {
            $user['sum_sgin_day'] = $this->getSignSumDay($user['uid']);
            $user['is_day_sgin'] = $this->getIsSign($user['uid'], 'today');
            $user['is_YesterDay_sgin'] = $this->getIsSign($user['uid'], 'yesterday');
            $user['sign_num'] = $this->normalizeContinuousDays((int)$user['sign_num'], $user['is_day_sgin'], $user['is_YesterDay_sgin'], (int)sys_config('sign_mode', -1), time());
        }
        //是否统计积分使用情况
        if ($integral || $all) {
            /** @var UserBillServices $userBill */
            $userBill = app()->make(UserBillServices::class);
            $user['sum_integral'] = intval($userBill->getRecordCount($user['uid'], 'integral', 'sign,system_add,gain,lottery_add,product_gain,pay_product_integral_back'));
            $user['deduction_integral'] = intval($userBill->getRecordCount($user['uid'], 'integral', 'deduction,lottery_use,order_deduction,storeIntegral_use', '', true) ?? 0);
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
            $dataIdsList = $this->dao->getList(['id' => $ids], 'add_time,title,number,id,uid', 0, 0);
            foreach ($dataIdsList as &$val) {
                // add_time 与表字段同名会被模型按 int 转换截断，需取原始时间戳在 PHP 侧格式化
                $val['add_time'] = date('Y-m-d H:i:s', $val['add_time']);
            }
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
    public function signConfig($uid, $signMode = 0)
    {
        $uid = (int)$uid;
        $now = time();
        $today = (int)strtotime('today', $now);
        $rewardMode = (int)sys_config('sign_mode', -1);
        $signMode = $signMode ? (int)$signMode : $rewardMode;
        // DIY 可固定显示本周，但奖励和统计仍遵循实际配置的周期。
        $startDate = $signMode == 1 ? strtotime('monday this week midnight', $now) : strtotime('first day of this month midnight', $now);
        $endDate = $signMode == 1 ? strtotime('sunday this week midnight', $now) : strtotime('last day of this month midnight', $now);
        $dateList = range($startDate, $endDate, 86400);
        $list = $this->dao->getUserSignList($signMode, $uid, $now);
        $checkSign = $this->dao->hasSignOnDate($uid, $now);
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $user = $uid > 0 ? $userServices->getUserInfo($uid, 'uid,sign_num,sign_remind,is_money_level,is_ever_level,overdue_time') : null;
        $cumulativeSignDays = $this->dao->getCumulativeDays($rewardMode, $uid, $now);
        $continuousSignDays = $this->normalizeContinuousDays((int)($user['sign_num'] ?? 0), $checkSign, $this->dao->hasSignOnDate($uid, (int)strtotime('yesterday', $now)), $rewardMode, $now);
        $multiplier = $this->getMemberMultiplier($user, $now);
        /** @var SystemSignRewardServices $rewardServices */
        $rewardServices = app()->make(SystemSignRewardServices::class);
        $nextContinuous = $rewardServices->getNextRewardDays(0, $continuousSignDays);
        $nextCumulative = $rewardServices->getNextRewardDays(1, $cumulativeSignDays);
        $nextContinuousDays = $nextContinuous ? $nextContinuous - $continuousSignDays : 0;
        $nextCumulativeDays = $nextCumulative ? $nextCumulative - $cumulativeSignDays : 0;

        $signedDates = [];
        foreach ($list as $record) {
            $signedDates[date('Y-m-d', $record['add_time'])] = $record;
        }
        $forecast = [];
        $continuousTargets = $cumulativeTargets = [];
        $continuous = $continuousSignDays;
        $cumulative = $cumulativeSignDays;
        $period = $this->getPeriodStart($rewardMode, $today);
        foreach ($dateList as $timestamp) {
            if ($timestamp < $today) {
                continue;
            }
            $nextPeriod = $this->getPeriodStart($rewardMode, $timestamp);
            if ($nextPeriod !== $period) {
                $continuous = $cumulative = 0;
                $period = $nextPeriod;
            }
            if ($timestamp !== $today || !$checkSign) {
                $continuous++;
                $cumulative++;
            }
            $forecast[$timestamp] = [$continuous, $cumulative];
            $continuousTargets[] = $continuous;
            $cumulativeTargets[] = $cumulative;
        }
        // 只批量查询日历内会命中的阈值，不逐日访问数据库。
        $rewardMap = [];
        foreach ($rewardServices->getRewardsByDays($continuousTargets, $cumulativeTargets) as $rule) {
            if (!isset($rewardMap[$rule['type']][$rule['days']])) {
                $rewardMap[$rule['type']][$rule['days']] = $rule;
            }
        }
        $signList = [];
        foreach ($dateList as $timestamp) {
            $date = date('Y-m-d', $timestamp);
            $record = $signedDates[$date] ?? null;
            [$continuous, $cumulative] = $forecast[$timestamp] ?? [0, 0];
            // 未来日期按当前日内时刻预测会员状态，不延续已经到期的权益。
            $dailyMultiplier = $this->getMemberMultiplier($user, $timestamp + ($now - $today), $multiplier);
            $reward = $this->calculateSignReward($continuous, $cumulative, $dailyMultiplier, $rewardMap);
            $signList[] = [
                'day' => ltrim(date('m.d', $timestamp), '0'),
                'is_sign' => $record !== null,
                'type' => $record ? 0 : $reward['type'],
                'sign_day' => $timestamp === $today,
                'point' => $record ? (int)$record['number'] : $reward['point'],
                'exp' => $reward['exp'],
            ];
        }
        $signList = array_chunk($signList, 7);
        $signRemindStatus = (int)($user['sign_remind'] ?? 0);
        $signRemindSwitch = (int)sys_config('sign_remind', 0);
        $signStatus = (int)sys_config('sign_status', 0);
        $signGivePoint = (int)sys_config('sign_give_point', 0);
        return compact('signList', 'continuousSignDays', 'cumulativeSignDays', 'nextContinuousDays', 'nextCumulativeDays', 'signMode', 'checkSign', 'signRemindStatus', 'signRemindSwitch', 'signStatus', 'signGivePoint');
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
        if (!in_array($status, [0, 1, '0', '1'], true)) {
            throw new ValidateException('签到提醒状态必须为0或1');
        }
        $userServices = app()->make(UserServices::class);
        if ((int)$uid <= 0 || !$userServices->getUserInfo((int)$uid, 'uid')) {
            throw new ApiException('用户不存在');
        }
        if (!$userServices->update((int)$uid, ['sign_remind' => (int)$status])) {
            throw new \RuntimeException('签到提醒设置失败');
        }
        return true;
    }

    /**
     * 签到提醒
     * @return bool
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2023/9/30
     */
    public function sendSignRemind()
    {
        if (!(int)sys_config('sign_status', 0) || !(int)sys_config('sign_remind', 0)) {
            return true;
        }
        $channels = [];
        foreach ((array)sys_config('sign_remind_type', []) as $type) {
            if ($type === 1 || $type === '1') {
                $channels[] = 'is_sms';
            } elseif ($type === 2 || $type === '2') {
                $channels[] = 'is_system';
            }
        }
        if (!$channels) {
            return true;
        }
        $remindTime = (string)sys_config('sign_remind_time', '');
        if (!preg_match('/^(?:[01]?\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/D', $remindTime)) {
            throw new \RuntimeException('签到提醒时间配置无效');
        }
        $now = time();
        $cacheKey = 'reminder:sent:' . date('Y-m-d', $now);
        if ($now < strtotime('today ' . $remindTime, $now) || PluginCacheService::get($cacheKey)) {
            return true;
        }
        $start = (int)strtotime('today', $now);
        $end = (int)strtotime('tomorrow', $now);
        $lastUid = 0;
        $userServices = app()->make(UserServices::class);
        // 按用户游标分批读取，每批只查询一次已签到用户，避免加载全站数据。
        do {
            $list = $userServices->getDb()->where('sign_remind', 1)->where('status', 1)->where('is_del', 0)
                ->where('uid', '>', $lastUid)->field('uid,phone')->order('uid asc')->limit(500)->select()->toArray();
            if (!$list) {
                break;
            }
            $uids = array_column($list, 'uid');
            $signed = array_flip($this->dao->getColumn([
                ['uid', 'in', $uids], ['add_time', '>=', $start], ['add_time', '<', $end],
            ], 'uid'));
            foreach ($list as $user) {
                $lastUid = (int)$user['uid'];
                if (isset($signed[$lastUid])) {
                    continue;
                }
                event('Sign', ['sign_remind', [
                    'uid'       => $lastUid,
                    'phone'     => $user['phone'],
                    'sign_days' => 0,
                    'integral'  => 0,
                    'channels'  => array_values(array_unique($channels)),
                ]]);
            }
        } while (count($list) === 500);
        PluginCacheService::set($cacheKey, 1, max(1, $end - time()));
        return true;
    }
}
