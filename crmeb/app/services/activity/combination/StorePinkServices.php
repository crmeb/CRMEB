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

use app\dao\activity\combination\StorePinkDao;
use app\jobs\PinkJob;
use app\services\BaseServices;
use app\services\order\StoreOrderRefundServices;
use app\services\order\StoreOrderServices;
use app\services\other\PosterServices;
use app\services\other\QrcodeServices;
use app\services\system\attachment\SystemAttachmentServices;
use app\services\user\UserServices;
use crmeb\exceptions\ApiException;
use crmeb\services\CacheService;
use crmeb\services\app\MiniProgramService;
use app\services\other\UploadService;
use Guzzle\Http\EntityBody;

/**
 *
 * Class StorePinkServices
 * @package app\services\activity
 * @method getPinkCount(array $where)
 * @method int count(array $where = []) 获取指定条件下的条数
 * @method getPinkOkSumTotalNum()
 * @method isPink(int $id, int $uid) 是否能继续拼团
 * @method getPinkUserOne(int $id) 拼团
 * @method getCount(array $where) 获取某些条件总数
 * @method value(array $where, string $field)
 * @method getColumn(array $where, string $field, ?string $key)
 * @method update(array $where, array $data)
 */
class StorePinkServices extends BaseServices
{

    /**
     * StorePinkServices constructor.
     * @param StorePinkDao $dao
     */
    public function __construct(StorePinkDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * @param array $where
     * @return array
     */
    public function systemPage(array $where)
    {
        $where['k_id'] = 0;
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, $page, $limit);
        foreach ($list as &$item) {
            $item['count_people'] = $this->dao->count(['k_id' => $item['id']]) + 1;
            $item['_add_time'] = $item['add_time'] ? date('Y-m-d H:i:s', (int)$item['add_time']) : '';
            $item['_stop_time'] = $item['stop_time'] ? date('Y-m-d H:i:s', (int)$item['stop_time']) : '';
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 拼团列表头部
     * @return array
     */
    public function getStatistics()
    {
        $res = [
            ['col' => 6, 'count' => $this->dao->count(), 'name' => '参与人数(人)', 'className' => 'ios-speedometer-outline'],
            ['col' => 6, 'count' => $this->dao->count(['k_id' => 0, 'status' => 2]), 'name' => '成团数量(个)', 'className' => 'md-rose'],
        ];
        return compact('res');
    }

    /**
     * 参团人员
     * @param int $id
     * @return array
     */
    public function getPinkMember(int $id)
    {
        return $this->dao->getList(['k_id' => $id, 'is_refund' => 0]);
    }

    /**
     * 拼团退款
     * @param $id
     * @return bool
     */
    public function setRefundPink($order)
    {
        $res = true;
        if ($order['pink_id']) {
            $id = $order['pink_id'];
        } else {
            return true;
        }
        //正在拼团 团长
        $count = $this->dao->getOne(['id' => $id, 'uid' => $order['uid']]);
        //正在拼团 团员
        $countY = $this->dao->getOne(['k_id' => $id, 'uid' => $order['uid']]);
        if (!$count && !$countY) {
            return $res;
        }
        if ($count) {//团长
            //判断团内是否还有其他人  如果有  团长为第二个进团的人
            $kCount = $this->dao->getPinking(['k_id' => $id]);
            if ($kCount) {
                $res11 = $this->dao->update($id, ['k_id' => $kCount['id']], 'k_id');
                $res12 = $this->dao->update($kCount['id'], ['stop_time' => $count['add_time'] + 86400, 'k_id' => 0]);
                $res1 = $res11 && $res12;
                $res2 = $this->dao->update($id, ['stop_time' => time() - 1, 'k_id' => 0, 'is_refund' => $kCount['id'], 'status' => 3]);
            } else {
                $res1 = true;
                $res2 = $this->dao->update($id, ['stop_time' => time() - 1, 'k_id' => 0, 'is_refund' => $id, 'status' => 3]);
            }
            //修改结束时间为前一秒  团长ID为0
            $res = $res1 && $res2;
        } else if ($countY) {//团员
            $res = $this->dao->update($countY['id'], ['stop_time' => time() - 1, 'k_id' => 0, 'is_refund' => $id, 'status' => 3]);
        }
        if ($res) {
            CacheService::setStock(md5((string)$id), 1, 3, false);
        }
        return $res;
    }

    /**
     * 拼团详情查看拼团列表
     * @param int $id
     * @param bool $type
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getPinkList(int $id, bool $type)
    {
        $where['cid'] = $id;
        $where['k_id'] = 0;
        $where['is_refund'] = 0;
        $list = $this->dao->pinkList($where);
        $ids = array_column($list, 'id');
        $counts = $this->dao->getPinkPeopleCount($ids);
        if ($type) {
            $pinkAll = [];
            foreach ($list as &$v) {
                $v['count'] = $v['people'] - $counts[$v['id']];
                $v['h'] = date('H', (int)$v['stop_time']);
                $v['i'] = date('i', (int)$v['stop_time']);
                $v['s'] = date('s', (int)$v['stop_time']);
                $pinkAll[] = $v['id'];//开团团长ID
                $v['stop_time'] = (int)$v['stop_time'];
            }
            return [$list, $pinkAll];
        }
        return $list;
    }

    /**
     * 获取成团列表信息
     * @param int $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getPinkOkList(int $uid)
    {
        $list = $this->dao->successList($uid);
        $msg = [];
        foreach ($list as &$item) {
            if (isset($item['nickname'])) $msg[] = $item['nickname'] .= '拼团成功';
        }
        return $msg;
    }

    /**
     * 查找拼团信息
     * @param $pink
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getPinkMemberAndPinkK($pink)
    {
        //查找拼团团员和团长
        if ($pink['k_id']) {
            $pinkAll = $this->dao->getPinkUserList(['k_id' => $pink['k_id'], 'is_refund' => 0]);
            $pinkT = $this->dao->getPinkUserOne($pink['k_id']);
        } else {
            $pinkAll = $this->dao->getPinkUserList(['k_id' => $pink['id'], 'is_refund' => 0]);
            $pinkT = $pink;
        }
        $count = count($pinkAll) + 1;
        $count = $pinkT['people'] - $count;
        $idAll = [];
        $uidAll = [];
        //收集拼团用户id和拼团id
        foreach ($pinkAll as $k => $v) {
            $idAll[$k] = $v['id'];
            $uidAll[$k] = $v['uid'];
        }
        $idAll[] = $pinkT['id'];
        $uidAll[] = $pinkT['uid'];
        return [$pinkAll, $pinkT, $count, $idAll, $uidAll];
    }

    /**
     * 拼团失败
     * @param $pinkAll
     * @param $pinkT
     * @param $pinkBool
     * @param bool $isRunErr
     * @param bool $isIds
     * @return array|int
     */
    public function pinkFail($pinkAll, $pinkT, $pinkBool, $isRunErr = true, $isIds = false)
    {
        /** @var StoreOrderServices $orderService */
        $orderService = app()->make(StoreOrderServices::class);
        /** @var StoreOrderRefundServices $orderRefundService */
        $orderRefundService = app()->make(StoreOrderRefundServices::class);
        $pinkIds = [];
        try {
            if ($pinkT['stop_time'] < time()) {//拼团时间超时  退款
                $virtual = $this->virtualCombination($pinkT['id']);
                if ($virtual) return 1;
                $pinkBool = -1;
                array_push($pinkAll, $pinkT);
                $oids = array_column($pinkAll, 'order_id_key');
                $orders = $orderService->getColumn([['id', 'in', $oids]], '*', 'id');
                $refundData = [
                    'refund_reason' => '拼团时间超时',
                    'refund_explain' => '拼团时间超时',
                    'refund_img' => json_encode([]),
                ];
                foreach ($pinkAll as $v) {
                    if (isset($orders[$v['order_id_key']]) && $order = $orders[$v['order_id_key']]) {
                        $res1 = $res2 = true;
                        if (!in_array($order['refund_status'], [1, 2])) {
                            $res1 = $orderRefundService->applyRefund((int)$order['id'], (int)$order['uid'], $order, [], 1, (float)$order['pay_price'], $refundData, 1);
                        }
                        $res2 = $this->dao->getCount([['uid', '=', $v['uid']], ['is_tpl', '=', 0], ['k_id|id', '=', $pinkT['id']]]);
                        if ($res1 && $res2) {
                            if ($isIds) array_push($pinkIds, $v['id']);
                            $this->orderPinkAfterNo($pinkT['uid'], $pinkT['id'], false, $orders[$v['order_id_key']]['is_channel']);
                        } else {
                            if ($isRunErr) return $pinkBool;
                        }
                    }
                }
            }
            if ($isIds) return $pinkIds;
            return $pinkBool;
        } catch (\Exception $e) {
            return $pinkBool;
        }
    }

    /**
     * 失败发送消息和修改状态
     * @param $uid
     * @param $pid
     * @param bool $isRemove
     * @param $channel
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function orderPinkAfterNo($uid, $pid, $isRemove = false, $channel)
    {
        $pink = $this->dao->getOne([['id|k_id', '=', $pid], ['uid', '=', $uid]], '*', ['getProduct']);
        if ($isRemove) {
            event('notice.notice', [['uid' => $uid, 'pink' => $pink, 'user_type' => $channel], 'send_order_pink_clone']);
        } else {
            event('notice.notice', [['uid' => $uid, 'pink' => $pink, 'user_type' => $channel], 'send_order_pink_fial']);
        }
        $this->dao->update([['id|k_id', '=', $pid]], ['status' => 3, 'stop_time' => time()]);
    }


    /**
     * 判断拼团状态
     * @param $pinkId
     * @return bool
     */
    public function isPinkStatus($pinkId)
    {
        if (!$pinkId) return false;
        $stopTime = $this->dao->value(['id' => $pinkId], 'stop_time');
        if ($stopTime < time()) return true; //拼团结束
        else return false;//拼团未结束
    }

    /**
     * 获取拼团order_id
     * @param int $id
     * @param int $uid
     * @return mixed
     */
    public function getCurrentPink(int $id, int $uid)
    {
        $oid = $this->dao->value(['id' => $id, 'uid' => $uid], 'order_id_key');
        if (!$oid) $oid = $this->dao->value(['k_id' => $id, 'uid' => $uid], 'order_id_key');
        /** @var StoreOrderServices $orderService */
        $orderService = app()->make(StoreOrderServices::class);
        return $orderService->value(['id' => $oid], 'order_id');
    }

    /**
     * 拼团成功
     * @param $uidAll
     * @param $idAll
     * @param $uid
     * @param $pinkT
     * @return int
     */
    public function pinkComplete($uidAll, $idAll, $uid, $pinkT)
    {
        $pinkBool = 6;
        try {
            if (!$this->dao->getCount([['id', 'in', $idAll], ['is_refund', '=', 1]])) {
                $this->dao->update([['id', 'in', $idAll]], ['stop_time' => time(), 'status' => 2]);
                if (in_array($uid, $uidAll)) {
                    if ($this->dao->getCount([['uid', 'in', $uidAll], ['is_tpl', '=', 0], ['k_id|id', '=', $pinkT['id']]]))
                        $this->orderPinkAfter($uidAll, $pinkT['id']);
                    $pinkBool = 1;
                } else  $pinkBool = 3;
            }
            return $pinkBool;
        } catch (\Exception $e) {
            return $pinkBool;
        }
    }

    /**
     * 拼团成功修改
     * @param $uidAll
     * @param $pid
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function orderPinkAfter($uidAll, $pid)
    {
        //发送消息之前去除虚拟用户
        foreach ($uidAll as $key => $uid) {
            if ($uid == 0) unset($uidAll[$key]);
        }
        /** @var StoreCombinationServices $storeCombinationServices */
        $storeCombinationServices = app()->make(StoreCombinationServices::class);
        $title = $storeCombinationServices->value(['id' => $this->dao->value(['id' => $pid], 'cid')], 'title');
        $pinkList = $this->dao->getColumn([['id|k_id', '=', $pid], ['uid', '<>', 0]], '*', 'uid');
        $pinkT_name = $this->dao->value(['id' => $pid], 'nickname');
        $order_ids = array_column($pinkList, 'order_id');
        /** @var StoreOrderServices $orderService */
        $orderService = app()->make(StoreOrderServices::class);
        $order_channels = $orderService->getColumn([['order_id', 'in', $order_ids]], 'is_channel', 'order_id');
        if (!$pinkList) return false;
        foreach ($pinkList as $item) {
            $item['nickname'] = $pinkT_name;
            //用户发送消息
            event('notice.notice', [
                [
                    'list' => $item,
                    'title' => $title,
                    'user_type' => $order_channels[$item['order_id']],
                    'url' => '/pages/users/order_details/index?order_id=' . $item['order_id']
                ], 'order_user_groups_success']);
        }
        $this->dao->update([['uid', 'in', $uidAll], ['id|k_id', '=', $pid]], ['is_tpl' => 1]);
    }

    /**
     * 创建拼团
     * @param $order
     * @return mixed
     */
    public function createPink(array $orderInfo)
    {
        /** @var StoreCombinationServices $services */
        $services = app()->make(StoreCombinationServices::class);
        $product = $services->getOne(['id' => $orderInfo['combination_id']], 'effective_time,title,people');
        if (!$product) {
            return false;
        }
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->get($orderInfo['uid']);
        if ($orderInfo['pink_id']) {
            //拼团存在
            $res = false;
            $pink['uid'] = $orderInfo['uid'];//用户id
            $pink['nickname'] = $userInfo['nickname'];
            $pink['avatar'] = $userInfo['avatar'];
            if ($this->isPinkBe($pink, $orderInfo['pink_id'])) return false;
            $pink['order_id'] = $orderInfo['order_id'];//订单id  生成
            $pink['order_id_key'] = $orderInfo['id'];//订单id  数据库id
            $pink['total_num'] = $orderInfo['total_num'];//购买个数
            $pink['total_price'] = $orderInfo['pay_price'];//总金额
            $pink['k_id'] = $orderInfo['pink_id'];//拼团id
            foreach ($orderInfo['cartInfo'] as $v) {
                $pink['cid'] = $v['combination_id'];//拼团商品id
                $pink['pid'] = $v['product_id'];//商品id
                $pink['people'] = $product['people'];//几人拼团
                $pink['price'] = $v['productInfo']['price'];//单价
                $pink['stop_time'] = 0;//结束时间
                $pink['add_time'] = time();//开团时间
                $res = $this->save($pink);
            }
            // 拼团团成功发送模板消息
            event('notice.notice', [['orderInfo' => $orderInfo, 'title' => $product['title'], 'pink' => $pink], 'can_pink_success']);

            //处理拼团完成
            list($pinkAll, $pinkT, $count, $idAll, $uidAll) = $this->getPinkMemberAndPinkK($pink);
            if ($pinkT['status'] == 1) {
                if (!$count)//组团完成
                    $this->pinkComplete($uidAll, $idAll, $pink['uid'], $pinkT);
                else
                    $this->pinkFail($pinkAll, $pinkT, 0);
            }

            if ($res) return true;
            else return false;
        } else {
            //创建拼团
            $res = false;
            $pink['uid'] = $orderInfo['uid'];//用户id
            $pink['nickname'] = $userInfo['nickname'];
            $pink['avatar'] = $userInfo['avatar'];
            $pink['order_id'] = $orderInfo['order_id'];//订单id  生成
            $pink['order_id_key'] = $orderInfo['id'];//订单id  数据库id
            $pink['total_num'] = $orderInfo['total_num'];//购买个数
            $pink['total_price'] = $orderInfo['pay_price'];//总金额
            $pink['k_id'] = 0;//拼团id
            /** @var StoreOrderServices $orderServices */
            $orderServices = app()->make(StoreOrderServices::class);
            foreach ($orderInfo['cartInfo'] as $v) {
                $pink['cid'] = $v['combination_id'];//拼团商品id
                $pink['pid'] = $v['product_id'];//商品id
                $pink['people'] = $product['people'];//几人拼团
                $pink['price'] = $v['productInfo']['price'];//单价
                $pink['stop_time'] = time() + $product->effective_time * 3600;//结束时间
                $pink['add_time'] = time();//开团时间
                $res1 = $this->dao->save($pink);
                $res2 = $orderServices->update($orderInfo['id'], ['pink_id' => $res1['id']]);
                $res = $res1 && $res2;
                $pink['id'] = $res1['id'];
            }

            $number = (int)bcsub((string)$pink['people'], '1', 0);
            if ($number) CacheService::setStock(md5($pink['id']), $number, 3);

            PinkJob::dispatchSecs((int)(($product->effective_time * 3600) + 60), [$pink['id']]);
            // 开团成功发送模板消息
            event('notice.notice', [['orderInfo' => $orderInfo, 'title' => $product['title'], 'pink' => $pink], 'open_pink_success']);

            if ($res) return true;
            else return false;
        }
    }

    /**
     * 是否拼团
     * @param array $data
     * @param int $id
     * @return int
     */
    public function isPinkBe(array $data, int $id)
    {
        $data['id'] = $id;
        $count = $this->dao->getCount($data);
        if ($count) return $count;
        $data['k_id'] = $id;
        $count = $this->dao->getCount($data);
        if ($count) return $count;
        else return 0;
    }

    /**
     * 取消拼团
     * @param int $uid
     * @param int $cid
     * @param int $pink_id
     * @param null $nextPinkT
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function removePink(int $uid, int $cid, int $pink_id, $nextPinkT = null)
    {
        $pinkT = $this->dao->getOne([
            ['uid', '=', $uid],
            ['id', '=', $pink_id],
            ['cid', '=', $cid],
            ['k_id', '=', 0],
            ['is_refund', '=', 0],
            ['status', '=', 1],
            ['stop_time', '>', time()],
        ]);
        if (!$pinkT) throw new ApiException(410314);
        list($pinkAll, $pinkT, $count, $idAll, $uidAll) = $this->getPinkMemberAndPinkK($pinkT);
        if (count($pinkAll)) {
            $count = $pinkT['people'] - ($this->dao->count(['k_id' => $pink_id, 'is_refund' => 0]) + 1);
            if ($count) {
                //拼团未完成，拼团有成员取消开团取 紧跟团长后拼团的人
                if (isset($pinkAll[0])) $nextPinkT = $pinkAll[0];
            } else {
                //拼团完成
                $this->PinkComplete($uidAll, $idAll, $uid, $pinkT);
                throw new ApiException(410316);
            }
        }
        /** @var StoreOrderServices $orderService */
        $orderService = app()->make(StoreOrderServices::class);
        /** @var StoreOrderRefundServices $orderRefundService */
        $orderRefundService = app()->make(StoreOrderRefundServices::class);
        //取消开团
        $order = $orderService->get($pinkT['order_id_key']);
        $refundData = [
            'refund_reason' => '用户手动取消拼团',
            'refund_explain' => '用户手动取消拼团',
            'refund_img' => json_encode([]),
        ];
        $res1 = $orderRefundService->applyRefund((int)$order['id'], (int)$order['uid'], $order, [], 1, (float)$order['pay_price'], $refundData, 1);
        $res2 = $this->dao->getCount([['uid', '=', $pinkT['uid']], ['k_id|id', '=', $pinkT['id']]]);
        if ($res1 && $res2) {
            $this->orderPinkAfterNo($pinkT['uid'], $pinkT['id'], true, $order->is_channel);
        }
        //当前团有人的时候
        if (is_array($nextPinkT)) {
            $this->dao->update($nextPinkT['id'], ['k_id' => 0, 'status' => 1, 'stop_time' => $pinkT['stop_time']]);
            $this->dao->update($pinkT['id'], ['k_id' => $nextPinkT['id']], 'k_id');
            $orderService->update($nextPinkT['order_id'], ['pink_id' => $nextPinkT['id']], 'order_id');
        }
        return true;
    }

    /**
     * 获取拼团海报
     * @param $pinkId
     * @param $from
     * @param $user
     * @return string
     */
    public function getPinkPoster($pinkId, $from, $user)
    {
        $pinkInfo = $this->dao->get((int)$pinkId);
        /** @var StoreCombinationServices $combinationService */
        $combinationService = app()->make(StoreCombinationServices::class);
        $storeCombinationInfo = $combinationService->getOne(['id' => $pinkInfo['cid']], '*', ['getPrice']);
        $data['title'] = $storeCombinationInfo['title'];
        $data['image'] = $storeCombinationInfo['image'];
        $data['price'] = $pinkInfo['price'];
        $data['label'] = $pinkInfo['people'] . '人团';
        if ($pinkInfo['k_id']) $pinkAll = $this->getPinkMember($pinkInfo['k_id']);
        else $pinkAll = $this->getPinkMember($pinkInfo['id']);
        $count = count($pinkAll);
        $data['msg'] = '原价￥' . $storeCombinationInfo['product_price'] . ' 还差' . ($pinkInfo['people'] - $count) . '人拼团成功';

        /** @var SystemAttachmentServices $systemAttachmentServices */
        $systemAttachmentServices = app()->make(SystemAttachmentServices::class);

        try {
            $siteUrl = sys_config('site_url');
            if ($from == 'routine') {
                //小程序
                $name = $pinkId . '_' . $user['uid'] . '_' . $user['is_promoter'] . '_pink_share_routine.jpg';
                $imageInfo = $systemAttachmentServices->getInfo(['name' => $name]);
                if (!$imageInfo) {
                    $valueData = 'id=' . $pinkId;
                    /** @var UserServices $userServices */
                    $userServices = app()->make(UserServices::class);
                    if ($userServices->checkUserPromoter((int)$user['uid'], $user)) {
                        $valueData .= '&pid=' . $user['uid'];
                    }
                    $res = MiniProgramService::appCodeUnlimitService($valueData, 'pages/activity/goods_combination_status/index', 280);
                    if (!$res) throw new ApiException(410167);
                    $uploadType = (int)sys_config('upload_type', 1);
                    $upload = UploadService::init();
                    $res = (string)EntityBody::factory($res);
                    $res = $upload->to('routine/activity/pink/code')->validate()->setAuthThumb(false)->stream($res, $name);
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
                $data['url'] = $url;
                if ($imageInfo['image_type'] == 1)
                    $data['url'] = $siteUrl . $url;
                $posterImage = PosterServices::setShareMarketingPoster($data, 'routine/activity/pink/poster');
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
            } else if ($from == 'wechat') {
                //公众号
                $name = $pinkId . '_' . $user['uid'] . '_' . $user['is_promoter'] . '_pink_share_wap.jpg';
                $imageInfo = $systemAttachmentServices->getInfo(['name' => $name]);
                if (!$imageInfo) {
                    $codeUrl = set_http_type($siteUrl . '/pages/activity/goods_combination_status/index?id=' . $pinkId . '&spread=' . $user['uid'], 1);//二维码链接
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
                $posterImage = PosterServices::setShareMarketingPoster($data, 'wap/activity/pink/poster');
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
                $wapPosterImage = set_http_type($posterImage['dir'], 1);//公众号推广海报
                return $wapPosterImage;
            }
            throw new ApiException(100100);
        } catch (\Exception $e) {
            throw new ApiException($e->getMessage());
        }
    }

    /**
     * 修改到期的拼团状态
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function statusPink()
    {
        $pinkListEnd = $this->dao->pinkListEnd();
        foreach ($pinkListEnd as $key => $pink) {
            [$pinkAll, $pinkT, $count, $idAll, $uidAll] = $this->getPinkMemberAndPinkK($pink);
            $this->pinkFail($pinkAll, $pinkT, 0);
        }
        return true;
    }

    /**
     * 拼团成功
     * @param array $pinkRegimental 成功的团长编号
     * @return bool
     * @throws \Exception
     */
    public function successPinkEdit(array $pinkRegimental)
    {
        if (!count($pinkRegimental)) return true;
        foreach ($pinkRegimental as $key => &$item) {
            $pinkList = $this->dao->getColumn(['k_id' => $item], 'id', 'id');
            $pinkList[] = $item;
            $pinkList = implode(',', $pinkList);
            $this->dao->update([['id', 'in', $pinkList]], ['stop_time' => time(), 'status' => 2]);
            $pinkUidList = $this->dao->getColumn([['id', 'in', $pinkList], ['is_tpl', '=', 0]], 'uid', 'uid');
            if (count($pinkUidList)) $this->orderPinkAfter($pinkUidList, $item);//发送模板消息
        }
        return true;
    }

    /**
     * 拼团失败
     * @param array $pinkRegimental 失败的团长编号
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function failPinkEdit(array $pinkRegimental)
    {
        if (!count($pinkRegimental)) return true;
        foreach ($pinkRegimental as $key => &$item) {
            $pinkList = $this->dao->getColumn(['k_id' => $item], 'id', 'id');
            $pinkList[] = $item;
            $pinkList = implode(',', $pinkList);
            $refundPinkList = $this->dao->getColumn([['id', 'in', $pinkList]], 'order_id,uid', 'id');
            if ($refundPinkList) {
                /** @var StoreOrderRefundServices $orderRefundService */
                $orderRefundService = app()->make(StoreOrderRefundServices::class);
                $refundData = [
                    'refund_reason' => '拼团时间超时',
                    'refund_explain' => '拼团时间超时',
                    'refund_img' => json_encode([]),
                ];
                foreach ($refundPinkList as &$items) {
                    $orderRefundService->applyRefund((int)$items['id'], (int)$items['uid'], $items, [], 1, (float)$items['pay_price'], $refundData, 1);//申请退款
                }
            }
            $this->dao->update([['id', 'in', $pinkList]], ['status' => 3]);
        }
        return true;
    }

    /**
     * 虚拟拼团
     * @param $pinkId
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function virtualCombination($pinkId)
    {
        $pinkInfo = $this->dao->get($pinkId);
        $people = $pinkInfo['people'];
        $count = $this->dao->count(['k_id' => $pinkId]) + 1;
        $percent1 = bcdiv((string)$count, (string)$people, 2) * 100;
        /** @var StoreCombinationServices $services */
        $services = app()->make(StoreCombinationServices::class);
        $percent2 = $services->value(['id' => $pinkInfo['cid']], 'virtual');
        if ($percent1 >= $percent2) {
            $time = time();
            $num = $people - $count;
            $data = [];
            for ($i = 0; $i < $num; $i++) {
                $data[$i]['uid'] = 0;
                $data[$i]['nickname'] = substr(md5(time() . rand(1000, 9999)), 0, 12);
                $data[$i]['avatar'] = sys_config('h5_avatar');
                $data[$i]['order_id'] = 0;
                $data[$i]['order_id_key'] = 0;
                $data[$i]['total_num'] = 0;
                $data[$i]['total_price'] = 0;
                $data[$i]['cid'] = $pinkInfo['cid'];
                $data[$i]['pid'] = $pinkInfo['pid'];
                $data[$i]['people'] = $people;
                $data[$i]['price'] = 0;
                $data[$i]['add_time'] = $time;
                $data[$i]['stop_time'] = $time;
                $data[$i]['k_id'] = $pinkInfo['id'];
                $data[$i]['is_tpl'] = 1;
                $data[$i]['is_refund'] = 0;
                $data[$i]['status'] = 2;
                $data[$i]['is_virtual'] = 1;
            }
            //添加虚拟团员
            $this->dao->saveAll($data);
            //更改团员状态为拼团成功
            $this->dao->update($pinkId, ['stop_time' => $time, 'status' => 2], 'k_id');
            //更改团长为拼团成功
            $this->dao->update($pinkId, ['stop_time' => $time, 'status' => 2]);
            $uidAll = $this->dao->getColumn([['id|k_id', '=', $pinkId]], 'uid');
            $this->orderPinkAfter($uidAll, $pinkId);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取拼团海报详情信息
     * @param int $id
     * @param $user
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function posterInfo(int $id, $user)
    {
        $pinkInfo = $this->dao->get($id);
        /** @var StoreCombinationServices $combinationService */
        $combinationService = app()->make(StoreCombinationServices::class);
        $storeCombinationInfo = $combinationService->getOne(['id' => $pinkInfo['cid']], '*', ['getPrice']);
        $data['title'] = $storeCombinationInfo['title'];
        $data['url'] = '';
        $data['image'] = $storeCombinationInfo['image'];
        $data['price'] = $pinkInfo['price'];
        $data['label'] = $pinkInfo['people'] . '人团';
        if ($pinkInfo['k_id']) $pinkAll = $this->getPinkMember($pinkInfo['k_id']);
        else $pinkAll = $this->getPinkMember($pinkInfo['id']);
        $count = count($pinkAll);
        $data['msg'] = '原价￥' . $storeCombinationInfo['product_price'] . ' 还差' . ($pinkInfo['people'] - $count) . '人拼团成功';

        /** @var SystemAttachmentServices $systemAttachmentServices */
        $systemAttachmentServices = app()->make(SystemAttachmentServices::class);

        try {
            $siteUrl = sys_config('site_url');
            if (request()->isRoutine()) {
                //小程序
                $name = $id . '_' . $user['uid'] . '_' . $user['is_promoter'] . '_pink_share_routine.jpg';
                $imageInfo = $systemAttachmentServices->getInfo(['name' => $name]);
                if (!$imageInfo) {
                    $valueData = 'id=' . $id;
                    /** @var UserServices $userServices */
                    $userServices = app()->make(UserServices::class);
                    if ($userServices->checkUserPromoter((int)$user['uid'], $user)) {
                        $valueData .= '&pid=' . $user['uid'];
                    }
                    $res = MiniProgramService::appCodeUnlimitService($valueData, 'pages/activity/goods_combination_status/index', 280);
                    if (!$res) throw new ApiException(410167);
                    $uploadType = (int)sys_config('upload_type', 1);
                    $upload = UploadService::init();
                    $res = $upload->to('routine/activity/pink/code')->validate()->setAuthThumb(false)->stream($res, $name);
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
                $data['url'] = $url;
                if ($imageInfo['image_type'] == 1)
                    $data['url'] = $siteUrl . $url;
            } else {
                if (sys_config('share_qrcode', 0) && request()->isWechat()) {
                    /** @var QrcodeServices $qrcodeService */
                    $qrcodeService = app()->make(QrcodeServices::class);
                    $data['url'] = $qrcodeService->getTemporaryQrcode('pink-' . $id, $user['uid'])->url;
                }
            }
        } catch (\Throwable $e) {
        }
        return $data;
    }
}
