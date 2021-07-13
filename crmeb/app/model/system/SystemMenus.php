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

namespace app\model\system;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * 菜单规则模型
 * Class SystemMenus
 * @package app\model\system
 */
class SystemMenus extends BaseModel
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
    protected $name = 'system_menus';

    /**
     * 参数修改器
     * @param $value
     * @return false|string
     */
    public function setParamsAttr($value)
    {
        $value = $value ? explode('/', $value) : [];
        $params = array_chunk($value, 2);
        $data = [];
        foreach ($params as $param) {
            if (isset($param[0]) && isset($param[1])) $data[$param[0]] = $param[1];
        }
        return json_encode($data);
    }

    /**
     * 控制器修改器
     * @param $value
     * @return string
     */
    protected function setControllerAttr($value)
    {
        return lcfirst($value);
    }

    /**
     * 参数获取器
     * @param $_value
     * @return mixed
     */
    public function getParamsAttr($_value)
    {
        return json_decode($_value, true);
    }

    /**
     * pid获取器
     * @param $value
     * @return mixed|string
     */
    public function getPidAttr($value)
    {
        return !$value ? '顶级' : $this->where('pid', $value)->value('menu_name');
    }

    /**
     * 默认条件查询器
     * @param Model $query
     * @param $value
     */
    public function searchDefaultAttr($query)
    {
        $query->where(['is_show' => 1, 'access' => 1]);
    }

    /**
     * 是否显示搜索器
     * @param Model $query
     * @param $value
     */
    public function searchIsShowAttr($query, $value)
    {
        if ($value != '') {
            $query->where('is_show', $value);
        }
    }

    /**
     * 是否删除搜索器
     * @param Model $query
     * @param $value
     */
    public function searchIsDelAttr($query, $value)
    {
        $query->where('is_del', $value);
    }

    /**
     * Pid搜索器
     * @param Model $query
     * @param $value
     */
    public function searchPidAttr($query, $value)
    {
        $query->where('pid', $value ?? 0);
    }

    /**
     * 规格搜索器
     * @param Model $query
     * @param $value
     */
    public function searchRuleAttr($query, $value)
    {
        $query->whereIn('id', $value)->where('is_del', 0)->whereOr('pid', 0);
    }

    /**
     * 搜索菜单
     * @param Model $query
     * @param $value
     */
    public function searchKeywordAttr($query, $value)
    {
        if ($value != '') {
            $query->whereLike('menu_name|id|pid', "%$value%");
        }
    }

    /**
     * 方法搜索器
     * @param Model $query
     * @param $value
     */
    public function searchActionAttr($query, $value)
    {
        $query->where('action', $value);
    }

    /**
     * 控制器搜索器
     * @param Model $query
     * @param $value
     */
    public function searchControllerAttr($query, $value)
    {
        $query->where('controller', lcfirst($value));
    }

    /**
     * 访问地址搜索器
     * @param Model $query
     * @param $value
     */
    public function searchUrlAttr($query, $value)
    {
        $query->where('api_url', $value);
    }

    /**
     * 参数搜索器
     * @param Model $query
     * @param $value
     */
    public function searchParamsAttr($query, $value)
    {
        $query->where(function ($query) use ($value) {
            $query->where('params', $value)->whereOr('params', "'[]'");
        });
    }

    /**
     * 权限标识搜索器
     * @param Model $query
     * @param $value
     */
    public function searchUniqueAttr($query, $value)
    {
        $query->where('is_del', 0);
        if ($value) {
            $query->whereIn('id', $value);
        }
    }

    /**
     * 菜单规格搜索
     * @param Model $query
     * @param $value
     */
    public function searchRouteAttr($query, $value)
    {
        $query->where('auth_type', 1)->where('is_show', 1)->where('is_del', 0);
        if ($value) {
            $query->whereIn('id', $value);
        }
    }

    /**
     * Id搜索器
     * @param Model $query
     * @param $value
     */
    public function searchIdAttr($query, $value)
    {
        $query->whereIn('id', $value);
    }

    /**
     * is_show_path
     * @param Model $query
     * @param $value
     */
    public function searchIsShowPathAttr($query, $value)
    {
        $query->where('is_show_path', $value);
    }

    /**
     * auth_type
     * @param Model $query
     * @param $value
     */
    public function searchAuthTypeAttr($query, $value)
    {
        $query->where('auth_type', $value);
    }
}
