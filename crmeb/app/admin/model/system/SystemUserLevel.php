<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/13
 */

namespace app\admin\model\system;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * 设置会员vip model
 * Class SystemVip
 * @package app\admin\model\system
 */
class SystemUserLevel extends BaseModel
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
    protected $name = 'system_user_level';

    use ModelTrait;

    public static function setAddTimeAttr()
    {
        return time();
    }
    public static function getAddTimeAttr($value)
    {
        return date('Y-m-d H:i:s',$value);
    }

    /**
     * 获取查询条件
     * @param $where
     * @param string $alert
     * @param null $model
     * @return SystemUserLevel|null
     */
    public static function setWhere($where,$alert='',$model=null)
    {
        $model=$model===null ? new self() : $model;
        if($alert) $model=$model->alias($alert);
        $alert=$alert ? $alert.'.': '';
        $model = $model->where("{$alert}is_del",0);
        if(isset($where['is_show']) && $where['is_show']!=='') $model=$model->where("{$alert}is_show",$where['is_show']);
        if(isset($where['title']) && $where['title']) $model=$model->where("{$alert}name",'LIKE',"%$where[title]%");
        return $model;
    }

    /**
     * 查找系统设置的会员等级列表
     * @param $where
     * @return array
     */
    public static function getSytemList($where)
    {
        $data=self::setWhere($where)->order('grade asc')->page((int)$where['page'],(int)$where['limit'])->select();
        $data=count($data) ? $data->toArray() : [];
        $count=self::setWhere($where)->count();
        return compact('data','count');
    }

}