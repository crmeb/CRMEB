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
 * 分销员等级
 * Class AgentLevel
 * @package app\model\agent
 */
class AgentLevel extends BaseModel
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
    protected $name = 'agent_level';

    /**
     * 关联等级任务
     * @return \think\model\relation\HasMany
     */
    public function task()
    {
        return $this->hasMany(AgentLevelTask::class, 'level_id', 'id')->where('is_del', 0);
    }

    /**
     * 关键词搜索
     * @param $query
     * @param $value
     */
    public function searchKeywordAttr($query, $value)
    {
        if ($value !== '') $query->whereLike('id|name', "%" . trim($value) . "%");
    }

    /**
     * 等级搜索器
     * @param $query Model
     * @param $value
     */
    public function searchGradeAttr($query, $value)
    {
        if ($value !== '') $query->where('grade', $value);
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

    /**
     * 是否删除搜索器
     * @param $query Model
     * @param $value
     */
    public function searchIsDelAttr($query, $value)
    {
        if ($value !== '') $query->where('is_del', $value);
    }
}
