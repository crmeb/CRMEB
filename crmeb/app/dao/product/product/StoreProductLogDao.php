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

namespace app\dao\product\product;


use app\dao\BaseDao;
use app\model\product\product\StoreProductLog;

class StoreProductLogDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return StoreProductLog::class;
    }

    public function getRanking($where)
    {
        return $this->search($where)->with('storeName')
            ->field([
                'product_id',
                'SUM(visit_num) as visit',
                'COUNT(distinct(uid)) as user',
                'SUM(cart_num) as cart',
                'SUM(order_num) as orders',
                'SUM(pay_num) as pay',
                'SUM(pay_price * pay_num) as price',
                'SUM(cost_price) as cost',
                'ROUND((SUM(pay_price)-SUM(cost_price))/SUM(cost_price),2) as profit',
                'SUM(collect_num) as collect',
                'ROUND((COUNT(distinct(pay_uid))-1)/COUNT(distinct(uid)),2) as changes',
                'COUNT(distinct(pay_uid))-1 as repeats'
            ])->group('product_id')->order("$where[sort] desc")->limit(20)->select()->toArray();
    }

    public function getRepeats($where, $product_id)
    {
        return count($this->search($where)->where('type', 'pay')->where('product_id', $product_id)->field('count(pay_uid) as p')->group('pay_uid')->having('p>1')->select());
    }

    /**
     * 访问趋势
     * @param $time
     * @param $timeType
     * @param $str
     * @return mixed
     */
    public function getProductTrend($time, $timeType, $str)
    {
        return $this->getModel()->where(function ($query) use ($time) {
            if ($time[0] == $time[1]) {
                $query->whereDay('add_time', $time[0]);
            } else {
                $time[1] = date('Y/m/d', strtotime($time[1]) + 86400);
                $query->whereTime('add_time', 'between', $time);
            }
        })->field("FROM_UNIXTIME(add_time,'$timeType') as days,$str as num")->group('days')->select()->toArray();
    }

    /**
     * 列表
     * @param array $where
     * @param string $field
     * @param int $page
     * @param int $limit
     * @param string $group
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where, string $field = '*', int $page = 0, int $limit = 0, string $group = '')
    {
        return $this->search($where)->with(['storeName'])->field($field)
            ->when($page != 0 && $limit != 0, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })->when($group, function ($query) use ($group) {
                $query->group($group);
            })->order('add_time desc')->select()->toArray();
    }
}
