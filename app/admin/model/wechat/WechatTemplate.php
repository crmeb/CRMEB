<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/02
 */
namespace app\admin\model\wechat;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * 微信模板消息model
 * Class WechatTemplate
 * @package app\admin\model\wechat
 */
class WechatTemplate extends BaseModel
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
    protected $name = 'wechat_template';

    use ModelTrait;

    /**
     * 获取系统分页数据   分类
     * @param array $where
     * @return array
     */
    public static function systemPage($where = array()){
        $model = new self;
        if($where['name'] !== '') $model = $model->where('name','LIKE',"%$where[name]%");
        if($where['status'] !== '') $model = $model->where('status',$where['status']);
        return self::page($model);
    }
}