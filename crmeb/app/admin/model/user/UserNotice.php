<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\user;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * 用户通知 model
 * Class UserNotice
 * @package app\admin\model\user
 */
class UserNotice extends BaseModel
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
    protected $name = 'user_notice';

    use ModelTrait;

    /**
     * @param array $where
     * @return array
     */
    public static function getList($where = [])
    {
        $model = new self;
        $model->order('id desc');
        if (!empty($where)) {
            $data = ($data = $model->page((int)$where['page'], (int)$where['limit'])->select()) && count($data) ? $data->toArray() : [];
            foreach ($data as &$item) {
                if ($item["uid"] != '') {
                    $uids = explode(",", $item["uid"]);
                    array_splice($uids, 0, 1);
                    array_splice($uids, count($uids) - 1, 1);
                    $item["uid"] = $uids;
                }
                $item['send_time'] = date('Y-m-d H:i:s', $item['send_time']);
            }
            $count = self::count();
            return compact('data', 'count');
        }
        return self::page($model, function ($item, $key) {
            if ($item["uid"] != '') {
                $uids = explode(",", $item["uid"]);
                array_splice($uids, 0, 1);
                array_splice($uids, count($uids) - 1, 1);
                $item["uid"] = $uids;
            }
        });
    }

    /**
     * 获取用户通知
     * @param array $where
     * @return array
     */
    public static function getUserList($where = array())
    {
        $model = new self;
        if (isset($where['title']) && $where['title'] != '') $model = $model->where('title', 'LIKE', "%" . $where['title'] . "%");
        $model = $model->where('type', 2);
//        $model = $model->where('is_send',0);
        $model = $model->order('id desc');
        return self::page($model, $where);
    }
}