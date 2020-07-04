<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\store;

use app\admin\model\user\User;
use app\models\store\StoreOrder;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * 评论管理 model
 * Class StoreProductReply
 * @package app\admin\model\store
 */
class StoreProductReply extends BaseModel
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
    protected $name = 'store_product_reply';

    use ModelTrait;

    protected function getPicsAttr($value)
    {
        return json_decode($value, true);
    }

    /**
     * 设置where条件
     * @param array $where
     * @param string $alias
     * @param object $model
     * */
    public static function valiWhere($where, $alias = '', $joinAlias = '', $model = null)
    {
        $model = is_null($model) ? new self() : $model;
        if ($alias) {
            $model = $model->alias($alias);
            $alias .= '.';
        }
        $joinAlias = $joinAlias ? $joinAlias . '.' : '';
        if (isset($where['title']) && $where['title'] != '') $model = $model->where("{$alias}comment", 'LIKE', "%$where[title]%");
        if (isset($where['is_reply']) && $where['is_reply'] != '') $model = $where['is_reply'] >= 0 ? $model->where("{$alias}is_reply", $where['is_reply']) : $model->where("{$alias}is_reply", '>', 0);
        if (isset($where['producr_id']) && $where['producr_id'] != 0) $model = $model->where($alias . 'product_id', $where['producr_id']);
        if (isset($where['product_name']) && $where['product_name']) $model = $model->where("{$joinAlias}store_name", 'LIKE', "%$where[product_name]%");
        if (isset($where['order_id']) && $where['order_id'] != '') {
            $model = $model->join('store_order o', 'o.id = a.oid')->where('o.order_id', $where['order_id']);
        }
        if (isset($where['nickname']) && $where['nickname']) {
            $model = $model->join('user u', 'u.uid = a.uid')->where('u.nickname', 'like', "%$where[nickname]%");
        }

        if (isset($where['score_type']) && $where['score_type'] != '') {
            switch ((int)$where['score_type']) {
                case 1:
                    $model = $model->where('product_score', 5)->where('service_score', 5);
                    break;
                case 2:
                    $model = $model->where(function ($query) {
                        $query->where('product_score', '<', 3)->whereOr('service_score', '<', 3);
                    });
                    break;
            }
        }
        return $model->where("{$alias}is_del", 0);
    }

    public static function getProductImaesList($where)
    {
        $list = self::valiWhere($where, 'a', 'p')->group('p.id')->join('wechat_user u', 'u.uid=a.uid', 'LEFT')->join("store_product p", 'a.product_id=p.id', 'LEFT')->field(['p.id', 'p.image', 'p.store_name', 'p.price'])->page($where['page'], $where['limit'])->select();
        $list = count($list) ? $list->toArray() : [];
        foreach ($list as &$item) {
            $item['store_name'] = self::getSubstrUTf8($item['store_name'], 10, 'UTF-8', '');
        }

        return $list;
    }

    public static function getProductReplyList($where)
    {
        $data = self::valiWhere($where, 'a', 'p')
            ->join("store_product p", 'a.product_id=p.id', 'left')
            ->order('a.add_time desc,a.is_reply asc')
            ->field(['a.*', 'p.image', 'p.store_name'])
            ->page((int)$where['message_page'], (int)$where['limit'])
            ->select();
        $data = count($data) ? $data->toArray() : [];
        foreach ($data as &$item) {
            $item['_add_time'] = time_tran($item['add_time']);
            $item['order_id'] = StoreOrder::where('id', $item['oid'])->value('order_id');
            if ($item['uid']) {
                $userInfo = User::where('uid', $item['uid'])->field(['avatar', 'nickname'])->find();
                if ($userInfo) {
                    $item['nickname'] = $userInfo['nickname'];
                    $item['avatar'] = $userInfo['avatar'];
                }
            }
        }

        $count = self::valiWhere($where, 'a', 'p')->join("store_product p", 'a.product_id=p.id', 'left')->count();
        return ['list' => $data, 'count' => $count];
    }

    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where)
    {
        $model = new self;
        if ($where['comment'] != '') $model = $model->where('r.comment', 'LIKE', "%$where[comment]%");
        if ($where['is_reply'] != '') {
            if ($where['is_reply'] >= 0) {
                $model = $model->where('r.is_reply', $where['is_reply']);
            } else {
                $model = $model->where('r.is_reply', '>', 0);
            }
        }
        if ($where['product_id']) $model = $model->where('r.product_id', $where['product_id']);
        $model = $model->alias('r')->join('wechat_user u', 'u.uid=r.uid');
        $model = $model->join('store_product p', 'p.id=r.product_id');
        $model = $model->where('r.is_del', 0);
        $model = $model->field('r.*,u.nickname,u.headimgurl,p.store_name');
        $model = $model->order('r.add_time desc,r.is_reply asc');
        return self::page($model, function ($itme) {

        }, $where);
    }

}