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

namespace app\services\activity\lottery;

use app\services\BaseServices;
use app\dao\activity\lottery\LuckPrizeDao;
use app\services\activity\coupon\StoreCouponIssueServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;

/**
 *
 * Class LuckPrizeServices
 * @package app\services\activity\lottery
 */
class LuckPrizeServices extends BaseServices
{
    /**
     * @var array 1：未中奖2：积分3:余额4：红包5:优惠券6：站内商品7：等级经验8：用户等级 9：svip天数
     */
    public $prize_type = [
        '1' => '未中奖',
        '2' => '积分',
        '3' => '余额',
        '4' => '红包',
        '5' => '优惠券',
        '6' => '站内商品',
        '7' => '等级经验',
        '8' => '用户等级',
        '9' => 'svip天数'
    ];

    /**
     * 奖品数据字段
     * @var array
     */
    public $prize = [
        'id' => 0,
        'type' => 1,
        'lottery_id' => 0,
        'name' => '',
        'prompt' => '',
        'image' => '',
        'chance' => 0,
        'total' => 0,
        'coupon_id' => 0,
        'product_id' => 0,
        'unique' => '',
        'num' => 1,
        'sort' => 0,
        'status' => 1,
        'is_del' => 0,
        'add_time' => 0,
    ];

    /**
     * LuckPrizeServices constructor.
     * @param LuckPrizeDao $dao
     */
    public function __construct(LuckPrizeDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 奖品数据验证
     * @param array $data
     * @return bool
     */
    public function checkPrizeData(array $data)
    {
        $data = array_merge($this->prize, array_intersect_key($data, $this->prize));
        if (!isset($data['name']) || !$data['name']) {
            throw new AdminException(400538);
        }
        if (!isset($data['image']) || !$data['image']) {
            throw new AdminException(400539);
        }
        if (!isset($data['chance']) || !$data['chance']) {
            throw new AdminException(400540);
        }
        if (!isset($data['type']) || !isset($this->prize_type[$data['type']])) {
            throw new AdminException(400541);
        }
        if (in_array($data['type'], [2, 3, 4]) && (!isset($data['num']) || !$data['num'])) {
            $msg = '';
            switch ($data['type']) {
                case 2:
                    $msg = '积分';
                    break;
                case 3:
                    $msg = '余额';
                    break;
                case 4:
                    $msg = '红包';
                    break;
            }
            throw new AdminException(400542, ['type' => $msg]);
        }
        if ($data['type'] == 5 && (!isset($data['coupon_id']) || !$data['coupon_id'])) {
            throw new AdminException(400543);
        }
        if ($data['type'] == 6 && (!isset($data['product_id']) || !$data['product_id'])) {
            throw new AdminException(400337);
        }
        return $data;
    }

    /**
     * 获取某个抽奖活动的所有奖品
     * @param int $lottery_id
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getLotteryPrizeList(int $lottery_id, string $field = '*')
    {
        return $this->dao->getPrizeList($lottery_id, $field);
    }


    /**
     * 随机奖品
     * @param array $data
     * @return array|mixed
     */
    function getLuckPrize(array $data)
    {
        $prize = [];
        if (!$data) return $prize;
        $coupon = [];
        $coupon_ids = array_unique(array_column($data, 'coupon_id'));
        if ($coupon_ids) {
            /** @var StoreCouponIssueServices $couponServices */
            $couponServices = app()->make(StoreCouponIssueServices::class);
            $coupon = $couponServices->getGiveCoupon([['id', 'IN', $coupon_ids]]);
            if ($coupon) $coupon = array_combine(array_column($coupon, 'id'), $coupon);
        }
        $totalChance = array_sum(array_column($data, 'chance'));
        if (!$totalChance) return $prize;
        $startChance = 0;
        mt_srand();
        $prizeChance = rand(0, $totalChance);
        $newPrize = array_combine(array_column($data, 'type'), $data);
        foreach ($data as $item) {
            $newStartChance = $item['chance'] + $startChance;
            //随机数在这个基数端内 且该商品数量大于0 中奖
            if ($prizeChance >= $startChance && $prizeChance < $newStartChance) {
                //随机到不是未中奖奖品-》设置了奖品数量-》数量不足时 返回未中奖奖品   || 抽到优惠券 数量不足
                if (($item['type'] != 1 && $item['total'] != -1 && $item['total'] <= 0) || ($item['coupon_id'] && $coupon && !isset($coupon[$item['coupon_id']]))) {
                    $prize = $newPrize[1] ?? [];
                } else {
                    $prize = $item;
                }
                break;
            }
            $startChance = $newStartChance;
        }
        return $prize;
    }

    /**
     * 中奖后减少奖品数量
     * @param int $id
     * @param array $prize
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function decPrizeNum(int $id, array $prize = [])
    {
        if (!$id) return false;
        if (!$prize) {
            $prize = $this->dao->get($id);
        }
        if (!$prize) {
            throw new ApiException(410048);
        }
        //不是未中奖奖品 减少奖品数量
        if ($prize['type'] != 1 && $prize['total'] >= 1) {
            $total = $prize['total'] - 1;
            if (!$this->dao->update($id, ['total' => $total], 'id')) {
                throw new ApiException(410070);
            }
        }
        return true;
    }
}
