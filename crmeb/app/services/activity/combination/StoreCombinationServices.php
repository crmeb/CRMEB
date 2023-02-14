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
declare (strict_types=1);

namespace app\services\activity\combination;

use app\Request;
use app\services\BaseServices;
use app\dao\activity\combination\StoreCombinationDao;
use app\services\order\StoreOrderServices;
use app\services\other\QrcodeServices;
use app\services\product\product\StoreCategoryServices;
use app\services\product\product\StoreDescriptionServices;
use app\services\product\product\StoreProductRelationServices;
use app\services\product\product\StoreProductReplyServices;
use app\services\product\product\StoreProductServices;
use app\services\product\sku\StoreProductAttrResultServices;
use app\services\product\sku\StoreProductAttrServices;
use app\services\product\sku\StoreProductAttrValueServices;
use crmeb\exceptions\AdminException;
use app\jobs\ProductLogJob;
use crmeb\exceptions\ApiException;
use crmeb\services\CacheService;

/**
 *
 * Class StoreCombinationServices
 * @package app\services\activity
 * @method getPinkIdsArray(array $ids, array $field)
 * @method getOne(array $where, ?string $field = '*', array $with = []) 根据条件获取一条数据
 * @method get(int $id, array $field) 获取一条数据
 */
class StoreCombinationServices extends BaseServices
{

    /**
     * StoreCombinationServices constructor.
     * @param StoreCombinationDao $dao
     */
    public function __construct(StoreCombinationDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取指定条件下的条数
     * @param array $where
     */
    public function getCount(array $where)
    {
        $this->dao->count($where);
    }

    /**
     * 获取是否有拼团商品
     * */
    public function validCombination()
    {
        return $this->dao->count([
            'is_del' => 0,
            'is_show' => 1,
            'pinkIngTime' => true
        ]);
    }

    /**
     * 拼团商品添加
     * @param int $id
     * @param array $data
     */
    public function saveData(int $id, array $data)
    {
        $description = $data['description'];
        $detail = $data['attrs'];
        $items = $data['items'];
        $data['start_time'] = strtotime($data['section_time'][0]);
        $data['stop_time'] = strtotime($data['section_time'][1]);
        if ($data['stop_time'] < strtotime(date('Y-m-d', time()))) throw new AdminException(400096);
        $data['image'] = $data['images'][0];
        $data['images'] = json_encode($data['images']);
        $data['price'] = min(array_column($detail, 'price'));
        $data['quota'] = $data['quota_show'] = array_sum(array_column($detail, 'quota'));
        $data['stock'] = array_sum(array_column($detail, 'stock'));
        $data['logistics'] = implode(',', $data['logistics']);
        unset($data['section_time'], $data['description'], $data['attrs'], $data['items']);
        /** @var StoreDescriptionServices $storeDescriptionServices */
        $storeDescriptionServices = app()->make(StoreDescriptionServices::class);
        /** @var StoreProductAttrServices $storeProductAttrServices */
        $storeProductAttrServices = app()->make(StoreProductAttrServices::class);
        /** @var StoreProductServices $storeProductServices */
        $storeProductServices = app()->make(StoreProductServices::class);
        if ($data['quota'] > $storeProductServices->value(['id' => $data['product_id']], 'stock')) {
            throw new AdminException(400090);
        }
        $this->transaction(function () use ($id, $data, $description, $detail, $items, $storeDescriptionServices, $storeProductAttrServices, $storeProductServices) {
            if ($id) {
                $res = $this->dao->update($id, $data);
                $storeDescriptionServices->saveDescription((int)$id, $description, 3);
                $skuList = $storeProductServices->validateProductAttr($items, $detail, (int)$id, 3);
                $valueGroup = $storeProductAttrServices->saveProductAttr($skuList, (int)$id, 3);
                if (!$res) throw new AdminException(100007);
            } else {
                if (!$storeProductServices->getOne(['is_show' => 1, 'is_del' => 0, 'id' => $data['product_id']])) {
                    throw new AdminException(400091);
                }
                $data['add_time'] = time();
                $res = $this->dao->save($data);
                $storeDescriptionServices->saveDescription((int)$res->id, $description, 3);
                $skuList = $storeProductServices->validateProductAttr($items, $detail, (int)$res->id, 3);
                $valueGroup = $storeProductAttrServices->saveProductAttr($skuList, (int)$res->id, 3);
                if (!$res) throw new AdminException(100022);
            }
            $res = true;
            foreach ($valueGroup->toArray() as $item) {
                $res = $res && CacheService::setStock($item['unique'], (int)$item['quota_show'], 3);
            }
            if (!$res) {
                throw new AdminException(400092);
            }
        });
    }

    /**
     * 拼团列表
     * @param array $where
     * @return array
     */
    public function systemPage(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, $page, $limit);
        $count = $this->dao->count($where);
        /** @var StorePinkServices $storePinkServices */
        $storePinkServices = app()->make(StorePinkServices::class);
        $countAll = $storePinkServices->getPinkCount([]);
        $countTeam = $storePinkServices->getPinkCount(['k_id' => 0, 'status' => 2]);
        $countPeople = $storePinkServices->getPinkCount(['k_id' => 0]);
        foreach ($list as &$item) {
            $item['count_people'] = $countPeople[$item['id']] ?? 0;//拼团数量
            $item['count_people_all'] = $countAll[$item['id']] ?? 0;//参与人数
            $item['count_people_pink'] = $countTeam[$item['id']] ?? 0;//成团数量
            $item['stop_status'] = $item['stop_time'] < time() ? 1 : 0;
            if ($item['is_show']) {
                if ($item['start_time'] > time())
                    $item['start_name'] = '未开始';
                else if ($item['stop_time'] < time())
                    $item['start_name'] = '已结束';
                else if ($item['stop_time'] > time() && $item['start_time'] < time()) {
                    $item['start_name'] = '进行中';
                }
            } else $item['start_name'] = '已结束';
        }
        return compact('list', 'count');
    }

    /**
     * 获取详情
     * @param int $id
     * @return array|\think\Model|null
     */
    public function getInfo(int $id)
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new ApiException(410294);
        }
        if ($info->is_del) {
            throw new ApiException(410311);
        }
        if ($info['start_time'])
            $start_time = date('Y-m-d H:i:s', $info['start_time']);

        if ($info['stop_time'])
            $stop_time = date('Y-m-d H:i:s', $info['stop_time']);
        if (isset($start_time) && isset($stop_time))
            $info['section_time'] = [$start_time, $stop_time];
        else
            $info['section_time'] = [];
        unset($info['start_time'], $info['stop_time']);
        $info['price'] = floatval($info['price']);
        $info['postage'] = floatval($info['postage']);
        $info['weight'] = floatval($info['weight']);
        $info['volume'] = floatval($info['volume']);
        $info['logistics'] = explode(',', $info['logistics']);
        /** @var StoreDescriptionServices $storeDescriptionServices */
        $storeDescriptionServices = app()->make(StoreDescriptionServices::class);
        $info['description'] = $storeDescriptionServices->getDescription(['product_id' => $id, 'type' => 3]);
        $info['attrs'] = $this->attrList($id, $info['product_id']);
        return $info;
    }

    /**
     * 获取规格
     * @param int $id
     * @param int $pid
     * @return mixed
     */
    public function attrList(int $id, int $pid)
    {
        /** @var StoreProductAttrResultServices $storeProductAttrResultServices */
        $storeProductAttrResultServices = app()->make(StoreProductAttrResultServices::class);
        $combinationResult = $storeProductAttrResultServices->value(['product_id' => $id, 'type' => 3], 'result');
        $items = json_decode($combinationResult, true)['attr'];
        $productAttr = $this->getAttr($items, $pid, 0);
        $combinationAttr = $this->getAttr($items, $id, 3);
        foreach ($productAttr as $pk => $pv) {
            foreach ($combinationAttr as &$sv) {
                if ($pv['detail'] == $sv['detail']) {
                    $productAttr[$pk] = $sv;
                    $productAttr[$pk]['r_price'] = $pv['price'];
                }
            }
            $productAttr[$pk]['detail'] = json_decode($productAttr[$pk]['detail']);
            $productAttr[$pk]['r_price'] = $productAttr[$pk]['r_price'] ?? $productAttr[$pk]['price'];
        }
        $attrs['items'] = $items;
        $attrs['value'] = $productAttr;
        foreach ($items as $key => $item) {
            $header[] = ['title' => $item['value'], 'key' => 'value' . ($key + 1), 'align' => 'center', 'minWidth' => 80];
        }
        $header[] = ['title' => '图片', 'slot' => 'pic', 'align' => 'center', 'minWidth' => 120];
        $header[] = ['title' => '拼团价', 'slot' => 'price', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '成本价', 'key' => 'cost', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '日常售价', 'key' => 'r_price', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '库存', 'key' => 'stock', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '限量', 'key' => 'quota', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '重量(KG)', 'key' => 'weight', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '体积(m³)', 'key' => 'volume', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '商品编号', 'key' => 'bar_code', 'align' => 'center', 'minWidth' => 80];
        $attrs['header'] = $header;
        return $attrs;
    }

    /**
     * 获得规格
     * @param $attr
     * @param $id
     * @param $type
     * @return array
     */
    public function getAttr($attr, $id, $type)
    {
        /** @var StoreProductAttrValueServices $storeProductAttrValueServices */
        $storeProductAttrValueServices = app()->make(StoreProductAttrValueServices::class);
        list($value, $head) = attr_format($attr);
        $valueNew = [];
        $count = 0;
        foreach ($value as $suk) {
            $detail = explode(',', $suk);
            $sukValue = $storeProductAttrValueServices->getColumn(['product_id' => $id, 'type' => $type, 'suk' => $suk], 'bar_code,cost,price,ot_price,stock,image as pic,weight,volume,brokerage,brokerage_two,quota', 'suk');
            if (count($sukValue)) {
                foreach ($detail as $k => $v) {
                    $valueNew[$count]['value' . ($k + 1)] = $v;
                }
                $valueNew[$count]['detail'] = json_encode(array_combine($head, $detail));
                $valueNew[$count]['pic'] = $sukValue[$suk]['pic'] ?? '';
                $valueNew[$count]['price'] = $sukValue[$suk]['price'] ? floatval($sukValue[$suk]['price']) : 0;
                $valueNew[$count]['cost'] = $sukValue[$suk]['cost'] ? floatval($sukValue[$suk]['cost']) : 0;
                $valueNew[$count]['ot_price'] = isset($sukValue[$suk]['ot_price']) ? floatval($sukValue[$suk]['ot_price']) : 0;
                $valueNew[$count]['stock'] = $sukValue[$suk]['stock'] ? intval($sukValue[$suk]['stock']) : 0;
                $valueNew[$count]['quota'] = $sukValue[$suk]['quota'] ? intval($sukValue[$suk]['quota']) : 0;
                $valueNew[$count]['bar_code'] = $sukValue[$suk]['bar_code'] ?? '';
                $valueNew[$count]['weight'] = $sukValue[$suk]['weight'] ? floatval($sukValue[$suk]['weight']) : 0;
                $valueNew[$count]['volume'] = $sukValue[$suk]['volume'] ? floatval($sukValue[$suk]['volume']) : 0;
                $valueNew[$count]['brokerage'] = $sukValue[$suk]['brokerage'] ? floatval($sukValue[$suk]['brokerage']) : 0;
                $valueNew[$count]['brokerage_two'] = $sukValue[$suk]['brokerage_two'] ? floatval($sukValue[$suk]['brokerage_two']) : 0;
                $valueNew[$count]['_checked'] = $type != 0;
                $count++;
            }
        }
        return $valueNew;
    }

    /**
     * 根据id获取拼团数据列表
     * @param array $ids
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */

    public function getCombinationList()
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->combinationList(['is_del' => 0, 'is_show' => 1, 'pinkIngTime' => true, 'storeProductId' => true], $page, $limit);
        foreach ($list as &$item) {
            $item['image'] = set_file_url($item['image']);
            $item['price'] = floatval($item['price']);
            $item['product_price'] = floatval($item['product_price']);
        }
        return $list;
    }

    /**
     *首页获取拼团数据
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getHomeList($where)
    {
        [$page, $limit] = $this->getPageValue();
        $where['is_del'] = 0;
        $where['is_show'] = 1;
        $where['pinkIngTime'] = true;
        $where['storeProductId'] = true;
        $data = [];
        $list = $this->dao->getHomeList($where, $page, $limit);
        foreach ($list as &$item) {
            $item['image'] = set_file_url($item['image']);
            $item['price'] = floatval($item['price']);
            $item['product_price'] = floatval($item['product_price']);
        }
        $data['list'] = $list;
        return $data;
    }

    /**
     * 后台页面设计获取拼团列表
     * @param $where
     */
    public function getDiyCombinationList($where)
    {
        $where['pinkIngTime'] = true;
        $where['storeProductId'] = true;
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->diyCombinationList($where, $page, $limit);
        $count = $this->dao->getCount($where);
        $cateIds = implode(',', array_column($list, 'cate_id'));
        /** @var StoreCategoryServices $storeCategoryServices */
        $storeCategoryServices = app()->make(StoreCategoryServices::class);
        $cateList = $storeCategoryServices->getCateArray($cateIds);
        foreach ($list as &$item) {
            $cateName = array_filter($cateList, function ($val) use ($item) {
                if (in_array($val['id'], explode(',', $item['cate_id']))) {
                    return $val;
                }
            });
            $item['cate_name'] = implode(',', array_column($cateName, 'cate_name'));
            $item['store_name'] = $item['title'];
            $item['price'] = floatval($item['price']);
            $item['is_product_type'] = 2;
        }
        return compact('count', 'list');
    }

    /**
     * 拼团商品详情
     * @param Request $request
     * @param int $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function combinationDetail(Request $request, int $id)
    {
        $uid = (int)$request->uid();
        $storeInfo = $this->dao->getOne(['id' => $id], '*', ['description', 'total']);
        if (!$storeInfo) {
            throw new ApiException(410311);
        } else {
            $storeInfo = $storeInfo->toArray();
        }

        $siteUrl = sys_config('site_url');
        $storeInfo['image'] = set_file_url($storeInfo['image'], $siteUrl);
        $storeInfo['image_base'] = set_file_url($storeInfo['image'], $siteUrl);
        $storeInfo['sale_stock'] = 0;
        if ($storeInfo['stock'] > 0) $storeInfo['sale_stock'] = 1;
        /** @var StoreProductRelationServices $storeProductRelationServices */
        $storeProductRelationServices = app()->make(StoreProductRelationServices::class);
        $storeInfo['userCollect'] = $storeProductRelationServices->isProductRelation(['uid' => $uid, 'product_id' => $id, 'type' => 'collect', 'category' => 'product']);
        $storeInfo['userLike'] = false;
        $storeInfo['store_name'] = $storeInfo['title'];

        if (sys_config('share_qrcode', 0) && request()->isWechat()) {
            /** @var QrcodeServices $qrcodeService */
            $qrcodeService = app()->make(QrcodeServices::class);
            $storeInfo['wechat_code'] = $qrcodeService->getTemporaryQrcode('combination-' . $id, $uid)->url;
        } else {
            $storeInfo['wechat_code'] = '';
        }

        $data['storeInfo'] = get_thumb_water($storeInfo, 'big', ['image', 'images']);
        $storeInfoNew = get_thumb_water($storeInfo, 'small');
        $data['storeInfo']['small_image'] = $storeInfoNew['image'];

        /** @var StorePinkServices $pinkService */
        $pinkService = app()->make(StorePinkServices::class);
        list($pink, $pindAll) = $pinkService->getPinkList($id, true);//拼团列表
        $data['pink_ok_list'] = $pinkService->getPinkOkList($uid);
        $data['pink_ok_sum'] = $pinkService->getPinkOkSumTotalNum();
        $data['pink'] = $pink;
        $data['pindAll'] = $pindAll;

        /** @var StoreOrderServices $storeOrderServices */
        $storeOrderServices = app()->make(StoreOrderServices::class);
        $data['buy_num'] = $storeOrderServices->getBuyCount($uid, 'combination_id', $id);

        /** @var StoreProductReplyServices $storeProductReplyService */
        $storeProductReplyService = app()->make(StoreProductReplyServices::class);
        $data['reply'] = get_thumb_water($storeProductReplyService->getRecProductReply($storeInfo['product_id']), 'small', ['pics']);
        [$replyCount, $goodReply, $replyChance] = $storeProductReplyService->getProductReplyData((int)$storeInfo['product_id']);
        $data['replyChance'] = $replyChance;
        $data['replyCount'] = $replyCount;

        /** @var StoreProductAttrServices $storeProductAttrServices */
        $storeProductAttrServices = app()->make(StoreProductAttrServices::class);
        list($productAttr, $productValue) = $storeProductAttrServices->getProductAttrDetail($id, $uid, 0, 3, $storeInfo['product_id']);
        $data['productAttr'] = $productAttr;
        $data['productValue'] = $productValue;
        $data['routine_contact_type'] = sys_config('routine_contact_type', 0);

        //用户访问事件
        event('user.userVisit', [$uid, $id, 'combination', $storeInfo['product_id'], 'view']);
        //浏览记录
        ProductLogJob::dispatch(['visit', ['uid' => $uid, 'product_id' => $storeInfo['product_id']]]);
        return $data;
    }

    /**
     * 修改销量和库存
     * @param $num
     * @param $CombinationId
     * @return bool
     */
    public function decCombinationStock(int $num, int $CombinationId, string $unique)
    {
        $product_id = $this->dao->value(['id' => $CombinationId], 'product_id');
        if ($unique) {
            /** @var StoreProductAttrValueServices $skuValueServices */
            $skuValueServices = app()->make(StoreProductAttrValueServices::class);
            //减去拼团商品的sku库存增加销量
            $res = false !== $skuValueServices->decProductAttrStock($CombinationId, $unique, $num, 3);
            //减去拼团库存
            $res = $res && $this->dao->decStockIncSales(['id' => $CombinationId, 'type' => 3], $num);
            //获取拼团的sku
            $sku = $skuValueServices->value(['product_id' => $CombinationId, 'unique' => $unique, 'type' => 3], 'suk');
            //减去当前普通商品sku的库存增加销量
            $res = $res && $skuValueServices->decStockIncSales(['product_id' => $product_id, 'suk' => $sku, 'type' => 0], $num);
        } else {
            $res = false !== $this->dao->decStockIncSales(['id' => $CombinationId, 'type' => 3], $num);
        }
        /** @var StoreProductServices $services */
        $services = app()->make(StoreProductServices::class);
        //减去普通商品库存
        $res = $res && $services->decProductStock($num, $product_id);
        return $res;
    }

    /**
     * 加库存减销量
     * @param int $num
     * @param int $CombinationId
     * @param string $unique
     * @return bool
     */
    public function incCombinationStock(int $num, int $CombinationId, string $unique)
    {
        $product_id = $this->dao->value(['id' => $CombinationId], 'product_id');
        if ($unique) {
            /** @var StoreProductAttrValueServices $skuValueServices */
            $skuValueServices = app()->make(StoreProductAttrValueServices::class);
            //增加拼团商品的sku库存,减去销量
            $res = false !== $skuValueServices->incProductAttrStock($CombinationId, $unique, $num, 3);
            //增加拼团库存
            $res = $res && $this->dao->incStockDecSales(['id' => $CombinationId, 'type' => 3], $num);
            //增加当前普通商品sku的库存,减去销量
            $suk = $skuValueServices->value(['unique' => $unique, 'product_id' => $CombinationId], 'suk');
            $productUnique = $skuValueServices->value(['suk' => $suk, 'product_id' => $product_id], 'unique');
            if ($productUnique) {
                $res = $res && $skuValueServices->incProductAttrStock($product_id, $productUnique, $num);
            }
        } else {
            $res = false !== $this->dao->incStockDecSales(['id' => $CombinationId, 'type' => 3], $num);
        }
        /** @var StoreProductServices $services */
        $services = app()->make(StoreProductServices::class);
        //增加普通商品库存
        $res = $res && $services->incProductStock($num, $product_id);
        return $res;
    }

    /**
     * 获取一条拼团数据
     * @param $id
     * @param $field
     * @return mixed
     */
    public function getCombinationOne($id, $field = '*')
    {
        return $this->dao->validProduct($id, $field);
    }

    /**
     * 获取拼团详情
     * @param Request $request
     * @param int $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getPinkInfo(Request $request, int $id)
    {
        /** @var StorePinkServices $pinkService */
        $pinkService = app()->make(StorePinkServices::class);

        $is_ok = 0;//判断拼团是否完成
        $userBool = 0;//判断当前用户是否在团内  0未在 1在
        $pinkBool = 0;//判断拼团是否成功  0未在 1在
        $user = $request->user();
        if (!$id) throw new ApiException(100100);
        $pink = $pinkService->getPinkUserOne($id);
        if (!$pink) throw new ApiException(100100);
        $pink = $pink->toArray();
        if (isset($pink['is_refund']) && $pink['is_refund']) {
            if ($pink['is_refund'] != $pink['id']) {
                $id = $pink['is_refund'];
                return $this->getPinkInfo($request, $id);
            } else {
                throw new ApiException(410226);
            }
        }
        list($pinkAll, $pinkT, $count, $idAll, $uidAll) = $pinkService->getPinkMemberAndPinkK($pink);
        if ($pinkT['status'] == 2) {
            $pinkBool = 1;
            $is_ok = 1;
        } else if ($pinkT['status'] == 3) {
            $pinkBool = -1;
            $is_ok = 0;
        } else {
            if ($count < 1) {//组团完成
                $is_ok = 1;
                $pinkBool = $pinkService->pinkComplete($uidAll, $idAll, $user['uid'], $pinkT);
            } else {
                $pinkBool = $pinkService->pinkFail($pinkAll, $pinkT, $pinkBool);
            }
        }
        if (!empty($pinkAll)) {
            foreach ($pinkAll as $v) {
                if ($v['uid'] == $user['uid']) $userBool = 1;
            }
        }
        if ($pinkT['uid'] == $user['uid']) $userBool = 1;
        $combinationOne = $this->getCombinationOne($pink['cid']);
        if (!$combinationOne) {
            throw new ApiException(410312);
        }

        $data['userInfo']['uid'] = $user['uid'];
        $data['userInfo']['nickname'] = $user['nickname'];
        $data['userInfo']['avatar'] = $user['avatar'];
        $data['is_ok'] = $is_ok;
        $data['userBool'] = $userBool;
        $data['pinkBool'] = $pinkBool;
        $data['store_combination'] = $combinationOne->hidden(['mer_id', 'images', 'attr', 'info', 'sort', 'sales', 'stock', 'add_time', 'is_host', 'is_show', 'is_del', 'combination', 'mer_use', 'is_postage', 'postage', 'start_time', 'stop_time', 'cost', 'browse', 'product_price'])->toArray();
        $data['pinkT'] = $pinkT;
        $data['pinkAll'] = $pinkAll;
        $data['count'] = $count <= 0 ? 0 : $count;
        $data['store_combination_host'] = $this->dao->getCombinationHost();
        $data['current_pink_order'] = $pinkService->getCurrentPink($id, $user['uid']);

        /** @var StoreProductAttrServices $storeProductAttrServices */
        $storeProductAttrServices = app()->make(StoreProductAttrServices::class);
        /** @var StoreProductAttrValueServices $storeProductAttrValueServices */
        $storeProductAttrValueServices = app()->make(StoreProductAttrValueServices::class);

        list($productAttr, $productValue) = $storeProductAttrServices->getProductAttrDetail($combinationOne['id'], $user['uid'], 0, 3, $combinationOne['product_id']);
        foreach ($productValue as $k => $v) {
            $productValue[$k]['product_stock'] = $storeProductAttrValueServices->value(['product_id' => $combinationOne['product_id'], 'suk' => $v['suk'], 'type' => 0], 'stock');
        }
        $data['store_combination']['productAttr'] = $productAttr;
        $data['store_combination']['productValue'] = $productValue;

        /** @var StoreOrderServices $orderServices */
        $orderServices = app()->make(StoreOrderServices::class);
        $data['order_pid'] = $orderServices->value(['order_id' => $data['current_pink_order']], 'pid');

        return $data;
    }

    /**
     * 验证拼团下单库存限量
     * @param int $uid
     * @param int $combinationId
     * @param int $cartNum
     * @param string $unique
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkCombinationStock(int $uid, int $combinationId, int $cartNum = 1, string $unique = '')
    {
        /** @var StoreProductAttrValueServices $attrValueServices */
        $attrValueServices = app()->make(StoreProductAttrValueServices::class);
        if ($unique == '') {
            $unique = $attrValueServices->value(['product_id' => $combinationId, 'type' => 3], 'unique');
        }
        $attrInfo = $attrValueServices->getOne(['product_id' => $combinationId, 'unique' => $unique, 'type' => 3]);
        if (!$attrInfo || $attrInfo['product_id'] != $combinationId) {
            throw new ApiException(410305);
        }
        $StoreCombinationInfo = $productInfo = $this->getCombinationOne($combinationId, '*,title as store_name');
        if (!$StoreCombinationInfo) {
            throw new ApiException(410295);
        }
        /** @var StoreOrderServices $orderServices */
        $orderServices = app()->make(StoreOrderServices::class);
        $userBuyCount = $orderServices->getBuyCount($uid, 'combination_id', $combinationId);
        if ($StoreCombinationInfo['once_num'] < $cartNum) {
            throw new ApiException(410313, ['num' => $StoreCombinationInfo['once_num']]);
        }
        if ($StoreCombinationInfo['num'] < ($userBuyCount + $cartNum)) {
            throw new ApiException(410298, ['num' => $StoreCombinationInfo['num']]);
        }

        if ($cartNum > $attrInfo['quota']) {
            throw new ApiException(410296);
        }
        return [$attrInfo, $unique, $productInfo];
    }

    /**
     * 拼团统计
     * @param $id
     * @return array
     */
    public function combinationStatistics($id)
    {
        /** @var StorePinkServices $pinkServices */
        $pinkServices = app()->make(StorePinkServices::class);
        /** @var StoreOrderServices $orderServices */
        $orderServices = app()->make(StoreOrderServices::class);
        $people_count = $pinkServices->getDistinctCount([['cid', '=', $id]], 'uid', false);
        $spread_count = $pinkServices->getDistinctCount([['cid', '=', $id], ['k_id', '>', 0]], 'uid', false);
        $start_count = $pinkServices->count(['cid' => $id, 'k_id' => 0]);
        $success_count = $pinkServices->count(['cid' => $id, 'k_id' => 0, 'status' => 2]);
        $pay_price = $orderServices->sum(['combination_id' => $id, 'paid' => 1], 'pay_price', true);
        $pay_count = $orderServices->getDistinctCount([['combination_id', '=', $id], ['paid', '=', 1]], 'uid', false);
        return compact('people_count', 'spread_count', 'start_count', 'success_count', 'pay_price', 'pay_count');
    }

    /**
     * 拼团订单
     * @param $id
     * @param array $where
     * @return array
     */
    public function combinationStatisticsOrder($id, $where = [])
    {
        /** @var StoreOrderServices $orderServices */
        $orderServices = app()->make(StoreOrderServices::class);
        [$page, $limit] = $this->getPageValue();
        $list = $orderServices->combinationStatisticsOrder($id, $where, $page, $limit);
        $where['combination_id'] = $id;
        $count = $orderServices->count($where);
        foreach ($list as &$item) {
            if ($item['status'] == 0) {
                if ($item['paid'] == 0) {
                    $item['status'] = '未支付';
                } else {
                    $item['status'] = '未发货';
                }
            } elseif ($item['status'] == 1) {
                $item['status'] = '待收货';
            } elseif ($item['status'] == 2) {
                $item['status'] = '待评价';
            } elseif ($item['status'] == 3) {
                $item['status'] = '已完成';
            } elseif ($item['status'] == -2) {
                $item['status'] = '已退款';
            } else {
                $item['status'] = '未知';
            }
            $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
            $item['pay_time'] = $item['pay_time'] ? date('Y-m-d H:i:s', $item['pay_time']) : '';
        }
        return compact('list', 'count');
    }
}
