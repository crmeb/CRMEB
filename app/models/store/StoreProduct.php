<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/12
 */

namespace app\models\store;

use app\admin\model\store\StoreProductAttrValue as StoreProductAttrValueModel;
use app\models\system\SystemUserLevel;
use app\models\user\UserLevel;
use crmeb\basic\BaseModel;
use crmeb\services\SystemConfigService;
use crmeb\services\workerman\ChannelService;
use crmeb\traits\ModelTrait;
use think\facade\Db;

/**
 * TODO 产品Model
 * Class StoreProduct
 * @package app\models\store
 */
class StoreProduct extends BaseModel
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
    protected $name = 'store_product';

    use  ModelTrait;

    protected function getSliderImageAttr($value)
    {
        $sliderImage = json_decode($value, true) ?: [];
        foreach ($sliderImage as &$item) {
            $item = str_replace('\\', '/', $item);
        }
        return $sliderImage;
    }

    protected function getImageAttr($value)
    {
        return str_replace('\\', '/', $value);
    }

    public static function getValidProduct($productId, $field = 'add_time,browse,cate_id,code_path,cost,description,ficti,give_integral,id,image,is_bargain,is_benefit,is_best,is_del,is_hot,is_new,is_postage,is_seckill,is_show,keyword,mer_id,mer_use,ot_price,postage,price,sales,slider_image,sort,stock,store_info,store_name,unit_name,vip_price,IFNULL(sales,0) + IFNULL(ficti,0) as fsales')
    {
        $Product = self::where('is_del', 0)->where('is_show', 1)->where('id', $productId)->field($field)->find();
        if ($Product) return $Product->toArray();
        else return false;
    }

    public static function validWhere()
    {
        return self::where('is_del', 0)->where('is_show', 1)->where('mer_id', 0);
    }

    public static function getProductList($data, $uid)
    {
        $sId = $data['sid'];
        $cId = $data['cid'];
        $keyword = $data['keyword'];
        $priceOrder = $data['priceOrder'];
        $salesOrder = $data['salesOrder'];
        $news = $data['news'];
        $page = $data['page'];
        $limit = $data['limit'];
        $model = self::validWhere();
        if ($sId) {
            $product_ids = Db::name('store_product_cate')->where('cate_id', $sId)->column('product_id');
            if (count($product_ids))
                $model->where('id', 'in', $product_ids);
            else
                $model->where('cate_id', -1);
        } elseif ($cId) {
            $sids = StoreCategory::pidBySidList($cId) ?: [];
            if ($sids) {
                $sidsr = [];
                foreach ($sids as $v) {
                    $sidsr[] = $v['id'];
                }
                $model->where('cate_id', 'IN', $sidsr);
            }
        }
        if (!empty($keyword)) $model->where('keyword|store_name', 'LIKE', htmlspecialchars("%$keyword%"));
        if ($news != 0) $model->where('is_new', 1);
        $baseOrder = '';
        if ($priceOrder) $baseOrder = $priceOrder == 'desc' ? 'price DESC' : 'price ASC';
//        if($salesOrder) $baseOrder = $salesOrder == 'desc' ? 'sales DESC' : 'sales ASC';//真实销量
        if ($salesOrder) $baseOrder = $salesOrder == 'desc' ? 'sales DESC' : 'sales ASC';//虚拟销量
        if ($baseOrder) $baseOrder .= ', ';
        $model->order($baseOrder . 'sort DESC, add_time DESC');
        $list = $model->page((int)$page, (int)$limit)->field('id,store_name,cate_id,image,IFNULL(sales,0) + IFNULL(ficti,0) as sales,price,stock')->select();
        $list = count($list) ? $list->toArray() : [];
        return self::setLevelPrice($list, $uid);
    }

    /*
     * 分类搜索
     * @param string $value
     * @return array
     * */
    public static function getSearchStorePage($keyword, $page, $limit, $uid, $cutApart = [' ', ',', '-'])
    {
        $model = self::validWhere();
        $keyword = trim($keyword);
        if (strlen($keyword)) {
            $cut = false;
            foreach ($cutApart as $val) {
                if (strstr($keyword, $val) !== false) {
                    $cut = $val;
                    break;
                }
            }
            if ($cut !== false) {
                $keywordArray = explode($cut, $keyword);
                $sql = [];
                foreach ($keywordArray as $item) {
                    $sql[] = '(`store_name` LIKE "%' . $item . '%"  OR `keyword` LIKE "%' . $item . '%")';
                }
                $model = $model->where(implode(' OR ', $sql));
            } else {
                $model = $model->where('store_name|keyword', 'LIKE', "%$keyword%");
            }
        }
        $list = $model->field('id,store_name,cate_id,image,ficti as sales,price,stock')->page($page, $limit)->select();
        return self::setLevelPrice($list, $uid);
    }

    /**
     * 新品产品
     * @param string $field
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getNewProduct($field = '*', $limit = 0, $uid = 0)
    {
        $model = self::where('is_new', 1)->where('is_del', 0)->where('mer_id', 0)
            ->where('stock', '>', 0)->where('is_show', 1)->field($field)
            ->order('sort DESC, id DESC');
        if ($limit) $model->limit($limit);
        $list = $model->select();
        $list = count($list) ? $list->toArray() : [];
        return self::setLevelPrice($list, $uid);
    }

    /**
     * 热卖产品
     * @param string $field
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getHotProduct($field = '*', $limit = 0, $uid = 0)
    {
        $model = self::where('is_hot', 1)->where('is_del', 0)->where('mer_id', 0)
            ->where('stock', '>', 0)->where('is_show', 1)->field($field)
            ->order('sort DESC, id DESC');
        if ($limit) $model->limit($limit);
        return self::setLevelPrice($model->select(), $uid);
    }

    /**
     * 热卖产品
     * @param string $field
     * @param int $page
     * @param int $limit
     * @return array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getHotProductLoading($field = '*', $page = 0, $limit = 0)
    {
        if(!$limit) return  [];
        $model = self::where('is_hot', 1)->where('is_del', 0)->where('mer_id', 0)
            ->where('stock', '>', 0)->where('is_show', 1)->field($field)
            ->order('sort DESC, id DESC');
        if($page) $model->page($page, $limit);
        $list = $model->select();
        if (is_object($list)) return $list->toArray();
        return $list;
    }

    /**
     * 精品产品
     * @param string $field
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getBestProduct($field = '*', $limit = 0, $uid = 0)
    {
        $model = self::where('is_best', 1)->where('is_del', 0)->where('mer_id', 0)
            ->where('stock', '>', 0)->where('is_show', 1)->field($field)
            ->order('sort DESC, id DESC');
        if ($limit) $model->limit($limit);
        return self::setLevelPrice($model->select(), $uid);
    }

    /*
     * 设置会员价格
     * @param object | array $list 产品列表
     * @param int $uid 用户uid
     * @return array
     * */
    public static function setLevelPrice($list, $uid, $isSingle = false)
    {
        if (is_object($list)) $list = count($list) ? $list->toArray() : [];
        if (!SystemConfigService::get('vip_open')) {
            if (is_array($list)) return $list;
            return $isSingle ? $list : 0;
        }
        $levelId = UserLevel::getUserLevel($uid);
        if ($levelId) {
            $discount = UserLevel::getUserLevelInfo($levelId, 'discount');
            $discount = bcsub(1, bcdiv($discount, 100, 2), 2);
        } else {
            $discount = SystemUserLevel::getLevelDiscount();
            $discount = bcsub(1, bcdiv($discount, 100, 2), 2);
        }
        //如果不是数组直接执行减去会员优惠金额
        if (!is_array($list))
            //不是会员原价返回
            if ($levelId)
                //如果$isSingle==true 返回优惠后的总金额，否则返回优惠的金额
                return $isSingle ? bcsub($list, bcmul($discount, $list, 2), 2) : bcmul($discount, $list, 2);
            else
                return $isSingle ? $list : 0;
        //当$list为数组时$isSingle==true为一维数组 ，否则为二维
        if ($isSingle)
            $list['vip_price'] = isset($list['price']) ? bcsub($list['price'], bcmul($discount, $list['price'], 2), 2) : 0;
        else
            foreach ($list as &$item) {
                $item['vip_price'] = isset($item['price']) ? bcsub($item['price'], bcmul($discount, $item['price'], 2), 2) : 0;
            }
        return $list;
    }


    /**
     * 优惠产品
     * @param string $field
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getBenefitProduct($field = '*', $limit = 0)
    {
        $model = self::where('is_benefit', 1)
            ->where('is_del', 0)->where('mer_id', 0)->where('stock', '>', 0)
            ->where('is_show', 1)->field($field)
            ->order('sort DESC, id DESC');
        if ($limit) $model->limit($limit);
        return $model->select();
    }

    public static function cateIdBySimilarityProduct($cateId, $field = '*', $limit = 0)
    {
        $pid = StoreCategory::cateIdByPid($cateId) ?: $cateId;
        $cateList = StoreCategory::pidByCategory($pid, 'id') ?: [];
        $cid = [$pid];
        foreach ($cateList as $cate) {
            $cid[] = $cate['id'];
        }
        $model = self::where('cate_id', 'IN', $cid)->where('is_show', 1)->where('is_del', 0)
            ->field($field)->order('sort DESC,id DESC');
        if ($limit) $model->limit($limit);
        return $model->select();
    }

    public static function isValidProduct($productId)
    {
        return self::be(['id' => $productId, 'is_del' => 0, 'is_show' => 1]) > 0;
    }

    public static function getProductStock($productId, $uniqueId = '')
    {
        return $uniqueId == '' ?
            self::where('id', $productId)->value('stock') ?: 0
            : StoreProductAttr::uniqueByStock($uniqueId);
    }

    public static function decProductStock($num, $productId, $unique = '')
    {
        if ($unique) {
            $res = false !== StoreProductAttrValueModel::decProductAttrStock($productId, $unique, $num);
            $res = $res && self::where('id', $productId)->inc('sales', $num)->update();
        } else {
            $res = false !== self::where('id', $productId)->dec('stock', $num)->inc('sales', $num)->update();
        }
        if($res){
            $stock = self::where('id', $productId)->value('stock');
            $replenishment_num = SystemConfigService::get('store_stock') ?? 0;//库存预警界限
            if($replenishment_num >= $stock){
                ChannelService::instance()->send('STORE_STOCK', ['id'=>$productId]);
            }
        }
        return $res;
    }

    /*
     * 减少销量,增加库存
     * @param int $num 增加库存数量
     * @param int $productId 产品id
     * @param string $unique 属性唯一值
     * @return boolean
     * */
    public static function incProductStock($num, $productId, $unique = '')
    {
        $product = self::where('id', $productId)->field(['sales', 'stock'])->find();
        if (!$product) return true;
        if ($product->sales > 0) $product->sales = bcsub($product->sales, $num, 0);
        if ($product->sales < 0) $product->sales = 0;
        if ($unique) {
            $res = false !== StoreProductAttrValueModel::incProductAttrStock($productId, $unique, $num);
            //没有修改销量则直接返回
            if ($product->sales == 0) return true;
            $res = $res && $product->save();
        } else {
            $product->stock = bcadd($product->stock, $num, 0);
            $res = false !== $product->save();
        }
        return $res;
    }

    public static function getPacketPrice($storeInfo, $productValue)
    {
        $store_brokerage_ratio = SystemConfigService::get('store_brokerage_ratio');
        $store_brokerage_ratio = bcdiv($store_brokerage_ratio, 100, 2);
        if (count($productValue)) {
            $Maxkey = self::getArrayMax($productValue, 'price');
            $Minkey = self::getArrayMin($productValue, 'price');

            if (isset($productValue[$Maxkey])) {
                $value = $productValue[$Maxkey];
                if ($value['cost'] > $value['price'])
                    $maxPrice = 0;
                else
                    $maxPrice = bcmul($store_brokerage_ratio, bcsub($value['price'], $value['cost']), 0);
                unset($value);
            } else $maxPrice = 0;

            if (isset($productValue[$Minkey])) {
                $value = $productValue[$Minkey];
                if ($value['cost'] > $value['price'])
                    $minPrice = 0;
                else
                    $minPrice = bcmul($store_brokerage_ratio, bcsub($value['price'], $value['cost']), 0);
                unset($value);
            } else $minPrice = 0;
            if ($minPrice == 0 && $maxPrice == 0)
                return 0;
            else if ($minPrice == 0 && $maxPrice)
                return $maxPrice;
            else if ($maxPrice == 0 && $minPrice)
                return $minPrice;
            else
                return $minPrice . '~' . $maxPrice;
        } else {
            if ($storeInfo['cost'] < $storeInfo['price'])
                return bcmul($store_brokerage_ratio, bcsub($storeInfo['price'], $storeInfo['cost']), 2);
            else
                return 0;
        }
    }

    /*
     * 获取二维数组中最大的值
     * */
    public static function getArrayMax($arr, $field)
    {
        $temp = [];
        foreach ($arr as $k => $v) {
            $temp[] = $v[$field];
        }
        $maxNumber = max($temp);
        foreach ($arr as $k => $v) {
            if ($maxNumber == $v[$field]) return $k;
        }
        return 0;
    }

    /*
     * 获取二维数组中最小的值
     * */
    public static function getArrayMin($arr, $field)
    {
        $temp = [];
        foreach ($arr as $k => $v) {
            $temp[] = $v[$field];
        }
        $minNumber = min($temp);
        foreach ($arr as $k => $v) {
            if ($minNumber == $v[$field]) return $k;
        }
        return 0;
    }

    /**
     * 产品名称 图片
     * @param array $productIds
     * @return array
     */
    public static function getProductStoreNameOrImage(array $productIds)
    {
        return self::whereIn('id', $productIds)->column('store_name,image', 'id');
    }
}