<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\dao\system\statistics;

use app\dao\BaseDao;
use app\model\system\statistics\CapitalFlow;

class CapitalFlowDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return CapitalFlow::class;
    }

    /**
     * 资金流水
     * @param $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList($where, $page = 0, $limit = 0)
    {
        return $this->search($where)->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->order('id desc')->select()->toArray();
    }

    /**
     * 账单记录
     * @param $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRecordList($where, $page = 0, $limit = 0)
    {
        $model = $this->search($where)
            ->when(isset($where['type']) && $where['type'] !== '', function ($query) use ($where) {
                $timeUnix = '%d';
                switch ($where['type']) {
                    case "day" :
                        $timeUnix = "%d";
                        break;
                    case "week" :
                        $timeUnix = "%u";
                        break;
                    case "month" :
                        $timeUnix = "%m";
                        break;
                }
                $query->field("FROM_UNIXTIME(add_time,'$timeUnix') as day,sum(if(price >= 0,price,0)) as income_price,sum(if(price < 0,price,0)) as exp_price,add_time,group_concat(id) as ids");
                $query->group("FROM_UNIXTIME(add_time, '$timeUnix')");
            });
        $count = $model->count();
        $list = $model->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->order('add_time desc')->select()->toArray();
        return compact('list', 'count');
    }
}
