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

namespace app\model\system\config;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * 组合数据配置模型
 * Class SystemGroup
 * @package app\model\system\config
 */
class SystemGroup extends BaseModel
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
    protected $name = 'system_group';

    /**
     * 配置名搜索器
     * @param Model $query
     * @param $value
     */
    public function searchConfigNameAttr($query, $value)
    {
        $query->where('config_name', $value);
    }

    /**
     * @param Model $query
     * @param $value
     */
    public function searchTitleAttr($query, $value)
    {
        if ($value != '') {
            $query->whereLIke('id|name|info|config_name', "%$value%");
        }
    }

    /**
     * 查询分类
     * @param Model $query
     * @param $value
     */
    public function searchCateIdAttr($query, $value)
    {
        $query->where('cate_id', $value);
    }
}
