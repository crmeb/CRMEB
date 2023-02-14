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

namespace app\services\activity\bargain;

use app\dao\activity\bargain\StoreBargainDao;
use app\jobs\ProductLogJob;
use app\Request;
use app\services\BaseServices;
use app\services\order\StoreOrderServices;
use app\services\other\PosterServices;
use app\services\other\QrcodeServices;
use app\services\product\product\StoreCategoryServices;
use app\services\product\product\StoreDescriptionServices;
use app\services\product\product\StoreProductServices;
use app\services\product\sku\StoreProductAttrResultServices;
use app\services\product\sku\StoreProductAttrServices;
use app\services\product\sku\StoreProductAttrValueServices;
use app\services\system\attachment\SystemAttachmentServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;
use crmeb\services\CacheService;
use crmeb\services\app\MiniProgramService;
use app\services\other\UploadService;
use Guzzle\Http\EntityBody;

/**
 *
 * Class StoreBargainServices
 * @package app\services\activity
 * @method get(int $id, ?array $field) 获取一条数据
 * @method getBargainIdsArray(array $ids, array $field)
 * @method sum(array $where, string $field)
 * @method update(int $id, array $data)
 * @method addBargain(int $id, string $field)
 * @method value(array $where, string $field)
 * @method validWhere()
 * @method getList(array $where, int $page = 0, int $limit = 0) 获取砍价列表
 */
class StoreBargainServices extends BaseServices
{

    /**
     * StoreCombinationServices constructor.
     * @param StoreBargainDao $dao
     */
    public function __construct(StoreBargainDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 判断砍价商品是否开启
     * @param int $bargainId
     * @return int
     */
    public function validBargain($bargainId = 0)
    {
        $where = [];
        $time = time();
        $where[] = ['is_del', '=', 0];
        $where[] = ['status', '=', 1];
        $where[] = ['start_time', '<', $time];
        $where[] = ['stop_time', '>', $time - 85400];
        if ($bargainId) $where[] = ['id', '=', $bargainId];
        return $this->dao->getCount($where);
    }

    /**
     * 获取后台列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getStoreBargainList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, $page, $limit);
        $count = $this->dao->count($where);
        /** @var StoreBargainUserServices $storeBargainUserServices */
        $storeBargainUserServices = app()->make(StoreBargainUserServices::class);
        $ids = array_column($list, 'id');
        $countAll = $storeBargainUserServices->getAllCount([['bargain_id', 'in', $ids]]);
        $countSuccess = $storeBargainUserServices->getAllCount([
            ['status', '=', 3],
            ['bargain_id', 'in', $ids]
        ]);
        /** @var StoreBargainUserHelpServices $storeBargainUserHelpServices */
        $storeBargainUserHelpServices = app()->make(StoreBargainUserHelpServices::class);
        $countHelpAll = $storeBargainUserHelpServices->getHelpAllCount([['bargain_id', 'in', $ids]]);
        foreach ($list as &$item) {
            $item['count_people_all'] = $countAll[$item['id']] ?? 0;//参与人数
            $item['count_people_help'] = $countHelpAll[$item['id']] ?? 0;//帮忙砍价人数
            $item['count_people_success'] = $countSuccess[$item['id']] ?? 0;//砍价成功人数
            $item['stop_status'] = $item['stop_time'] < time() ? 1 : 0;
            if ($item['status']) {
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
     * 保存数据
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
        $data['image'] = $data['images'][0];
        $data['images'] = json_encode($data['images']);
        $data['stock'] = $detail[0]['stock'];
        $data['quota'] = $detail[0]['quota'];
        $data['quota_show'] = $detail[0]['quota'];
        $data['price'] = $detail[0]['price'];
        $data['min_price'] = $detail[0]['min_price'];
        $data['logistics'] = implode(',', $data['logistics']);
        if ($detail[0]['min_price'] < 0 || $detail[0]['price'] <= 0 || $detail[0]['min_price'] === '' || $detail[0]['price'] === '') throw new AdminException(400095);
        if ($detail[0]['min_price'] >= $detail[0]['price']) throw new AdminException(400511);
        if ($detail[0]['quota'] > $detail[0]['stock']) throw new AdminException(400090);

        //按照能砍掉的金额计算最大设置人数，并判断填写的砍价人数是否大于最大设置人数
        $bNum = bcmul(bcsub((string)$data['price'], (string)$data['min_price'], 2), '100');
        if ($data['people_num'] > $bNum) throw new AdminException(400512, ['num' => $bNum]);

        unset($data['section_time'], $data['description'], $data['attrs'], $data['items'], $detail[0]['min_price'], $detail[0]['_index'], $detail[0]['_rowKey']);
        /** @var StoreDescriptionServices $storeDescriptionServices */
        $storeDescriptionServices = app()->make(StoreDescriptionServices::class);
        /** @var StoreProductAttrServices $storeProductAttrServices */
        $storeProductAttrServices = app()->make(StoreProductAttrServices::class);
        /** @var StoreProductServices $storeProductServices */
        $storeProductServices = app()->make(StoreProductServices::class);
        $this->transaction(function () use ($id, $data, $description, $detail, $items, $storeDescriptionServices, $storeProductAttrServices, $storeProductServices) {
            if ($id) {
                $res = $this->dao->update($id, $data);
                $storeDescriptionServices->saveDescription((int)$id, $description, 2);
                $skuList = $storeProductServices->validateProductAttr($items, $detail, (int)$id, 2);
                $valueGroup = $storeProductAttrServices->saveProductAttr($skuList, (int)$id, 2);
                if (!$res) throw new AdminException(100007);
            } else {
                if (!$storeProductServices->getOne(['is_show' => 1, 'is_del' => 0, 'id' => $data['product_id']])) {
                    throw new AdminException(400091);
                }
                $data['add_time'] = time();
                $res = $this->dao->save($data);
                $storeDescriptionServices->saveDescription((int)$res->id, $description, 2);
                $skuList = $storeProductServices->validateProductAttr($items, $detail, (int)$res->id, 2);
                $valueGroup = $storeProductAttrServices->saveProductAttr($skuList, (int)$res->id, 2);
                if (!$res) throw new AdminException(100022);
            }
            $res = true;
            foreach ($valueGroup->toArray() as $item) {
                $res = $res && CacheService::setStock($item['unique'], (int)$item['quota_show'], 2);
            }
            if (!$res) {
                throw new AdminException(400092);
            }
        });
    }

    /**
     * 获取砍价详情
     * @param int $id
     * @return array|\think\Model|null
     */
    public function getInfo(int $id)
    {
        $info = $this->dao->get($id);
        if ($info) {
            if ($info['start_time'])
                $start_time = date('Y-m-d H:i:s', $info['start_time']);

            if ($info['stop_time'])
                $stop_time = date('Y-m-d H:i:s', $info['stop_time']);
            if (isset($start_time) && isset($stop_time))
                $info['section_time'] = [$start_time, $stop_time];
            else
                $info['section_time'] = [];
            unset($info['start_time'], $info['stop_time']);
        }
        $info['give_integral'] = intval($info['give_integral']);
        $info['price'] = floatval($info['price']);
        $info['postage'] = floatval($info['postage']);
        $info['cost'] = floatval($info['cost']);
        $info['bargain_max_price'] = floatval($info['bargain_max_price']);
        $info['bargain_min_price'] = floatval($info['bargain_min_price']);
        $info['min_price'] = floatval($info['min_price']);
        $info['weight'] = floatval($info['weight']);
        $info['volume'] = floatval($info['volume']);
        $info['logistics'] = explode(',', $info['logistics']);
        /** @var StoreDescriptionServices $storeDescriptionServices */
        $storeDescriptionServices = app()->make(StoreDescriptionServices::class);
        $info['description'] = $storeDescriptionServices->getDescription(['product_id' => $id, 'type' => 2]);
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
        $bargainResult = $storeProductAttrResultServices->value(['product_id' => $id, 'type' => 2], 'result');
        $items = json_decode($bargainResult, true)['attr'];
        $productAttr = $this->getattr($items, $pid, 0);
        $bargainAttr = $this->getattr($items, $id, 2);
        foreach ($productAttr as $pk => $pv) {
            foreach ($bargainAttr as &$sv) {
                if ($pv['detail'] == $sv['detail']) {
                    $productAttr[$pk] = $sv;
                }
            }
            $productAttr[$pk]['detail'] = json_decode($productAttr[$pk]['detail']);
        }
        $attrs['items'] = $items;
        $attrs['value'] = $productAttr;
        foreach ($items as $key => $item) {
            $header[] = ['title' => $item['value'], 'key' => 'value' . ($key + 1), 'align' => 'center', 'minWidth' => 80];
        }
        $header[] = ['title' => '图片', 'slot' => 'pic', 'align' => 'center', 'minWidth' => 120];
        $header[] = ['title' => '砍价起始金额', 'slot' => 'price', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '砍价最低价', 'slot' => 'min_price', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '成本价', 'key' => 'cost', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '原价', 'key' => 'ot_price', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '库存', 'key' => 'stock', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '限量', 'slot' => 'quota', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '重量(KG)', 'key' => 'weight', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '体积(m³)', 'key' => 'volume', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '商品编号', 'key' => 'bar_code', 'align' => 'center', 'minWidth' => 80];
        $attrs['header'] = $header;
        return $attrs;
    }

    /**
     * 获取规格
     * @param $attr
     * @param $id
     * @param $type
     * @return array
     */
    public function getattr($attr, $id, $type)
    {
        /** @var StoreProductAttrValueServices $storeProductAttrValueServices */
        $storeProductAttrValueServices = app()->make(StoreProductAttrValueServices::class);
        list($value, $head) = attr_format($attr);
        $valueNew = [];
        $count = 0;
        if ($type == 2) {
            $min_price = $this->dao->value(['id' => $id], 'min_price');
        } else {
            $min_price = 0;
        }
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
                $valueNew[$count]['min_price'] = $min_price ? floatval($min_price) : 0;
                $valueNew[$count]['cost'] = $sukValue[$suk]['cost'] ? floatval($sukValue[$suk]['cost']) : 0;
                $valueNew[$count]['ot_price'] = isset($sukValue[$suk]['ot_price']) ? floatval($sukValue[$suk]['ot_price']) : 0;
                $valueNew[$count]['stock'] = $sukValue[$suk]['stock'] ? intval($sukValue[$suk]['stock']) : 0;
                $valueNew[$count]['quota'] = $sukValue[$suk]['quota'] ? intval($sukValue[$suk]['quota']) : 0;
                $valueNew[$count]['bar_code'] = $sukValue[$suk]['bar_code'] ?? '';
                $valueNew[$count]['weight'] = $sukValue[$suk]['weight'] ? floatval($sukValue[$suk]['weight']) : 0;
                $valueNew[$count]['volume'] = $sukValue[$suk]['volume'] ? floatval($sukValue[$suk]['volume']) : 0;
                $valueNew[$count]['brokerage'] = $sukValue[$suk]['brokerage'] ? floatval($sukValue[$suk]['brokerage']) : 0;
                $valueNew[$count]['brokerage_two'] = $sukValue[$suk]['brokerage_two'] ? floatval($sukValue[$suk]['brokerage_two']) : 0;
                $valueNew[$count]['opt'] = $type != 0;
                $count++;
            }
        }
        return $valueNew;
    }

//    /**
//     * TODO 获取砍价表ID
//     * @param int $bargainId $bargainId 砍价商品
//     * @param int $bargainUserUid $bargainUserUid  开启砍价用户编号
//     * @param int $status $status  砍价状态 1参与中 2 活动结束参与失败 3活动结束参与成功
//     * @return mixed
//     */
//    public function getBargainUserTableId($bargainId = 0, $bargainUserUid = 0)
//    {
//        return $this->dao->value(['bargain_id' => $bargainId, 'uid' => $bargainUserUid, 'is_del' => 0], 'id');
//    }

//    /**
//     * TODO 获取用户可以砍掉的价格
//     * @param $id $id 用户参与砍价表编号
//     * @return float
//     * @throws \think\db\exception\DataNotFoundException
//     * @throws \think\db\exception\ModelNotFoundException
//     * @throws \think\exception\DbException
//     */
//    public function getBargainUserDiffPriceFloat($id)
//    {
//        $price = $this->dao->get($id, ['bargain_price,bargain_price_min']);
//        return (float)bcsub($price['bargain_price'], $price['bargain_price_min'], 2);
//    }

//    /**
//     * TODO 获取用户砍掉的价格
//     * @param int $id $id 用户参与砍价表编号
//     * @return float
//     */
//    public function getBargainUserPrice($id = 0)
//    {
//        return (float)$this->dao->value(['id' => $id], 'price');
//    }

//    /**
//     * 获取一条砍价商品
//     * @param int $bargainId
//     * @param string $field
//     * @return array
//     */
//    public function getBargainOne($bargainId = 0, $field = 'id,product_id,title,price,min_price,image')
//    {
//        if (!$bargainId) return [];
//        $bargain = $this->dao->getOne(['id' => $bargainId], $field);
//        if ($bargain) return $bargain->toArray();
//        else return [];
//    }

    /**
     * 砍价列表
     * @return array
     */
    public function getBargainList()
    {
        /** @var StoreBargainUserServices $bargainUserService */
        $bargainUserService = app()->make(StoreBargainUserServices::class);
        [$page, $limit] = $this->getPageValue();
        $field = 'id,product_id,title,min_price,image,price';
        $list = $this->dao->BargainList($page, $limit, $field);
        foreach ($list as &$item) {
            $item['people'] = $bargainUserService->getUserIdList($item['id']);
            $item['price'] = floatval($item['price']);
        }
        return $list;
    }

    /**
     * 后台页面设计获取砍价列表
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getDiyBargainList($where)
    {
        $where['status'] = 1;
        unset($where['is_show']);
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->DiyBargainList($where, $page, $limit);
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
            $item['is_product_type'] = 1;
        }
        return compact('count', 'list');
    }

    /**
     * 首页砍价商品
     * @param $where
     * @return array
     */
    public function getHomeList($where)
    {
        [$page, $limit] = $this->getPageValue();
        $where['is_del'] = 0;
        $where['is_show'] = 1;
        $data = [];
        $list = $this->dao->getHomeList($where, $page, $limit);
        foreach ($list as &$item) {
            $item['price'] = floatval($item['price']);
        }
        $data['list'] = $list;
        return $data;
    }

    /**
     * 前端获取砍价详情
     * @param Request $request
     * @param int $id
     * @param int $bargainUid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getBargain(Request $request, int $id, int $bargainUid)
    {
        /** @var StoreProductAttrServices $storeProductAttrServices */
        $storeProductAttrServices = app()->make(StoreProductAttrServices::class);
        /** @var StoreOrderServices $orderService */
        $orderService = app()->make(StoreOrderServices::class);
        /** @var StoreBargainUserServices $bargainUserService */
        $bargainUserService = app()->make(StoreBargainUserServices::class);

        //获取砍价商品信息
        $bargain = $this->dao->getOne(['id' => $id], '*', ['description']);
        if (!$bargain) throw new ApiException(410306);
        if ($bargain['stop_time'] < time()) throw new ApiException(410299);
        list($productAttr, $productValue) = $storeProductAttrServices->getProductAttrDetail($id, $request->uid(), 0, 2, $bargain['product_id']);
        foreach ($productValue as $v) {
            $bargain['attr'] = $v;
        }
        $bargain['time'] = time();
        $bargain = get_thumb_water($bargain);
        $bargain['small_image'] = $bargain['image'];
        $data['bargain'] = $bargain;

        //写入查看和分享数据
        $this->dao->addBargain($id, 'look');

        //用户数据
        $user = $request->user();
        $data['userInfo']['uid'] = $user['uid'];
        $data['userInfo']['nickname'] = $user['nickname'];
        $data['userInfo']['avatar'] = $user['avatar'];

        //砍价数据
        $userBargainInfo = $bargainUserService->helpCount($request, $id, $bargainUid);
        //用户已经生成砍价订单的总数
        $userBargainInfo['bargainOrderCount'] = $orderService->count(['bargain_id' => $id, 'uid' => $user['uid']]);
        //用户砍价的总数
        $userBargainInfo['bargainCount'] = $bargainUserService->count(['bargain_id' => $id, 'uid' => $user['uid'], 'is_del' => 0]);
        //判断砍价状态
        if (($userBargainInfo['bargainCount'] == 0 || $userBargainInfo['bargainCount'] == $userBargainInfo['bargainOrderCount']) //没有发起过砍价或者发起的砍价数量等于对应砍价商品的订单数量
            && $bargain['people_num'] > $userBargainInfo['bargainCount'] //商品的可发起砍价数量大于已经发起过的砍价数量
            && $userBargainInfo['price'] > 0 //剩余金额大于0
            && $request->uid() == $bargainUid) { //是自己砍价
            $userBargainInfo['bargainType'] = 1; //用户发起砍价
        } elseif ($userBargainInfo['bargainCount'] > $userBargainInfo['bargainOrderCount'] //发起的砍价数量大于生成的订单数量
            && $userBargainInfo['price'] > 0 //剩余金额大于0
            && $request->uid() == $bargainUid) { //是自己砍价
            $userBargainInfo['bargainType'] = 2; //发送给好友邀请砍价
        } elseif ($userBargainInfo['userBargainStatus'] //用户可以砍价
            && $userBargainInfo['price'] > 0 //剩余金额大于0
            && $request->uid() != $bargainUid) { //不是自己的砍价
            $userBargainInfo['bargainType'] = 3; //帮朋友砍价
        } elseif ($userBargainInfo['userBargainStatus'] //用户可以砍价
            && $userBargainInfo['price'] == 0 //剩余金额大于0
            && $request->uid() != $bargainUid) { //不是自己的砍价
            $userBargainInfo['bargainType'] = 4; //好友已经完成
        } elseif (!$userBargainInfo['userBargainStatus'] //用户不可以砍价
            && $request->uid() != $bargainUid) { //不是自己的砍价
            $userBargainInfo['bargainType'] = 5; //已经帮好友砍价
        } elseif ($userBargainInfo['price'] == 0 //剩余金额等于0
            && $request->uid() == $bargainUid //是自己砍价
            && $userBargainInfo['status'] != 3) { //未生成订单
            $userBargainInfo['bargainType'] = 6; //立即支付
        } else {
            $userBargainInfo['bargainType'] = 1; //立即支付
        }
        $data['userBargainInfo'] = $userBargainInfo;
        $data['bargain']['price'] = bcsub($data['bargain']['price'], (string)$userBargainInfo['alreadyPrice'], 2);

        //用户访问事件
        event('user.userVisit', [$user['uid'], $id, 'bargain', $bargain['product_id'], 'view']);

        //浏览记录
        ProductLogJob::dispatch(['visit', ['uid' => $user['uid'], 'product_id' => $bargain['product_id']]]);
        return $data;
    }

    /**
     * 验证砍价是否能支付
     * @param int $bargainId
     * @param int $uid
     */
    public function checkBargainUser(int $bargainId, int $uid)
    {
        /** @var StoreBargainUserServices $bargainUserServices */
        $bargainUserServices = app()->make(StoreBargainUserServices::class);
        $bargainUserInfo = $bargainUserServices->getOne(['uid' => $uid, 'bargain_id' => $bargainId, 'status' => 1, 'is_del' => 0]);
        if (!$bargainUserInfo)
            throw new ApiException(410307);
        $bargainUserTableId = $bargainUserInfo['id'];
        if ($bargainUserInfo['bargain_price_min'] < bcsub((string)$bargainUserInfo['bargain_price'], (string)$bargainUserInfo['price'], 2)) {
            throw new ApiException(410308);
        }
        if ($bargainUserInfo['status'] == 3)
            throw new ApiException(410309);
        /** @var StoreProductAttrValueServices $attrValueServices */
        $attrValueServices = app()->make(StoreProductAttrValueServices::class);
        $res = $attrValueServices->getOne(['product_id' => $bargainId, 'type' => 2]);
        if (!$this->validBargain($bargainId) || !$res) {
            throw new ApiException(410295);
        }
        $StoreBargainInfo = $this->dao->get($bargainId);
        if (1 > $res['quota']) {
            throw new ApiException(410296);
        }
        $product_stock = $attrValueServices->value(['product_id' => $StoreBargainInfo['product_id'], 'suk' => $res['suk'], 'type' => 0], 'stock');
        if ($product_stock < 1) {
            throw new ApiException(410296);
        }
        //修改砍价状态
        $this->setBargainUserStatus($bargainId, $uid, $bargainUserTableId);
        return true;
    }

    /**
     * 修改砍价状态
     * @param int $bargainId
     * @param int $uid
     * @param int $bargainUserTableId
     * @return bool|\crmeb\basic\BaseModel
     */
    public function setBargainUserStatus(int $bargainId, int $uid, int $bargainUserTableId)
    {
        if (!$bargainId || !$uid) return false;
        if (!$bargainUserTableId) return false;
        /** @var StoreBargainUserServices $bargainUserServices */
        $bargainUserServices = app()->make(StoreBargainUserServices::class);
        $count = $bargainUserServices->count(['id' => $bargainUserTableId, 'uid' => $uid, 'bargain_id' => $bargainId, 'status' => 1]);
        if (!$count) return false;
        $userPrice = $bargainUserServices->value(['id' => $bargainUserTableId, 'uid' => $uid, 'bargain_id' => $bargainId, 'status' => 1], 'price');
        $price = $bargainUserServices->get($bargainUserTableId, ['bargain_price', 'bargain_price_min']);
        $price = bcsub($price['bargain_price'], $price['bargain_price_min'], 2);
        if (bcsub($price, $userPrice, 2) > 0) {
            return false;
        }
        return $bargainUserServices->updateBargainStatus($bargainUserTableId);
    }

    /**
     * 发起砍价
     * @param int $uid
     * @param int $bargainId
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setBargain(int $uid, int $bargainId)
    {
        if (!$bargainId) throw new ApiException(100101);
        $bargainInfo = $this->dao->getOne([
            ['is_del', '=', 0],
            ['status', '=', 1],
            ['start_time', '<', time()],
            ['stop_time', '>', time()],
            ['id', '=', $bargainId],
        ]);
        if (!$bargainInfo) throw new ApiException(410299);
        $bargainInfo = $bargainInfo->toArray();
        /** @var StoreBargainUserServices $bargainUserService */
        $bargainUserService = app()->make(StoreBargainUserServices::class);
        $count = $bargainUserService->count(['bargain_id' => $bargainId, 'uid' => $uid, 'is_del' => 0, 'status' => 1]);
        if ($count === false) {
            throw new ApiException(100101);
        } else {
            /** @var StoreBargainUserHelpServices $bargainUserHelpService */
            $bargainUserHelpService = app()->make(StoreBargainUserHelpServices::class);
            $count = $bargainUserService->count(['uid' => $uid, 'bargain_id' => $bargainId, 'is_del' => 0]);
            if ($count >= $bargainInfo['num']) throw new ApiException(410300);
            return $this->transaction(function () use ($bargainUserService, $bargainUserHelpService, $bargainId, $uid, $bargainInfo) {
                $bargainUserInfo = $bargainUserService->setBargain($bargainId, $uid, $bargainInfo);
                $price = $bargainUserHelpService->setBargainRecord($uid, $bargainUserInfo->toArray(), $bargainInfo);
                return ['bargainUserInfo' => $bargainUserInfo, 'price' => $price];
            });
        }
    }

    /**
     * 参与砍价
     * @param int $uid
     * @param int $bargainId
     * @param int $bargainUserUid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setHelpBargain(int $uid, int $bargainId, int $bargainUserUid)
    {
        if (!$bargainId || !$bargainUserUid) throw new ApiException(100100);
        $bargainInfo = $this->dao->getOne([
            ['is_del', '=', 0],
            ['status', '=', 1],
            ['start_time', '<', time()],
            ['stop_time', '>', time()],
            ['id', '=', $bargainId],
        ]);
        if (!$bargainInfo) throw new ApiException(410299);
        $bargainInfo = $bargainInfo->toArray();
        /** @var StoreBargainUserHelpServices $userHelpService */
        $userHelpService = app()->make(StoreBargainUserHelpServices::class);
        /** @var StoreBargainUserServices $bargainUserService */
        $bargainUserService = app()->make(StoreBargainUserServices::class);
        $bargainUserTableId = $bargainUserService->getBargainUserTableId($bargainId, $bargainUserUid);
        if (!$bargainUserTableId) throw new ApiException(410301);
        $bargainUserInfo = $bargainUserService->get($bargainUserTableId)->toArray();
        $count = $userHelpService->isBargainUserHelpCount($bargainId, $bargainUserTableId, $uid);
        if (!$count) throw new ApiException(410302);
        $price = $userHelpService->setBargainRecord($uid, $bargainUserInfo, $bargainInfo);
        if ($price) {
            if (!$bargainUserService->getSurplusPrice($bargainUserTableId, 1)) {
                event('notice.notice', [['uid' => $bargainUserUid, 'bargainInfo' => $bargainInfo, 'bargainUserInfo' => $bargainUserInfo,], 'bargain_success']);
            }
        }
        return ['bargainUserInfo' => $bargainUserInfo, 'price' => $price];
    }

    /**
     * 减库存加销量
     * @param int $num
     * @param int $bargainId
     * @param string $unique
     * @return bool
     */
    public function decBargainStock(int $num, int $bargainId, string $unique)
    {
        $product_id = $this->dao->value(['id' => $bargainId], 'product_id');
        if ($unique) {
            /** @var StoreProductAttrValueServices $skuValueServices */
            $skuValueServices = app()->make(StoreProductAttrValueServices::class);
            //减去砍价商品sku的库存增加销量
            $res = false !== $skuValueServices->decProductAttrStock($bargainId, $unique, $num, 2);
            //减去砍价商品的库存和销量
            $res = $res && $this->dao->decStockIncSales(['id' => $bargainId, 'type' => 2], $num);
            //减掉普通商品sku的库存加销量
            $suk = $skuValueServices->value(['unique' => $unique, 'product_id' => $bargainId], 'suk');
            $productUnique = $skuValueServices->value(['suk' => $suk, 'product_id' => $product_id, 'type' => 0], 'unique');
            if ($productUnique) {
                $res = $res && $skuValueServices->decProductAttrStock($product_id, $productUnique, $num);
            }
        } else {
            //减去砍价商品的库存和销量
            $res = false !== $this->dao->decStockIncSales(['id' => $bargainId, 'type' => 2], $num);
        }
        /** @var StoreProductServices $services */
        $services = app()->make(StoreProductServices::class);
        //减掉普通商品的库存加销量
        $res = $res && $services->decProductStock($num, $product_id);
        return $res;
    }

    /**
     * 减销量加库存
     * @param int $num
     * @param int $bargainId
     * @param string $unique
     * @return bool
     */
    public function incBargainStock(int $num, int $bargainId, string $unique)
    {
        $product_id = $this->dao->value(['id' => $bargainId], 'product_id');
        if ($unique) {
            /** @var StoreProductAttrValueServices $skuValueServices */
            $skuValueServices = app()->make(StoreProductAttrValueServices::class);
            //减去砍价商品sku的销量,增加库存和限购数量
            $res = false !== $skuValueServices->incProductAttrStock($bargainId, $unique, $num, 2);
            //减去砍价商品的销量,增加库存
            $res = $res && $this->dao->incStockDecSales(['id' => $bargainId, 'type' => 2], $num);
            //减掉普通商品sku的销量,增加库存
            $suk = $skuValueServices->value(['unique' => $unique, 'product_id' => $bargainId], 'suk');
            $productUnique = $skuValueServices->value(['suk' => $suk, 'product_id' => $product_id], 'unique');
            if ($productUnique) {
                $res = $res && $skuValueServices->incProductAttrStock($product_id, $productUnique, $num);
            }
        } else {
            //减去砍价商品的销量,增加库存
            $res = false !== $this->dao->incStockDecSales(['id' => $bargainId, 'type' => 2], $num);
        }
        /** @var StoreProductServices $services */
        $services = app()->make(StoreProductServices::class);
        //减掉普通商品的库存加销量
        $res = $res && $services->incProductStock($num, $product_id);
        return $res;
    }

    /**
     * 砍价分享
     * @param $bargainId
     * @param $user
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function poster($bargainId, $user, $from)
    {
        $storeBargainInfo = $this->dao->get($bargainId, ['title', 'image', 'price']);
        if (!$storeBargainInfo) {
            throw new ApiException(410303);
        }
        /** @var StoreBargainUserServices $services */
        $services = app()->make(StoreBargainUserServices::class);
        $bargainUser = $services->get(['bargain_id' => $bargainId, 'uid' => $user['uid']], ['price', 'bargain_price_min']);
        if (!$bargainUser) {
            throw new ApiException(410304);
        }
        try {
            $siteUrl = sys_config('site_url');
            $data['title'] = $storeBargainInfo['title'];
            $data['image'] = $storeBargainInfo['image'];
            $data['price'] = bcsub($storeBargainInfo['price'], $bargainUser['price'], 2);
            $data['label'] = '已砍至';
            $price = bcsub($storeBargainInfo['price'], $bargainUser['price'], 2);
            $data['msg'] = '还差' . (bcsub($price, $bargainUser['bargain_price_min'], 2)) . '元即可砍价成功';
            /** @var SystemAttachmentServices $systemAttachmentServices */
            $systemAttachmentServices = app()->make(SystemAttachmentServices::class);
            if ($from == 'wechat') {
                $name = $bargainId . '_' . $user['uid'] . '_' . $user['is_promoter'] . '_bargain_share_wap.jpg';
                //公众号
                $imageInfo = $systemAttachmentServices->getInfo(['name' => $name]);
                if (!$imageInfo) {
                    $codeUrl = set_http_type($siteUrl . '/pages/activity/goods_bargain_details/index?id=' . $bargainId . '&bargain=' . $user['uid'] . '&spread=' . $user['uid'], 1);//二维码链接
                    $imageInfo = PosterServices::getQRCodePath($codeUrl, $name);
                    if (is_string($imageInfo)) {
                        throw new ApiException(410167);
                    }
                    $systemAttachmentServices->save([
                        'name' => $imageInfo['name'],
                        'att_dir' => $imageInfo['dir'],
                        'satt_dir' => $imageInfo['thumb_path'],
                        'att_size' => $imageInfo['size'],
                        'att_type' => $imageInfo['type'],
                        'image_type' => $imageInfo['image_type'],
                        'module_type' => 2,
                        'time' => $imageInfo['time'],
                        'pid' => 1,
                        'type' => 1
                    ]);
                    $url = $imageInfo['dir'];
                } else $url = $imageInfo['att_dir'];
                $data['url'] = $url;
                if ($imageInfo['image_type'] == 1) $data['url'] = $siteUrl . $url;
                $posterImage = PosterServices::setShareMarketingPoster($data, 'wap/activity/bargain/poster');
                if (!is_array($posterImage)) {
                    throw new ApiException(410172);
                }
                $systemAttachmentServices->save([
                    'name' => $posterImage['name'],
                    'att_dir' => $posterImage['dir'],
                    'satt_dir' => $posterImage['thumb_path'],
                    'att_size' => $posterImage['size'],
                    'att_type' => $posterImage['type'],
                    'image_type' => $posterImage['image_type'],
                    'module_type' => 2,
                    'time' => $posterImage['time'],
                    'pid' => 1,
                    'type' => 1
                ]);
                if ($posterImage['image_type'] == 1) $posterImage['dir'] = $siteUrl . $posterImage['dir'];
                $wapPosterImage = set_http_type($posterImage['dir'], 1);//公众号推广海报
                return $wapPosterImage;
            } else {
                //小程序
                $name = $bargainId . '_' . $user['uid'] . '_' . $user['is_promoter'] . '_bargain_share_routine.jpg';
                $imageInfo = $systemAttachmentServices->getInfo(['name' => $name]);
                if (!$imageInfo) {
                    $valueData = 'id=' . $bargainId . '&bargain=' . $user['uid'];
                    /** @var UserServices $userServices */
                    $userServices = app()->make(UserServices::class);
                    if ($userServices->checkUserPromoter((int)$user['uid'], $user)) {
                        $valueData .= '&spread=' . $user['uid'];
                    }
                    $res = MiniProgramService::appCodeUnlimitService($valueData, 'pages/activity/goods_bargain_details/index', 280);
                    if (!$res) throw new ApiException(400237);
                    $uploadType = (int)sys_config('upload_type', 1);
                    $upload = UploadService::init();
                    $res = (string)EntityBody::factory($res);
                    $res = $upload->to('routine/activity/bargain/code')->validate()->setAuthThumb(false)->stream($res, $name);
                    if ($res === false) {
                        throw new ApiException($upload->getError());
                    }
                    $imageInfo = $upload->getUploadInfo();
                    $imageInfo['image_type'] = $uploadType;
                    if ($imageInfo['image_type'] == 1) $remoteImage = PosterServices::remoteImage($siteUrl . $imageInfo['dir']);
                    else $remoteImage = PosterServices::remoteImage($imageInfo['dir']);
                    if (!$remoteImage['status']) throw new ApiException(410167);
                    $systemAttachmentServices->save([
                        'name' => $imageInfo['name'],
                        'att_dir' => $imageInfo['dir'],
                        'satt_dir' => $imageInfo['thumb_path'],
                        'att_size' => $imageInfo['size'],
                        'att_type' => $imageInfo['type'],
                        'image_type' => $imageInfo['image_type'],
                        'module_type' => 2,
                        'time' => time(),
                        'pid' => 1,
                        'type' => 1
                    ]);
                    $url = $imageInfo['dir'];
                } else $url = $imageInfo['att_dir'];
                $data['url'] = $url;
                if ($imageInfo['image_type'] == 1)
                    $data['url'] = $siteUrl . $url;
                $posterImage = PosterServices::setShareMarketingPoster($data, 'routine/activity/bargain/poster');
                if (!is_array($posterImage)) throw new ApiException(410172);
                $systemAttachmentServices->save([
                    'name' => $posterImage['name'],
                    'att_dir' => $posterImage['dir'],
                    'satt_dir' => $posterImage['thumb_path'],
                    'att_size' => $posterImage['size'],
                    'att_type' => $posterImage['type'],
                    'image_type' => $posterImage['image_type'],
                    'module_type' => 2,
                    'time' => $posterImage['time'],
                    'pid' => 1,
                    'type' => 1
                ]);
                if ($posterImage['image_type'] == 1) $posterImage['dir'] = $siteUrl . $posterImage['dir'];
                $routinePosterImage = set_http_type($posterImage['dir'], 0);//小程序推广海报
                return $routinePosterImage;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取砍价海报信息
     * @param int $bargainId
     * @param $user
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function posterInfo(int $bargainId, $user)
    {
        $storeBargainInfo = $this->dao->get($bargainId, ['title', 'image', 'price']);
        if (!$storeBargainInfo) {
            throw new ApiException(410303);
        }
        /** @var StoreBargainUserServices $services */
        $services = app()->make(StoreBargainUserServices::class);
        $bargainUser = $services->get(['bargain_id' => $bargainId, 'uid' => $user['uid']], ['price', 'bargain_price_min']);
        if (!$bargainUser) {
            throw new ApiException(410304);
        }
        $data['url'] = '';
        $data['title'] = $storeBargainInfo['title'];
        $data['image'] = $storeBargainInfo['image'];
        $data['price'] = bcsub($storeBargainInfo['price'], $bargainUser['price'], 2);
        $data['label'] = '已砍至';
        $price = bcsub($storeBargainInfo['price'], $bargainUser['price'], 2);
        $data['msg'] = '还差' . (bcsub($price, $bargainUser['bargain_price_min'], 2)) . '元即可砍价成功';
        //只有在小程序端，才会生成二维码
        if (\request()->isRoutine()) {
            try {
                /** @var SystemAttachmentServices $systemAttachmentServices */
                $systemAttachmentServices = app()->make(SystemAttachmentServices::class);
                //小程序
                $name = $bargainId . '_' . $user['uid'] . '_' . $user['is_promoter'] . '_bargain_share_routine.jpg';
                $siteUrl = sys_config('site_url');
                $imageInfo = $systemAttachmentServices->getInfo(['name' => $name]);
                if (!$imageInfo) {
                    $valueData = 'id=' . $bargainId . '&bargain=' . $user['uid'];
                    /** @var UserServices $userServices */
                    $userServices = app()->make(UserServices::class);
                    if ($userServices->checkUserPromoter((int)$user['uid'], $user)) {
                        $valueData .= '&spread=' . $user['uid'];
                    }
                    $res = MiniProgramService::appCodeUnlimitService($valueData, 'pages/activity/goods_bargain_details/index', 280);
                    if (!$res) throw new ApiException(410167);
                    $uploadType = (int)sys_config('upload_type', 1);
                    $upload = UploadService::init();
                    $res = (string)EntityBody::factory($res);
                    $res = $upload->to('routine/activity/bargain/code')->validate()->setAuthThumb(false)->stream($res, $name);
                    if ($res === false) {
                        throw new ApiException($upload->getError());
                    }
                    $imageInfo = $upload->getUploadInfo();
                    $imageInfo['image_type'] = $uploadType;
                    if ($imageInfo['image_type'] == 1) $remoteImage = PosterServices::remoteImage($siteUrl . $imageInfo['dir']);
                    else $remoteImage = PosterServices::remoteImage($imageInfo['dir']);
                    if (!$remoteImage['status']) throw new ApiException($remoteImage['msg']);
                    $systemAttachmentServices->save([
                        'name' => $imageInfo['name'],
                        'att_dir' => $imageInfo['dir'],
                        'satt_dir' => $imageInfo['thumb_path'],
                        'att_size' => $imageInfo['size'],
                        'att_type' => $imageInfo['type'],
                        'image_type' => $imageInfo['image_type'],
                        'module_type' => 2,
                        'time' => time(),
                        'pid' => 1,
                        'type' => 1
                    ]);
                    $url = $imageInfo['dir'];
                } else $url = $imageInfo['att_dir'];
                if ($imageInfo['image_type'] == 1) {
                    $data['url'] = $siteUrl . $url;
                } else {
                    $data['url'] = $url;
                }
            } catch (\Throwable $e) {
            }
        } else {
            if (sys_config('share_qrcode', 0) && request()->isWechat()) {
                /** @var QrcodeServices $qrcodeService */
                $qrcodeService = app()->make(QrcodeServices::class);
                $data['url'] = $qrcodeService->getTemporaryQrcode('bargain-' . $bargainId . '-' . $user['uid'], $user['uid'])->url;
            }
        }
        return $data;
    }

    /**
     * 验证砍价下单库存限量
     * @param int $uid
     * @param int $bargainId
     * @param int $cartNum
     * @param string $unique
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkBargainStock(int $uid, int $bargainId, int $cartNum = 1, string $unique = '')
    {
        if (!$this->validBargain($bargainId)) {
            throw new ApiException(410295);
        }
        /** @var StoreProductAttrValueServices $attrValueServices */
        $attrValueServices = app()->make(StoreProductAttrValueServices::class);
        $attrInfo = $attrValueServices->getOne(['product_id' => $bargainId, 'type' => 2]);
        if (!$attrInfo || $attrInfo['product_id'] != $bargainId) {
            throw new ApiException(410305);
        }
        $productInfo = $this->dao->get($bargainId, ['*', 'title as store_name']);
        /** @var StoreBargainUserServices $bargainUserService */
        $bargainUserService = app()->make(StoreBargainUserServices::class);
        $bargainUserInfo = $bargainUserService->getOne(['uid' => $uid, 'bargain_id' => $bargainId, 'status' => 1, 'is_del' => 0]);
        if ($bargainUserInfo['bargain_price_min'] < bcsub((string)$bargainUserInfo['bargain_price'], (string)$bargainUserInfo['price'], 2)) {
            throw new ApiException(413103);
        }
        $unique = $attrInfo['unique'];
        if ($cartNum > $attrInfo['quota']) {
            throw new ApiException(410296);
        }
        return [$attrInfo, $unique, $productInfo, $bargainUserInfo];
    }

    /**
     * 砍价统计
     * @param $id
     * @return array
     */
    public function bargainStatistics($id)
    {
        /** @var StoreBargainUserServices $bargainUser */
        $bargainUser = app()->make(StoreBargainUserServices::class);
        /** @var StoreBargainUserHelpServices $bargainUserHelp */
        $bargainUserHelp = app()->make(StoreBargainUserHelpServices::class);
        /** @var StoreOrderServices $orderServices */
        $orderServices = app()->make(StoreOrderServices::class);
        $people_count = $bargainUserHelp->count(['bargain_id' => $id]);
        $spread_count = $bargainUserHelp->count(['bargain_id' => $id, 'type' => 0]);
        $start_count = $bargainUser->count(['bargain_id' => $id]);
        $success_count = $bargainUser->count(['bargain_id' => $id, 'status' => 3]);
        $pay_price = $orderServices->sum(['bargain_id' => $id, 'paid' => 1], 'pay_price', true);
        $pay_count = $orderServices->count(['bargain_id' => $id, 'paid' => 1]);
        $pay_rate = $start_count > 0 ? bcmul(bcdiv((string)$pay_count, (string)$start_count, 2), '100', 2) : 0;
        return compact('people_count', 'spread_count', 'start_count', 'success_count', 'pay_price', 'pay_count', 'pay_rate');
    }

    /**
     * 砍价列表
     * @param $id
     * @param array $where
     * @return array
     */
    public function bargainStatisticsList($id, $where = [])
    {
        /** @var StoreBargainUserServices $bargainUser */
        $bargainUser = app()->make(StoreBargainUserServices::class);
        $where['bargain_id'] = $id;
        return $bargainUser->bargainUserList($where);
    }

    /**
     * 砍价订单
     * @param $id
     * @param array $where
     * @return array
     */
    public function bargainStatisticsOrder($id, $where = [])
    {
        /** @var StoreOrderServices $orderServices */
        $orderServices = app()->make(StoreOrderServices::class);
        [$page, $limit] = $this->getPageValue();
        $list = $orderServices->bargainStatisticsOrder($id, $where, $page, $limit);
        $where['bargain_id'] = $id;
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
