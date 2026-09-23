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
namespace modules\marketing\signin\dao;

use app\dao\system\config\SystemConfigDao;
use crmeb\basic\BaseDao;
use modules\marketing\signin\model\SystemSignReward;

/**
 * 签到奖励规则数据访问对象
 * @author: 吴汐
 * @email: 442384644@qq.com
 * @date: 2023/7/28
 */
class SystemSignRewardDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/7/28
     */
    protected function setModel(): string
    {
        return SystemSignReward::class;
    }

    /**
     * 锁定签到模式配置，串行化奖励规则的查重和保存。
     * @return mixed
     */
    public function lockSignMode()
    {
        return app()->make(SystemConfigDao::class)->getDb()
            ->where('menu_name', 'sign_mode')->field('id,value')->lock(true)->find();
    }

    /**
     * 检查同类型、同天数的奖励是否存在，编辑时排除自身。
     * @param int $type 奖励类型
     * @param int $days 签到天数
     * @param int $excludeId 排除的规则主键
     * @return bool
     */
    public function hasDuplicate(int $type, int $days, int $excludeId = 0): bool
    {
        $query = $this->getDb()->where('type', $type)->where('days', $days);
        if ($excludeId > 0) {
            $query->where('id', '<>', $excludeId);
        }
        return (bool)$query->field('id')->lock(true)->find();
    }

    /**
     * 批量读取日历中实际涉及的连续、累计奖励阈值。
     * @param array $continuousDays 连续签到目标天数
     * @param array $cumulativeDays 累计签到目标天数
     * @return array
     */
    public function getRewardsByDays(array $continuousDays, array $cumulativeDays): array
    {
        if (!$continuousDays && !$cumulativeDays) {
            return [];
        }
        return $this->getDb()->where(function ($query) use ($continuousDays, $cumulativeDays) {
            if ($continuousDays) {
                $query->where(function ($continuousQuery) use ($continuousDays) {
                    $continuousQuery->where('type', 0)->whereIn('days', $continuousDays);
                });
            }
            if ($cumulativeDays) {
                $query->whereOr(function ($cumulativeQuery) use ($cumulativeDays) {
                    $cumulativeQuery->where('type', 1)->whereIn('days', $cumulativeDays);
                });
            }
        })->field('id,type,days,point,exp')->order('id asc')->select()->toArray();
    }

    /**
     * 查询当前天数之后最近的奖励阈值。
     * @param int $type 奖励类型
     * @param int $days 当前签到天数
     * @return int 无后续奖励时返回 0
     */
    public function getNextRewardDays(int $type, int $days): int
    {
        return (int)$this->getDb()->where('type', $type)->where('days', '>', $days)
            ->order('days asc,id asc')->value('days');
    }
}
