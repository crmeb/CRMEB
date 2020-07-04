<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\models\store;

use app\admin\model\store\StoreProductAttrValue;
use app\admin\model\store\StoreProductAttrValue as StoreProductAttrValueModel;
use crmeb\services\SystemConfigService;
use crmeb\services\UtilService;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use app\models\store\StoreProduct;

/**
 * TODO 拼团产品Model
 * Class StoreCombination
 * @package app\models\store
 */
class StoreCombination extends BaseModel
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
    protected $name = 'store_combination';

    use ModelTrait;

    public function getDescriptionAttr($value)
    {
        return htmlspecialchars_decode($value);
    }

    /**
     * @param $where
     * @return array
     */
    public static function get_list($length = 10)
    {
        if ($post = input('post.')) {
            $where = $post['where'];
            $model = new self();
            $model = $model->alias('c');
            $model = $model->join('StoreProduct s', 's.id=c.product_id');
            $model = $model->where('c.is_show', 1)->where('c.is_del', 0)->where('c.start_time', '<', time())->where('c.stop_time', '>', time());
            if (!empty($where['search'])) {
                $model = $model->where('c.title', 'like', "%{$where['search']}%");
                $model = $model->whereOr('s.keyword', 'like', "{$where['search']}%");
            }
            $model = $model->field('c.*,s.price as product_price');
            if ($where['key']) {
                if ($where['sales'] == 1) {
                    $model = $model->order('c.sales desc');
                } else if ($where['sales'] == 2) {
                    $model = $model->order('c.sales asc');
                }
                if ($where['price'] == 1) {
                    $model = $model->order('c.price desc');
                } else if ($where['price'] == 2) {
                    $model = $model->order('c.price asc');
                }
                if ($where['people'] == 1) {
                    $model = $model->order('c.people asc');
                }
                if ($where['default'] == 1) {
                    $model = $model->order('c.sort desc,c.id desc');
                }
            } else {
                $model = $model->order('c.sort desc,c.id desc');
            }
            $page = is_string($where['page']) ? (int)$where['page'] + 1 : $where['page'] + 1;
            $list = $model->page($page, $length)->select()->toArray();
            return ['list' => $list, 'page' => $page];
        }
    }

    /**
     * 获取拼团数据
     * @param int $page
     * @param int $limit
     * @return mixed
     */
    public static function getAll($page = 0, $limit = 20)
    {
        $model = new self();
        $model = $model->alias('c');
        $model = $model->join('StoreProduct s', 's.id=c.product_id');
        $model = $model->field('c.*,s.price as product_price');
        $model = $model->order('c.sort desc,c.id desc');
        $model = $model->where('c.is_show', 1);
        $model = $model->where('c.is_del', 0);
        $model = $model->where('c.start_time', '<', time());
        $model = $model->where('c.stop_time', '>', time());
        if ($page) $model = $model->page($page, $limit);
        return $model->select()->each(function ($item) {
            $item['image'] = set_file_url($item['image']);
        });
    }

    /**
     * 获取是否有拼团产品
     * */
    public static function getPinkIsOpen()
    {
        return self::alias('c')->join('StoreProduct s', 's.id=c.product_id')->where('c.is_show', 1)->where('c.is_del', 0)
            ->where('c.start_time', '<', time())->where('c.stop_time', '>', time())->count();
    }

    /**
     * 获取一条拼团数据
     * @param $id
     * @return mixed
     */
    public static function getCombinationOne($id)
    {
        $model = new self();
        $model = $model->alias('c');
        $model = $model->join('StoreProduct s', 's.id=c.product_id');
        $model = $model->field('c.*,s.price as product_price,SUM(s.sales+s.ficti) as total');
        $model = $model->where('c.is_show', 1);
        $model = $model->where('c.is_del', 0);
        $model = $model->where('c.id', $id);
        $model = $model->where('c.start_time', '<', time());
        $model = $model->where('c.stop_time', '>', time() - 86400);
        $info = $model->find();
        if ($info['id']) {
            return $info;
        } else {
            return [];
        }
    }

    /**
     * 获取推荐的拼团产品
     * @return mixed
     */
    public static function getCombinationHost($limit = 0)
    {
        $model = new self();
        $model = $model->alias('c');
        $model = $model->join('StoreProduct s', 's.id=c.product_id');
        $model = $model->field('c.id,c.image,c.price,c.sales,c.title,c.people,s.price as product_price');
        $model = $model->where('c.is_del', 0);
        $model = $model->where('c.is_host', 1);
        $model = $model->where('c.start_time', '<', time());
        $model = $model->where('c.stop_time', '>', time());
        if ($limit) $model = $model->limit($limit);
        return $model->select();
    }

    /**
     * 修改销量和库存
     * @param $num
     * @param $CombinationId
     * @return bool
     */
    public static function decCombinationStock($num, $CombinationId, $unique)
    {
        $product_id = self::where('id', $CombinationId)->value('product_id');
        if ($unique) {
            $res = false !== StoreProductAttrValue::decProductAttrStock($CombinationId, $unique, $num, 3);
            $res = $res && self::where('id', $CombinationId)->dec('stock', $num)->dec('quota', $num)->inc('sales', $num)->update();
            $sku = StoreProductAttrValue::where('product_id', $CombinationId)->where('unique', $unique)->where('type', 3)->value('suk');
            $res = $res && StoreProductAttrValue::where('product_id', $product_id)->where('suk', $sku)->where('type', 0)->dec('stock', $num)->inc('sales', $num)->update();
        } else {
            $res = false !== self::where('id', $CombinationId)->dec('stock', $num)->inc('sales', $num)->update();
        }
        $res = $res && StoreProduct::where('id', $product_id)->dec('stock', $num)->inc('sales', $num)->update();
        return $res;
    }

    /**
     * 增加库存,减少销量
     * @param $num
     * @param $CombinationId
     * @return bool
     */
    public static function incCombinationStock($num, $CombinationId, $unique = '')
    {

        $combination = self::where('id', $CombinationId)->field(['stock', 'sales'])->find();
        if (!$combination) return true;
        if ($combination->sales > 0) $combination->sales = bcsub($combination->sales, $num, 0);
        if ($combination->sales < 0) $combination->sales = 0;
        if ($unique) {
            $res = false !== StoreProductAttrValueModel::incProductAttrStock($CombinationId, $unique, $num, 3);
        }
        $combination->stock = bcadd($combination->stock, $num, 0);
        $res = $res && $combination->save();
        return $res;
    }

    /**
     * 判断库存是否足够
     * @param $id
     * @param $cart_num
     * @return int|mixed
     */
    public static function getCombinationStock($id, $cart_num)
    {
        $stock = self::where('id', $id)->value('stock');
        return $stock > $cart_num ? $stock : 0;
    }

    /**
     * 获取字段值
     * @param $id
     * @param $field
     * @return mixed
     */
    public static function getCombinationField($id, $field = 'title')
    {
        return self::where('id', $id)->value($field);
    }

    /**
     * 获取产品状态
     * @param $id
     * @return mixed
     */
    public static function isValidCombination($id)
    {
        $model = new self();
        $model = $model->where('id', $id);
        $model = $model->where('is_del', 0);
        $model = $model->where('is_show', 1);
        return $model->count();
    }

    /**
     * 增加浏览量
     * @param int $id
     * @return bool
     */
    public static function editIncBrowse($id = 0)
    {
        if (!$id) return false;
        $browse = self::where('id', $id)->value('browse');
        $browse = bcadd($browse, 1, 0);
        self::edit(['browse' => $browse], $id);
    }

}
