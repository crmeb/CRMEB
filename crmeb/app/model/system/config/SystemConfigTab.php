<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
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
 * 配置分类模型
 * Class SystemConfigTab
 * @package app\model\system\config
 */
class SystemConfigTab extends BaseModel
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
    protected $name = 'system_config_tab';

    /**
     * 状态搜索器
     * @param Model $query
     * @param $value
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value != '') {
            $query->where('status', $value);
        }
    }

    /**
     * pid搜索器
     * @param Model $query
     * @param $value
     */
    public function searchPidAttr($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('pid', $value);
        } else {
            $value && $query->where('pid', $value);
        }
    }

    /**
     * 类型搜索器
     * @param Model $query
     * @param $value
     */
    public function searchTypeAttr($query, $value)
    {
        $query->where('status', 1);
        if ($value > -1) {
            $query->where(['type' => $value, 'pid' => 0]);
        }
    }

    /**
     * 分类名称搜索器
     * @param Model $query
     * @param $value
     */
    public function searchTitleAttr($query, $value)
    {
        $query->whereLike('title', '%' . $value . '%');
    }

}
