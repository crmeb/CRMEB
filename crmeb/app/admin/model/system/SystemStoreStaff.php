<?php


namespace app\admin\model\system;

use app\admin\model\user\User;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use crmeb\services\PHPExcelService;

/**
 * 店员 model
 * Class SystemStore
 * @package app\admin\model\system
 */
class SystemStoreStaff extends BaseModel
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
    protected $name = 'system_store_staff';

    protected function getAddTimeAttr($value)
    {
        if ($value) $value = date('Y-m-d H:i:s', $value);
        return $value;
    }

    /**
     * 获取门店列表
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function lst($where)
    {
        $model = self::page((int)$where['page'], (int)$where['limit']);
        if (isset($where['store_id']) && $where['store_id'] != '') {
            $model = $model->where('store_id', $where['store_id']);
        }
//        if (isset($where['type']) && $where['type'] != '' && ($data = self::setData($where['type']))) {
//            $model = $model->where($data);
//        }
        $model = $model->alias('a')
            ->join('wechat_user u', 'u.uid=a.uid')
            ->join('system_store s', 'a.store_id = s.id')
            ->field('a.id,u.nickname,a.avatar,a.staff_name,a.status,a.add_time,s.name');
        $data = $model->select();
        $count = $data->count();
        return compact('count', 'data');
    }

    /**
     * 设置查找店员条件
     * @param array $where
     * @return $this
     */
    public static function staffWhere(array $where = [])
    {
        $model = User::where('uid', 'not in', function ($query) {
            $query->name('system_store_staff')->field('uid')->select();
        });
        if (isset($where['nickname']) && $where['nickname']) {
            $model->where('nickname|phone', 'like', "%$where[nickname]%");
        }
        return $model;
    }

    /**
     * 获取选择的商城用户
     */
    public static function getUserList($page = 1, $limit = 10, $nickname = '')
    {
        $list = self::staffWhere(['nickname' => $nickname])->page($page, $limit)->select();
        $count = self::staffWhere(['nickname' => $nickname])->count();
        return ['data' => $list, 'count' => $count];
    }

}