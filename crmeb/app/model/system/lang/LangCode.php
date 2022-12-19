<?php

namespace app\model\system\lang;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class LangCode extends BaseModel
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
    protected $name = 'lang_code';

    /**
     * type_id搜索器
     * @param $query
     * @param $value
     */
    public function searchTypeIdAttr($query, $value)
    {
        if ($value !== '' && $value !== 0) $query->where('type_id', $value);
    }

    /**
     * code搜索器
     * @param $query
     * @param $value
     */
    public function searchCodeAttr($query, $value)
    {
        if ($value !== '') $query->where('code', 'like', '%' . $value . '%');
    }

    /**
     * remarks搜索器
     * @param $query
     * @param $value
     */
    public function searchRemarksAttr($query, $value)
    {
        if ($value !== '') $query->where('remarks|code|lang_explain', 'like', '%' . $value . '%');
    }

    /**
     * is_admin搜索器
     * @param $query
     * @param $value
     */
    public function searchIsAdminAttr($query, $value)
    {
        if ($value !== '') $query->where('is_admin', $value);
    }
}