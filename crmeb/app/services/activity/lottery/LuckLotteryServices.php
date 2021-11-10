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

namespace app\services\activity\lottery;

use app\services\BaseServices;
use app\dao\activity\lottery\LuckLotteryDao;
use app\services\coupon\StoreCouponIssueServices;
use app\services\product\product\StoreProductServices;
use app\services\user\UserBillServices;
use app\services\user\UserLabelRelationServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use think\exception\ValidateException;

/**
 *
 * Class LuckLotteryServices
 * @package app\services\activity\lottery
 * @method getFactorLottery(int $factor = 1, string $field = '*', array $with = ['prize'], bool $is_doing = true)
 */
class LuckLotteryServices extends BaseServices
{
    /**
     * 抽奖形式，奖品数量
     * @var int[]
     */
    protected $lottery_type = [
        '1' => 8 //九宫格
    ];
    /**
     * 抽奖类型
     * @var string[]
     */
    protected $lottery_factor = [
        '1' => '积分',
        '2' => '余额',
        '3' => '下单支付成功',
        '4' => '订单评价',
        '5' => '关注公众号'
    ];

    /**
     * LuckLotteryServices constructor.
     * @param LuckLotteryDao $dao
     */
    public function __construct(LuckLotteryDao $dao)
    {
        $this->dao = $dao;
    }

    public function getList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $where['is_del'] = 0;
        $list = $this->dao->getList($where, '*', ['prize'], $page, $limit);
        $lottery_factor = $this->lottery_factor;
        /** @var LuckLotteryRecordServices $luckLotteryRecordServices */
        $luckLotteryRecordServices = app()->make(LuckLotteryRecordServices::class);
        foreach ($list as &$item) {
            $item['lottery_type'] = $lottery_factor[$item['factor']] ?? '';
            $data = $luckLotteryRecordServices->getLotteryRecordData((int)$item['id']);
            $item['lottery_all'] = $data['all'] ?? 0;
            $item['lottery_people'] = $data['people'] ?? 0;
            $item['lottery_win'] = $data['win'] ?? 0;
            if ($item['status']) {
                if ($item['start_time'] == 0 && $item['end_time'] == 0) {
                    $item['lottery_status'] = '进行中';
                } else {
                    if ($item['start_time'] > time())
                        $item['lottery_status'] = '未开始';
                    else if ($item['end_time'] < time())
                        $item['lottery_status'] = '已结束';
                    else if ($item['end_time'] > time() && $item['start_time'] < time()) {
                        $item['lottery_status'] = '进行中';
                    }
                }
            } else $item['lottery_status'] = '已结束';
            $item['start_time'] = $item['start_time'] ? date('Y-m-d H:i:s', $item['start_time']) : '';
            $item['end_time'] = $item['end_time'] ? date('Y-m-d H:i:s', $item['end_time']) : '';
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 获取抽奖详情
     * @param int $id
     * @return array|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getlotteryInfo(int $id)
    {
        $lottery = $this->dao->getLottery($id, '*', ['prize']);
        if (!$lottery) {
            throw new ValidateException('活动不存在或已删除');
        }
        $lottery = $lottery->toArray();
        if (isset($lottery['prize']) && $lottery['prize']) {
            $products = $coupons = [];
            $product_ids = array_unique(array_column($lottery['prize'], 'product_id'));
            $coupon_ids = array_unique(array_column($lottery['prize'], 'coupon_id'));
            /** @var StoreProductServices $productServices */
            $productServices = app()->make(StoreProductServices::class);
            $products = $productServices->getColumn([['id', 'in', $product_ids]], 'id,store_name,image', 'id');
            /** @var StoreCouponIssueServices $couponServices */
            $couponServices = app()->make(StoreCouponIssueServices::class);
            $coupons = $couponServices->getColumn([['id', 'in', $coupon_ids]], 'id,coupon_title', 'id');
            foreach ($lottery['prize'] as &$prize) {
                $prize['coupon_title'] = $prize['goods_image'] = '';
                if ($prize['type'] == 6) {
                    $prize['goods_image'] = $products[$prize['product_id']]['image'] ?? '';
                }
                if ($prize['type'] == 5) {
                    $prize['coupon_title'] = $coupons[$prize['coupon_id']]['coupon_title'] ?? '';
                }
            }
        }
        return $lottery;
    }

    /**
     * 添加抽奖活动以及奖品
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        $prizes = $data['prize'];
        $prize_num = $this->lottery_type[1];
        if (count($prizes) != $prize_num) {
            throw new ValidateException('请选择' . $prize_num . '个奖品');
        }
        unset($data['prize']);
        return $this->transaction(function () use ($data, $prizes) {
            $time = time();
            $data['add_time'] = $time;
            if (!$lottery = $this->dao->save($data)) {
                throw new ValidateException('添加抽奖活动失败');
            }
            if ($data['status']) {
                $this->setStatus((int)$lottery->id, $data['status']);
            }
            /** @var LuckPrizeServices $luckPrizeServices */
            $luckPrizeServices = app()->make(LuckPrizeServices::class);
            $data = [];
            $sort = 1;
            foreach ($prizes as $prize) {
                $prize = $luckPrizeServices->checkPrizeData($prize);
                $prize['lottery_id'] = $lottery->id;
                unset($prize['id']);
                $prize['add_time'] = $time;
                $prize['sort'] = $sort;
                $data[] = $prize;
                $sort++;
            }
            if (!$luckPrizeServices->saveAll($data)) {
                throw new ValidateException('添加抽奖奖品失败');
            }
            return true;
        });
    }

    /**
     * 修改抽奖活动以及奖品
     * @param int $id
     * @param array $data
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit(int $id, array $data)
    {
        $lottery = $this->dao->getLottery($id);
        if (!$lottery) {
            throw new ValidateException('抽奖活动不存在');
        }
        if ($lottery['status'] && (($lottery['start_time'] == 0 && $lottery['end_time'] == 0) || ($lottery['start_time'] <= time() && $lottery['end_time'] >= time()))) {
            throw new ValidateException('活动正在进行中，暂不支持编辑');
        }
        $newPrizes = $data['prize'];
        unset($data['prize'], $data['id']);
        $prize_num = $this->lottery_type[1];
        if (count($newPrizes) != $prize_num) {
            throw new ValidateException('请选择' . $prize_num . '个奖品');
        }
        /** @var LuckPrizeServices $luckPrizeServices */
        $luckPrizeServices = app()->make(LuckPrizeServices::class);
        $prizes = $luckPrizeServices->getLotteryPrizeList($id);
        return $this->transaction(function () use ($id, $lottery, $data, $newPrizes, $prizes, $luckPrizeServices) {
            $updateIds = array_column($newPrizes, 'id');
            $oldIds = array_column($prizes, 'id');
            $delIds = array_merge(array_diff($oldIds, $updateIds));
            $insert = [];
            $time = time();
            $sort = 1;
            foreach ($newPrizes as $prize) {
                $prize = $luckPrizeServices->checkPrizeData($prize);
                $prize['sort'] = $sort;
                if (isset($prize['id']) && $prize['id']) {
                    if (!$prize['lottery_id']) {
                        throw new ValidateException('缺少活动ID');
                    }
                    if (!$luckPrizeServices->update($prize['id'], $prize, 'id')) {
                        throw new ValidateException('修改奖品失败');
                    }
                } else {
                    unset($prize['id']);
                    $prize['lottery_id'] = $id;
                    $prize['add_time'] = $time;
                    $prize['sort'] = $sort;
                    $insert[] = $prize;
                }
                $sort++;
            }
            if ($insert) {
                if (!$luckPrizeServices->saveAll($insert)) {
                    throw new ValidateException('新增奖品失败');
                }
            }
            if ($delIds) {
                if (!$luckPrizeServices->update([['id', 'in', $delIds]], ['is_del' => 1])) {
                    throw new ValidateException('删除奖品失败');
                }
            }
            if (!$this->dao->update($id, $data)) {
                throw new ValidateException('修改失败');
            }
            //上架
            if (!$lottery['status'] && $data['status']) {
                $this->setStatus($id, $data['status']);
            }
            return true;
        });
    }

    /**
     * 获取用户某个抽奖活动剩余抽奖次数
     * @param int $uid
     * @param int $lottery_id
     * @param array $userInfo
     * @param array $lottery
     * @return false|float|int|mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getLotteryNum(int $uid, int $lottery_id, array $userInfo = [], array $lottery = [])
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        if (!$userInfo) {
            $userInfo = $userServices->getUserInfo($uid);
        }
        if (!$userInfo) {
            throw new ValidateException('用户不存在');
        }
        if (!$lottery) {
            $lottery = $this->dao->getLottery($lottery_id, '*', [], true);
        }
        if (!$lottery) {
            throw new ValidateException('该活动已经下架，请持续关注');
        }
        //抽奖类型：1:积分2：余额3：下单支付成功4：订单评价5：拉新人
        switch ($lottery['factor']) {
            case 1:
                return $userInfo['integral'] > 0 && $lottery['factor_num'] > 0 ? floor($userInfo['integral'] / $lottery['factor_num']) : 0;
                break;
            case 2:
                return $userInfo['now_money'] > 0 && $lottery['factor_num'] > 0 ? floor($userInfo['now_money'] / $lottery['factor_num']) : 0;
                break;
            case 3:
                return $this->getCacheLotteryNum($uid, 'order');
                break;
            case 4:
                return $this->getCacheLotteryNum($uid, 'comment');
                break;
            case 5:
                return $userInfo['spread_lottery'] ?? 0;
                break;
            default:
                throw new ValidateException('暂未有该类型活动');
                break;
        }
    }

    /**
     * 验证用户抽奖资格（用户等级、付费会员、用户标签）
     * @param int $uid
     * @param int $lottery_id
     * @param array $userInfo
     * @param array $lottery
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkoutUserAuth(int $uid, int $lottery_id, array $userInfo = [], array $lottery = [])
    {
        if (!$userInfo) {
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $userInfo = $userServices->getUserInfo($uid);
        }
        if (!$userInfo) {
            throw new ValidateException('用户不存在');
        }
        if (!$lottery) {
            $lottery = $this->dao->getLottery($lottery_id, '*', [], true);
        }
        if (!$lottery) {
            throw new ValidateException('该活动已经下架，请持续关注');
        }
        //部分用户参与
        if ($lottery['attends_user'] == 2) {
            //用户等级
            if ($lottery['user_level'] && !in_array($userInfo['level'], $lottery['user_level'])) {
                throw new ValidateException('您暂时无法参与该活动');
            }
            //用户标签
            if ($lottery['user_label']) {
                /** @var UserLabelRelationServices $userlableRelation */
                $userlableRelation = app()->make(UserLabelRelationServices::class);
                $user_labels = $userlableRelation->getUserLabels($uid);
                if (!array_intersect($lottery['user_label'], $user_labels)) {
                    throw new ValidateException('您暂时无法参与该活动');
                }
            }
            //是否是付费会员
            if ($lottery['is_svip'] != -1) {
                if (($lottery['is_svip'] == 1 && $userInfo['is_money_level'] <= 0) || ($lottery['is_svip'] == 0 && $userInfo['is_money_level'] > 0)) {
                    throw new ValidateException('您暂时无法参与该活动');
                }
            }
        }
        return true;
    }

    /**
     * 抽奖
     * @param int $uid
     * @param int $lottery_id
     */
    public function luckLottery(int $uid, int $lottery_id)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->getUserInfo($uid);
        if (!$userInfo) {
            throw new ValidateException('用户不存在');
        }
        $lottery = $this->dao->getLottery($lottery_id, '*', [], true);
        if (!$lottery) {
            throw new ValidateException('该活动已经下架，请持续关注');
        }
        $userInfo = $userInfo->toArray();
        $lottery = $lottery->toArray();
        //验证用户身份
        $this->checkoutUserAuth($uid, $lottery_id, $userInfo, $lottery);

        /** @var LuckPrizeServices $lotteryPrizeServices */
        $lotteryPrizeServices = app()->make(LuckPrizeServices::class);
        $lotteryPrize = $lotteryPrizeServices->getPrizeList($lottery_id);
        if (!$lotteryPrize) {
            throw new ValidateException('该活动状态有误，请联系管理员');
        }
        if ($this->getLotteryNum($uid, $lottery_id, $userInfo, $lottery) < 1) {
            //抽奖类型：1:积分2：余额3：下单支付成功4：订单评价5：拉新人
            switch ($lottery['factor']) {
                case 1:
                    throw new ValidateException('积分不足，没有更多抽奖次数');
                    break;
                case 2:
                    throw new ValidateException('余额不足，没有更多抽奖次数');
                    break;
                case 3:
                    throw new ValidateException('购买商品之后获得更多抽奖次数');
                    break;
                case 4:
                    throw new ValidateException('订单完成评价之后获得更多抽奖次数');
                    break;
                case 5:
                    throw new ValidateException('邀请更多好友获取抽奖次数');
                    break;
                default:
                    throw new ValidateException('暂未有该类型活动');
                    break;
            }
        }
        return $this->transaction(function () use ($uid, $lotteryPrize, $userInfo, $lottery) {
            /** @var LuckPrizeServices $luckPrizeServices */
            $luckPrizeServices = app()->make(LuckPrizeServices::class);
            //随机抽奖
            $prize = $luckPrizeServices->getLuckPrize($lotteryPrize);
            if (!$prize) {
                throw new ValidateException('该活动状态有误，请联系管理员');
            }
            //中奖扣除积分、余额
            $this->lotteryFactor($uid, $userInfo, $lottery);
            //中奖减少奖品数量
            $luckPrizeServices->decPrizeNum($prize['id'], $prize);
            /** @var LuckLotteryRecordServices $lotteryRecordServices */
            $lotteryRecordServices = app()->make(LuckLotteryRecordServices::class);
            //中奖写入记录
            $record = $lotteryRecordServices->insertPrizeRecord($uid, $prize, $userInfo);
            //不是站内商品直接领奖
            if ($prize['type'] != 6) {
                $lotteryRecordServices->receivePrize($uid, (int)$record->id);
            }
            $prize['lottery_record_id'] = $record->id;
            return $prize;
        });
    }

    /**
     * 抽奖消耗扣除用户积分、余额等
     * @param int $uid
     * @param array $userInfo
     * @param array $lottery
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function lotteryFactor(int $uid, array $userInfo, array $lottery)
    {
        if (!$userInfo || !$lottery) {
            return true;
        }
        //抽奖类型：1:积分2：余额3：下单支付成功4：订单评价5：拉新人
        switch ($lottery['factor']) {
            case 1:
                if ($userInfo['integral'] > $lottery['factor_num']) {
                    $integral = bcsub((string)$userInfo['integral'], (string)$lottery['factor_num'], 0);
                } else {
                    $integral = 0;
                }
                /** @var UserServices $userServices */
                $userServices = app()->make(UserServices::class);
                /** @var UserBillServices $userBillServices */
                $userBillServices = app()->make(UserBillServices::class);
                $userBillServices->income('lottery_use_integral', $uid, $lottery['factor_num'], $userInfo['integral'], $lottery['id']);
                if (!$userServices->update($uid, ['integral' => $integral], 'uid')) {
                    throw new ValidateException('抽奖扣除用户积分失败');
                }
                break;
            case 2:
                if ($userInfo['now_money'] > $lottery['factor_num']) {
                    $now_money = bcsub((string)$userInfo['now_money'], (string)$lottery['factor_num'], 2);
                } else {
                    $now_money = 0;
                }
                /** @var UserServices $userServices */
                $userServices = app()->make(UserServices::class);
                /** @var UserBillServices $userBillServices */
                $userBillServices = app()->make(UserBillServices::class);
                $userBillServices->income('lottery_use_money', $uid, $lottery['factor_num'], $userInfo['now_money'], $lottery['id']);
                if (!$userServices->update($uid, ['now_money' => $now_money], 'uid')) {
                    throw new ValidateException('抽奖扣除用户积分失败');
                }
                break;
            case 3:
            case 4:
                //销毁抽奖次数缓存
                $this->delCacheLotteryNum($uid, $lottery['factor'] == 3 ? 'order' : 'comment');
                break;
            case 5:
                /** @var UserServices $userServices */
                $userServices = app()->make(UserServices::class);
                $spread_lottery = 0;
                if ($userInfo['spread_lottery'] > 1) {
                    $spread_lottery = $userInfo['spread_lottery'] - 1;
                }
                if (!$userServices->update($uid, ['spread_lottery' => $spread_lottery], 'uid')) {
                    throw new ValidateException('抽奖扣除用户推广获取抽奖次数失败');
                }
                break;
            default:
                throw new ValidateException('暂未有该类型活动');
                break;
        }
        return true;
    }

    /**
     * 删除
     * @param int $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delLottery(int $id)
    {
        if ($lottery = $this->dao->getLottery($id)) {
            if (!$this->dao->update(['id' => $id], ['is_del' => 1])) {
                throw new AdminException('删除失败，请稍候重试');
            }
        }
        return true;
    }

    /**
     * 设置抽奖活动状态
     * @param int $id
     * @param $status
     * @return false|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setStatus(int $id, $status)
    {
        if (!$id) return false;
        $lottery = $this->dao->getLottery($id, 'id,factor');
        if (!$lottery) {
            return false;
        }
        //每一种抽奖类型只有一个上架
        if ($status) {
            $this->dao->update(['factor' => $lottery['factor']], ['status' => 0]);
        }
        return $this->dao->update($id, ['status' => $status], 'id');
    }

    /**
     *  下单支付、评论缓存抽奖次数
     * @param int $uid
     * @param string $type
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setCacheLotteryNum(int $uid, string $type = 'order')
    {
        $factor = $type == 'order' ? 3 : 4;
        $lottery = $this->dao->getFactorLottery($factor, 'id,factor_num');
        if (!$lottery || !$lottery['factor_num']) {
            return true;
        }
        $key = 'user_' . $type . '_luck_lottery_' . $uid;
        $cache = CacheService::redisHandler();
        return $cache->set($key, $lottery['factor_num'], 120);
    }

    /**
     * 取出下单支付、评论得到的抽奖此处
     * @param int $uid
     * @param string $type
     * @return int|mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getCacheLotteryNum(int $uid, string $type = 'order')
    {
        $key = 'user_' . $type . '_luck_lottery_' . $uid;
        $num = CacheService::redisHandler()->get($key);
        return empty($num) ? 0 : $num;
    }

    /**
     *  抽奖之后销毁缓存
     * @param int $uid
     * @param string $type
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function delCacheLotteryNum(int $uid, string $type = 'order')
    {
        $key = 'user_' . $type . '_luck_lottery_' . $uid;
        $num = $this->getCacheLotteryNum($uid, $type);
        $cache = CacheService::redisHandler();
        if ($num > 1) {
            $cache->set($key, $num - 1, 120);
        } else {
            $cache->delete($key);
        }
        return true;
    }
}
