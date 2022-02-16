<?php


namespace app\model\agent;


use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class DivisionAgentApply extends BaseModel
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
    protected $name = 'division_agent_apply';

    /**
     * uid
     * @param $query
     * @param $value
     */
    public function searchUidAttr($query, $value)
    {
        if ($value != '') $query->where('uid', $value);
    }

    /**
     * division_id
     * @param $query
     * @param $value
     */
    public function searchDivisionIdAttr($query, $value)
    {
        if ((int)$value !== 0) $query->where('division_id', $value);
    }

    /**
     * division_invite
     * @param $query
     * @param $value
     */
    public function searchDivisionInviteAttr($query, $value)
    {
        if ($value != '') $query->where('division_invite', $value);
    }

    /**
     * status
     * @param $query
     * @param $value
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value !== '' && $value !== 'all') $query->where('status', $value);
    }

    /**
     * @param $query
     * @param $value
     */
    public function searchKeywordAttr($query, $value)
    {
        if ($value !== '') $query->where('uid|agent_name', 'like', '%' . $value . '%');
    }

    /**
     * is_del
     * @param $query
     * @param $value
     */
    public function searchIsDelAttr($query, $value)
    {
        if ($value !== '') $query->where('is_del', $value);
    }
}
