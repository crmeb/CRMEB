<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/23
 */

namespace app\models\store;


use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * TODO 客服Model
 * Class StoreService
 * @package app\models\store
 */
class StoreService extends BaseModel
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
    protected $name = 'store_service';

    use ModelTrait;

    /**
     * 获取客服列表
     * @param $page
     * @param $limit
     * @return array
     */
    public static function lst($page, $limit)
    {
//        if(!$page || !$limit) return [];
        $model = new self;
        $model = $model->where('status', 1);
//        $model = $model->page($page, $limit);
        return $model->select();
    }

    /**
     * 获取客服信息
     * @param $uid
     * @param string $field
     * @return array|null|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getServiceInfo($uid, $field = '*')
    {
        return self::where('uid', $uid)->where('status', 1)->field($field)->find();
    }

    /**
     * 判断是否客服
     * @param $uid
     * @return int
     */
    public static function orderServiceStatus($uid)
    {
        return self::where('uid', $uid)->where('status', 1)->where('customer', 1)->count();
    }

    /**
     * 获取接受通知的客服
     *
     * @return array
     */
    public static function getStoreServiceOrderNotice(){
        return self::where('status',1)->where('notify',1)->column('uid','uid');
    }
}