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

namespace app\model\agent;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * 分销员完成等级任务记录
 * Class AgentLevelTaskRecord
 * @package app\model\agent
 */
class AgentLevelTaskRecord extends BaseModel
{

    use ModelTrait;

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'agent_level_task_record';

    /**
     * 关联分销员等级
     * @return \think\model\relation\HasOne
     */
    public function level()
    {
        return $this->hasOne(AgentLevel::class, 'id', 'level_id');
    }

    /**
     * 关联分销员等级任务
     * @return \think\model\relation\HasOne
     */
    public function task()
    {
        return $this->hasOne(AgentLevelTask::class, 'id', 'task_id');
    }

    /**
     * 分销员等级搜索器
     * @param $query Model
     * @param $value
     */
    public function searchLevelIdAttr($query, $value)
    {
        if ($value !== '') $query->where('level_id', $value);
    }

    /**
     * 等级任务搜索器
     * @param $query Model
     * @param $value
     */
    public function searchTaskIdAttr($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('task_id', $value);
        } else {
            if ($value !== '') $query->where('task_id', $value);
        }
    }

    /**
     * 用户搜索器
     * @param $query Model
     * @param $value
     */
    public function searchUidAttr($query, $value)
    {
        if ($value !== '') $query->where('uid', $value);
    }

    /**
     * 状态搜索器
     * @param $query Model
     * @param $value
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value !== '') $query->where('status', $value);
    }


}
