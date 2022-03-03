<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
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
use app\services\product\product\StoreCategoryServices;
use app\services\product\product\StoreDescriptionServices;
use app\services\product\product\StoreProductServices;
use app\services\product\sku\StoreProductAttrResultServices;
use app\services\product\sku\StoreProductAttrServices;
use app\services\product\sku\StoreProductAttrValueServices;
use app\services\system\attachment\SystemAttachmentServices;
use app\services\user\UserServices;
use app\services\wechat\WechatServices;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use crmeb\services\MiniProgramService;
use crmeb\services\UploadService;
use crmeb\services\UtilService;
use Guzzle\Http\EntityBody;
use think\exception\ValidateException;

/**
 *
 * Class StoreBargainServices
 * @package app\services\activity
 * @method get(int $id, array $field) 获取一条数据
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
     * @return int|string
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

        if ($detail[0]['min_price'] < 0 || $detail[0]['price'] <= 0 || $detail[0]['min_price'] === '' || $detail[0]['price'] === '') throw new ValidateException('金额不能小于0');
        if ($detail[0]['min_price'] >= $detail[0]['price']) throw new ValidateException('砍价最低价不能大于或等于起始金额');
        if ($detail[0]['quota'] > $detail[0]['stock']) throw new ValidateException('限量不能超过商品库存');

        //按照能砍掉的金额计算最大设置人数，并判断填写的砍价人数是否大于最大设置人数
        $bNum = bcmul(bcsub((string)$data['price'], (string)$data['min_price'], 2), '100');
        if ($data['people_num'] > $bNum) throw new ValidateException('砍价人数不能大于' . $bNum . '人');

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
                if (!$res) throw new AdminException('修改失败');
            } else {
                if (!$storeProductServices->getOne(['is_show' => 1, 'is_del' => 0, 'id' => $data['product_id']])) {
                    throw new AdminException('原商品已下架或移入回收站');
                }
                $data['add_time'] = time();
                $res = $this->dao->save($data);
                $storeDescriptionServices->saveDescription((int)$res->id, $description, 2);
                $skuList = $storeProductServices->validateProductAttr($items, $detail, (int)$res->id, 2);
                $valueGroup = $storeProductAttrServices->saveProductAttr($skuList, (int)$res->id, 2);
                if (!$res) throw new AdminException('添加失败');
            }
            $res = true;
            foreach ($valueGroup->toArray() as $item) {
                $res = $res && CacheService::setStock($item['unique'], (int)$item['quota_show'], 2);
            }
            if (!$res) {
                throw new AdminException('占用库存失败');
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
        $value = attr_format($attr)[1];
        $valueNew = [];
        $count = 0;
        if ($type == 2) {
            $min_price = $this->dao->value(['id' => $id], 'min_price');
        } else {
            $min_price = 0;
        }
        foreach ($value as $key => $item) {
            $detail = $item['detail'];
//            sort($item['detail'], SORT_STRING);
            $suk = implode(',', $item['detail']);
            $sukValue = $storeProductAttrValueServices->getColumn(['product_id' => $id, 'type' => $type, 'suk' => $suk], 'bar_code,cost,price,ot_price,stock,image as pic,weight,volume,brokerage,brokerage_two,quota', 'suk');
            if (count($sukValue)) {
                foreach (array_values($detail) as $k => $v) {
                    $valueNew[$count]['value' . ($k + 1)] = $v;
                }
                $valueNew[$count]['detail'] = json_encode($detail);
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
        if (!$bargain) throw new ValidateException('砍价商品不存在');
        if ($bargain['stop_time'] < time()) throw new ValidateException('砍价已结束');
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
        }
        $data['userBargainInfo'] = $userBargainInfo;

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
            throw new ValidateException('砍价失败');
        $bargainUserTableId = $bargainUserInfo['id'];
        if ($bargainUserInfo['bargain_price_min'] < bcsub((string)$bargainUserInfo['bargain_price'], (string)$bargainUserInfo['price'], 2)) {
            throw new ValidateException('砍价未成功');
        }
        if ($bargainUserInfo['status'] == 3)
            throw new ValidateException('砍价已支付');

        /** @var StoreBargainServices $bargainService */
        $bargainService = app()->make(StoreBargainServices::class);
        /** @var StoreProductAttrValueServices $attrValueServices */
        $attrValueServices = app()->make(StoreProductAttrValueServices::class);
        $res = $attrValueServices->getOne(['product_id' => $bargainId, 'type' => 2]);
        if (!$bargainService->validBargain($bargainId) || !$res) {
            throw new ValidateException('该商品已下架或删除');
        }
        $StoreBargainInfo = $bargainService->get($bargainId);
        if (1 > $res['quota']) {
            throw new ValidateException('该商品库存不足');
        }
        $product_stock = $attrValueServices->value(['product_id' => $StoreBargainInfo['product_id'], 'suk' => $res['suk'], 'type' => 0], 'stock');
        if ($product_stock < 1) {
            throw new ValidateException('该商品库存不足');
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
        if (!$bargainId) throw new ValidateException('参数错误');
        $bargainInfo = $this->dao->getOne([
            ['is_del', '=', 0],
            ['status', '=', 1],
            ['start_time', '<', time()],
            ['stop_time', '>', time()],
            ['id', '=', $bargainId],
        ]);
        if (!$bargainInfo) throw new ValidateException('砍价已结束');
        $bargainInfo = $bargainInfo->toArray();
        /** @var StoreBargainUserServices $bargainUserService */
        $bargainUserService = app()->make(StoreBargainUserServices::class);
        $count = $bargainUserService->count(['bargain_id' => $bargainId, 'uid' => $uid, 'is_del' => 0, 'status' => 1]);
        if ($count === false) {
            throw new ValidateException('参数错误');
        } else {
            /** @var StoreBargainUserHelpServices $bargainUserHelpService */
            $bargainUserHelpService = app()->make(StoreBargainUserHelpServices::class);
            $count = $bargainUserService->count(['uid' => $uid, 'bargain_id' => $bargainId, 'is_del' => 0]);
            if ($count >= $bargainInfo['num']) throw new ValidateException('您不能再发起此件商品砍价');
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
        if (!$bargainId || !$bargainUserUid) throw new ValidateException('参数错误');
        $bargainInfo = $this->dao->getOne([
            ['is_del', '=', 0],
            ['status', '=', 1],
            ['start_time', '<', time()],
            ['stop_time', '>', time()],
            ['id', '=', $bargainId],
        ]);
        if (!$bargainInfo) throw new ValidateException('砍价已结束');
        $bargainInfo = $bargainInfo->toArray();
        /** @var StoreBargainUserHelpServices $userHelpService */
        $userHelpService = app()->make(StoreBargainUserHelpServices::class);
        /** @var StoreBargainUserServices $bargainUserService */
        $bargainUserService = app()->make(StoreBargainUserServices::class);
        $bargainUserTableId = $bargainUserService->getBargainUserTableId($bargainId, $bargainUserUid);
        if (!$bargainUserTableId) throw new ValidateException('该分享未开启砍价');
        $bargainUserInfo = $bargainUserService->get($bargainUserTableId)->toArray();
        $count = $userHelpService->isBargainUserHelpCount($bargainId, $bargainUserTableId, $uid);
        if (!$count) throw new ValidateException('您已经帮砍过此砍价');
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
            throw new ValidateException('砍价信息没有查到');
        }
        /** @var StoreBargainUserServices $services */
        $services = app()->make(StoreBargainUserServices::class);
        $bargainUser = $services->get(['bargain_id' => $bargainId, 'uid' => $user['uid']], ['price', 'bargain_price_min']);
        if (!$bargainUser) {
            throw new ValidateException('用户砍价信息未查到');
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
                    $imageInfo = UtilService::getQRCodePath($codeUrl, $name);
                    if (is_string($imageInfo)) {
                        throw new ValidateException('二维码生成失败');
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
                $posterImage = UtilService::setShareMarketingPoster($data, 'wap/activity/bargain/poster');
                if (!is_array($posterImage)) {
                    throw new ValidateException('海报生成失败');
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
                    $res = MiniProgramService::qrcodeService()->appCodeUnlimit($valueData, 'pages/activity/goods_bargain_details/index', 280);
                    if (!$res) throw new ValidateException('二维码生成失败');
                    $uploadType = (int)sys_config('upload_type', 1);
                    $upload = UploadService::init();
                    $res = (string)EntityBody::factory($res);
                    $res = $upload->to('routine/activity/bargain/code')->validate()->setAuthThumb(false)->stream($res, $name);
                    if ($res === false) {
                        throw new ValidateException($upload->getError());
                    }
                    $imageInfo = $upload->getUploadInfo();
                    $imageInfo['image_type'] = $uploadType;
                    if ($imageInfo['image_type'] == 1) $remoteImage = UtilService::remoteImage($siteUrl . $imageInfo['dir']);
                    else $remoteImage = UtilService::remoteImage($imageInfo['dir']);
                    if (!$remoteImage['status']) throw new ValidateException($remoteImage['msg']);
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
                $posterImage = UtilService::setShareMarketingPoster($data, 'routine/activity/bargain/poster');
                if (!is_array($posterImage)) throw new ValidateException('海报生成失败');
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
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function posterInfo(int $bargainId, $user)
    {
        $storeBargainInfo = $this->dao->get($bargainId, ['title', 'image', 'price']);
        if (!$storeBargainInfo) {
            throw new ValidateException('砍价信息没有查到');
        }
        /** @var StoreBargainUserServices $services */
        $services = app()->make(StoreBargainUserServices::class);
        $bargainUser = $services->get(['bargain_id' => $bargainId, 'uid' => $user['uid']], ['price', 'bargain_price_min']);
        if (!$bargainUser) {
            throw new ValidateException('用户砍价信息未查到');
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
                    $res = MiniProgramService::qrcodeService()->appCodeUnlimit($valueData, 'pages/activity/goods_bargain_details/index', 280);
                    if (!$res) throw new ValidateException('二维码生成失败');
                    $uploadType = (int)sys_config('upload_type', 1);
                    $upload = UploadService::init();
                    $res = (string)EntityBody::factory($res);
                    $res = $upload->to('routine/activity/bargain/code')->validate()->setAuthThumb(false)->stream($res, $name);
                    if ($res === false) {
                        throw new ValidateException($upload->getError());
                    }
                    $imageInfo = $upload->getUploadInfo();
                    $imageInfo['image_type'] = $uploadType;
                    if ($imageInfo['image_type'] == 1) $remoteImage = UtilService::remoteImage($siteUrl . $imageInfo['dir']);
                    else $remoteImage = UtilService::remoteImage($imageInfo['dir']);
                    if (!$remoteImage['status']) throw new ValidateException($remoteImage['msg']);
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
            throw new ValidateException('该商品已下架或删除');
        }
        /** @var StoreProductAttrValueServices $attrValueServices */
        $attrValueServices = app()->make(StoreProductAttrValueServices::class);
        $attrInfo = $attrValueServices->getOne(['product_id' => $bargainId, 'type' => 2]);
        if (!$attrInfo || $attrInfo['product_id'] != $bargainId) {
            throw new ValidateException('请选择有效的商品属性');
        }
        $productInfo = $this->dao->get($bargainId, ['*', 'title as store_name']);
        /** @var StoreBargainUserServices $bargainUserService */
        $bargainUserService = app()->make(StoreBargainUserServices::class);
        $bargainUserInfo = $bargainUserService->getOne(['uid' => $uid, 'bargain_id' => $bargainId, 'status' => 1, 'is_del' => 0]);
        if ($bargainUserInfo['bargain_price_min'] < bcsub((string)$bargainUserInfo['bargain_price'], (string)$bargainUserInfo['price'], 2)) {
            throw new ValidateException('砍价未成功');
        }
        $unique = $attrInfo['unique'];
        if ($cartNum > $attrInfo['quota']) {
            throw new ValidateException('该商品库存不足' . $cartNum);
        }
        return [$attrInfo, $unique, $productInfo, $bargainUserInfo];
    }
}
