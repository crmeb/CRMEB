<?php
/**
 *
 * @author: wuhaotian<442384644@qq.com>
 * @day: 2019/12/07
 */

namespace app\admin\model\user;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * Class UserGroup
 * @package app\admin\model\user
 */
class UserGroup extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'user_group';

    use ModelTrait;

    /**
     * @param $where
     * @return array
     */
    public static function getList($where)
    {

        $data = self::page((int)$where['page'], (int)$where['limit'])->select();
        $count = $data->count();
        return compact('count', 'data');
    }
}