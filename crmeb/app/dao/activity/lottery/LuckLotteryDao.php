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

namespace app\dao\activity\lottery;

use app\dao\BaseDao;
use app\model\activity\lottery\LuckLottery;

/**
 *
 * Class LuckLotteryDao
 * @package app\dao\activity\lottery
 */
class LuckLotteryDao extends BaseDao
{

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return LuckLottery::class;
    }

    /**
     * 抽奖搜索
     * @param array $data
     * @param bool $search
     * @return \crmeb\basic\BaseModel
     * @throws \ReflectionException
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/03/20
     */
    public function search(array $where = [], bool $search = false)
    {
        return parent::search($where, $search)->when(isset($where['id']) && $where['id'], function ($query) use ($where) {
            $query->where('id', $where['id']);
        })->when(isset($where['start']) && $where['start'] !== '', function ($query) use ($where) {
            $time = time();
            switch ($where['start']) {
                case 0:
                    $query->where('start_time', '>', $time)->where('status', 1);
                    break;
                case -1:
                    $query->where(function ($query1) use ($time) {
                        $query1->where('end_time', '<', $time)->whereOr('status', 0);
                    });
                    break;
                case 1:
                    $query->where('status', 1)->where(function ($query1) use ($time) {
                        $query1->where(function ($query2) use ($time) {
                            $query2->where('start_time', '<=', $time)->where('end_time', '>=', $time);
                        })->whereOr(function ($query3) {
                            $query3->where('start_time', 0)->where('end_time', 0);
                        });
                    });
                    break;
            }
        });
    }

    /**
     * 抽奖活动列表
     * @param array $where
     * @param string $field
     * @param array $with
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where, string $field = '*', array $with = [], int $page = 0, int $limit = 0)
    {
        return $this->search($where)->field($field)->when($with, function ($query) use ($with) {
            $query->with($with);
        })->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->order('add_time desc')->select()->toArray();
    }

    /**
     * 获取单个活动
     * @param int $id
     * @param string $field
     * @param array|string[] $with
     * @param bool $is_doing
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getLottery(int $id, string $field = '*', array $with = ['prize'], bool $is_doing = false)
    {
        $where = ['id' => $id];
        $where['is_del'] = 0;
        if ($is_doing) $where['start'] = 1;
        return $this->search($where)->field($field)->when($with, function ($query) use ($with) {
            $query->with($with);
        })->find();
    }

    /**
     * 获取某个抽奖类型的一条抽奖数据
     * @param int $factor
     * @param string $field
     * @param array|string[] $with
     * @param bool $is_doing
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getFactorLottery(int $factor = 1, string $field = '*', array $with = ['prize'], bool $is_doing = false)
    {
        $where = ['factor' => $factor, 'is_del' => 0];
        if ($is_doing) $where['start'] = 1;
        return $this->search($where)->field($field)->when($with, function ($query) use ($with) {
            $query->with($with);
        })->find();
    }
}
