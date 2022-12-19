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

namespace app\dao\user;

use app\dao\BaseDao;
use app\model\user\User;
use app\model\user\UserBrokerage;


/**
 * 用户关联佣金
 * Class UserUserBrokerageDao
 * @package app\dao\user
 */
class UserUserBrokerageDao extends BaseDao
{

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return User::class;
    }

    public function joinModel(): string
    {
        return UserBrokerage::class;
    }

    /**
     * 关联模型
     * @param string $alias
     * @param string $join_alias
     * @return \crmeb\basic\BaseModel
     */
    public function getModel(string $alias = 'u', string $join_alias = 'b', $join = 'left')
    {
        $this->alias = $alias;
        $this->join_alis = $join_alias;
        /** @var UserBrokerage $userBrokerage */
        $userBrokerage = app()->make($this->joinModel());
        $table = $userBrokerage->getName();
        return parent::getModel()
            ->join($table . ' ' . $join_alias, $alias . '.uid = ' . $join_alias . '.uid', $join)
            ->alias($alias);
    }

    /**
     * 组合条件模型查询列表
     * @param array $where
     * @param string $field
     * @param string $order
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getList(array $where, string $field = '', string $order = '', int $page = 0, int $limit = 0)
    {
        $time = $where['time'];
        unset($where['time']);
        return $this->getModel()->when($time != '', function ($query) use ($time) {
            $timeKey = $this->join_alis . '.add_time';
            switch ($time) {
                case 'today':
                case 'week':
                case 'month':
                case 'year':
                case 'yesterday':
                case 'last year':
                case 'last week':
                case 'last month':
                    $query->whereTime($timeKey, $time);
                    break;
                case 'quarter':
                    [$startTime, $endTime] = $this->getMonth();
                    $query->whereBetween($timeKey, [$startTime, $endTime]);
                    break;
                case 'lately7':
                    $query->whereBetween($timeKey, [strtotime("-7 day"), time()]);
                    break;
                case 'lately30':
                    $query->whereBetween($timeKey, [strtotime("-30 day"), time()]);
                    break;
                default:
                    if (strstr($time, '-') !== false) {
                        [$startTime, $endTime] = explode('-', $time);
                        $startTime = trim($startTime);
                        $endTime = trim($endTime);
                        if ($startTime && $endTime) {
                            $query->whereBetween($timeKey, [strtotime($startTime), $startTime == $endTime ? strtotime($endTime) + 86400 : strtotime($endTime)]);
                        } else if (!$startTime && $endTime) {
                            $query->whereTime($timeKey, '<', strtotime($endTime) + 86400);
                        } else if ($startTime && !$endTime) {
                            $query->whereTime($timeKey, '>=', strtotime($startTime));
                        }
                    }
                    break;
            }
        })->where($where)->field($field)->group('u.uid')->order($order)->order('id desc')
            ->when($page && $limit, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })->select()->toArray();
    }

    /**
     * 获取条数
     * @param array $where
     * @return mixed
     */
    public function getCount(array $where)
    {
        $time = $where['time'];
        unset($where['time']);
        return $this->getModel()->when($time != '', function ($query) use ($time) {
            $timeKey = $this->join_alis . '.add_time';
            switch ($time) {
                case 'today':
                case 'week':
                case 'month':
                case 'year':
                case 'yesterday':
                case 'last year':
                case 'last week':
                case 'last month':
                    $query->whereTime($timeKey, $time);
                    break;
                case 'quarter':
                    [$startTime, $endTime] = $this->getMonth();
                    $query->whereBetween($timeKey, [$startTime, $endTime]);
                    break;
                case 'lately7':
                    $query->whereBetween($timeKey, [strtotime("-7 day"), time()]);
                    break;
                case 'lately30':
                    $query->whereBetween($timeKey, [strtotime("-30 day"), time()]);
                    break;
                default:
                    if (strstr($time, '-') !== false) {
                        [$startTime, $endTime] = explode('-', $time);
                        $startTime = trim($startTime);
                        $endTime = trim($endTime);
                        if ($startTime && $endTime) {
                            $query->whereBetween($timeKey, [strtotime($startTime), $startTime == $endTime ? strtotime($endTime) + 86400 : strtotime($endTime)]);
                        } else if (!$startTime && $endTime) {
                            $query->whereTime($timeKey, '<', strtotime($endTime) + 86400);
                        } else if ($startTime && !$endTime) {
                            $query->whereTime($timeKey, '>=', strtotime($startTime));
                        }
                    }
                    break;
            }
        })->where($where)->group('u.uid')->count();
    }

    /**
     * 获取本季度 time
     * @param int $ceil
     * @return array
     */
    public function getMonth(int $ceil = 0)
    {
        if ($ceil != 0) {
            $season = ceil(date('n') / 3) - $ceil;
        } else {
            $season = ceil(date('n') / 3);
        }
        $firstday = date('Y-m-01', mktime(0, 0, 0, ($season - 1) * 3 + 1, 1, date('Y')));
        $lastday = date('Y-m-t', mktime(0, 0, 0, $season * 3, 1, date('Y')));
        return [$firstday, $lastday];
    }
}
