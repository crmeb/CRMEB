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

namespace app\model\system\admin;

use crmeb\basic\BaseModel;
use crmeb\traits\JwtAuthModelTrait;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * 管理员模型
 * Class SystemAdmin
 * @package app\model\system\admin
 */
class SystemAdmin extends BaseModel
{
    use ModelTrait;
    use JwtAuthModelTrait;

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'system_admin';

    protected $insert = ['add_time'];

    /**
     * 权限数据
     * @param $value
     * @return false|string[]
     */
    public static function getRolesAttr($value)
    {
        return explode(',', $value);
    }

    /**
     * 管理员级别搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchLevelAttr($query, $value)
    {
        if (is_array($value)) {
            $query->where('level', $value[0], $value[1]);
        } else {
            $query->where('level', $value);
        }
    }

    /**
     * 管理员账号和姓名搜索器
     * @param Model $query
     * @param $value
     */
    public function searchAccountLikeAttr($query, $value)
    {
        if ($value) {
            $query->whereLike('account|real_name', '%' . $value . '%');
        }
    }

    /**
     * 管理员账号搜索器
     * @param Model $query
     * @param $value
     */
    public function searchAccountAttr($query, $value)
    {
        if ($value) {
            $query->where('account', $value);
        }
    }

    /**
     * 管理员权限搜索器
     * @param Model $query
     * @param $roles
     */
    public function searchRolesAttr($query, $roles)
    {
        if ($roles) {
            $query->where("CONCAT(',',roles,',')  LIKE '%,$roles,%'");
        }
    }

    /**
     * 是否删除搜索器
     * @param Model $query
     * @param $value
     */
    public function searchIsDelAttr($query)
    {
        $query->where('is_del', 0);
    }

    /**
     * 状态搜索器
     * @param Model $query
     * @param $value
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value != '' && $value != null) {
            $query->where('status', $value);
        }
    }

}
