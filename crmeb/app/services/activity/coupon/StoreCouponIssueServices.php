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
declare (strict_types = 1);

namespace app\services\activity\coupon;

use app\services\BaseServices;
use app\dao\activity\coupon\StoreCouponIssueDao;
use app\services\order\StoreCartServices;
use app\services\product\product\StoreCategoryServices;
use app\services\product\product\StoreProductServices;
use app\services\user\member\MemberCardServices;
use app\services\user\member\MemberRightServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder;
use think\exception\ValidateException;

/**
 *
 * Class StoreCouponIssueServices
 * @package app\services\coupon
 * @method getUserIssuePrice(string $price) 获取金大于额的优惠卷金额
 * @method getCouponInfo($id)
 * @method getColumn(array $where, string $field, ?string $key)
 * @method productCouponList(array $where, string $field)
 * @method checkProductCoupon($product_id)
 */
class StoreCouponIssueServices extends BaseServices
{

    public $_couponType = [0 => "通用券", 1 => "品类券", 2 => '商品券'];

    /**
     * StoreCouponIssueServices constructor.
     * @param StoreCouponIssueDao $dao
     */
    public function __construct(StoreCouponIssueDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取已发布列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCouponIssueList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $where['is_del'] = 0;
        $list = $this->dao->getList($where, $page, $limit);
        foreach ($list as &$item) {
            $item['use_time'] = date('Y-m-d', $item['start_use_time']) . ' ~ ' . date('Y-m-d', $item['end_use_time']);
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**获取会员优惠券列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getMemberCouponIssueList(array $where)
    {
        return $this->dao->getApiIssueList($where);
    }

    /**
     * 新增优惠券
     * @param $data
     * @return bool
     */
    public function saveCoupon($data)
    {
        $data['start_use_time'] = strtotime((string)$data['start_use_time']);
        $data['end_use_time'] = strtotime((string)$data['end_use_time']);
        $data['start_time'] = strtotime((string)$data['start_time']);
        $data['end_time'] = strtotime((string)$data['end_time']);
        $data['title'] = $data['coupon_title'];
        $data['remain_count'] = $data['total_count'];
        if ($data['receive_type'] == 2 || $data['receive_type'] == 3) {
            $data['is_permanent'] = 1;
            $data['total_count'] = 0;
        }
        $data['add_time'] = time();
        $res = $this->dao->save($data);
        if ($data['product_id'] !== '' && $res) {
            $productIds = explode(',', $data['product_id']);
            $couponData = [];
            foreach ($productIds as $product_id) {
                $couponData[] = ['product_id' => $product_id, 'coupon_id' => $res->id];
            }
            /** @var StoreCouponProductServices $storeCouponProductService */
            $storeCouponProductService = app()->make(StoreCouponProductServices::class);
            $storeCouponProductService->saveAll($couponData);
        }
        if (!$res) throw new AdminException('添加优惠券失败!');
        return true;
    }


    /**
     * 修改状态
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function createForm(int $id)
    {
        $issueInfo = $this->dao->get($id);
        if (-1 == $issueInfo['status'] || 1 == $issueInfo['is_del']) return app('json')->fail('状态错误,无法修改');
        $f = [FormBuilder::radio('status', '是否开启', $issueInfo['status'])->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]])];
        return create_form('状态修改', $f, $this->url('/marketing/coupon/released/status/' . $id), 'PUT');
    }

    /**
     * 领取记录
     * @param int $id
     * @return array
     */
    public function issueLog(int $id)
    {
        $coupon = $this->dao->get($id);
        if (!$coupon) {
            throw new ValidateException('优惠券不存在');
        }
        if ($coupon['receive_type'] != 4) {
            /** @var StoreCouponIssueUserServices $storeCouponIssueUserService */
            $storeCouponIssueUserService = app()->make(StoreCouponIssueUserServices::class);
            return $storeCouponIssueUserService->issueLog(['issue_coupon_id' => $id]);
        } else {//会员券
            /** @var StoreCouponUserServices $storeCouponUserService */
            $storeCouponUserService = app()->make(StoreCouponUserServices::class);
            return $storeCouponUserService->issueLog(['cid' => $id]);
        }

    }

    /**
     * 关注送优惠券
     * @param int $uid
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function userFirstSubGiveCoupon(int $uid)
    {
        $couponList = $this->dao->getGiveCoupon(['receive_type' => 2]);
        $this->giveUserCoupon($uid, $couponList ?: []);
        return true;
    }

    /**
     * 订单金额达到预设金额赠送优惠卷
     * @param $uid
     * @param $total_price
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function userTakeOrderGiveCoupon($uid, $total_price)
    {
        $couponList = $this->dao->getGiveCoupon([['is_full_give', '=', 1], ['full_reduction', '<=', $total_price]]);
        $this->giveUserCoupon((int)$uid, $couponList ?: []);
        return true;
    }

    /**
     * 下单之后赠送
     * @param $uid
     * @param $coupon_issue_ids 订单商品关联优惠券ids
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function orderPayGiveCoupon($uid, $coupon_issue_ids)
    {
        if (!$coupon_issue_ids) return [];
        $couponList = $this->dao->getGiveCoupon([['id', 'IN', $coupon_issue_ids]]);
        [$couponData, $issueUserData] = $this->giveUserCoupon($uid, $couponList ?: []);
        return $couponData;
    }

    /**
     * 发送优惠券
     * @param int $uid 发放人id
     * @param array $couponList 发送优惠券数据
     * @return array[]
     */
    public function giveUserCoupon(int $uid, array $couponList)
    {
        $couponData = $issueUserData = [];
        if ($uid && $couponList) {
            $time = time();
            $ids = array_column($couponList, 'id');
            /** @var StoreCouponIssueUserServices $issueUser */
            $issueUser = app()->make(StoreCouponIssueUserServices::class);
            $userCouponIds = $issueUser->getColumn([['uid', '=', $uid], ['issue_coupon_id', 'in', $ids]], 'issue_coupon_id') ?? [];
            foreach ($couponList as $item) {
                if (!$userCouponIds || !in_array($item['id'], $userCouponIds)) {
                    $data['cid'] = $item['id'];
                    $data['uid'] = $uid;
                    $data['coupon_title'] = $item['title'];
                    $data['coupon_price'] = $item['coupon_price'];
                    $data['use_min_price'] = $item['use_min_price'];
                    if ($item['coupon_time']) {
                        $data['add_time'] = $time;
                        $data['end_time'] = $data['add_time'] + $item['coupon_time'] * 86400;
                    } else {
                        $data['add_time'] = $item['start_use_time'];
                        $data['end_time'] = $item['end_use_time'];
                    }
                    $data['type'] = 'get';
                    $issue['uid'] = $uid;
                    $issue['issue_coupon_id'] = $item['id'];
                    $issue['add_time'] = $time;
                    $issueUserData[] = $issue;
                    $couponData[] = $data;
                    unset($data);
                    unset($issue);
                }
            }
            if ($couponData) {
                /** @var StoreCouponUserServices $storeCouponUser */
                $storeCouponUser = app()->make(StoreCouponUserServices::class);
                if (!$storeCouponUser->saveAll($couponData)) {
                    throw new AdminException('发劵失败');
                }
            }
            if ($issueUserData) {
                if (!$issueUser->saveAll($issueUserData)) {
                    throw new AdminException('发劵失败');
                }
            }
        }
        return [$couponData, $issueUserData];
    }

    /**
     * 获取优惠券列表
     * @param int $uid
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getIssueCouponList(int $uid, array $where)
    {
        [$page, $limit] = $this->getPageValue();
        if ($where['product_id'] == 0) {
            $typeId = 0;
            $cateId = 0;
            if ($where['type'] == -1) { // PC端获取优惠券
                $list = $this->dao->getPcIssueCouponList($uid, [], 0, 0);
            } else {
                $list = $this->dao->getIssueCouponList($uid, (int)$where['type'], $typeId, $page, $limit);
                if (!$list) $list = $this->dao->getIssueCouponList($uid, 1, $typeId, $page, $limit);
                if (!$list) $list = $this->dao->getIssueCouponList($uid, 2, $typeId, $page, $limit);
            }
        } else {
            /** @var StoreProductServices $storeProductService */
            $storeProductService = app()->make(StoreProductServices::class);
            /** @var StoreCategoryServices $storeCategoryService */
            $storeCategoryService = app()->make(StoreCategoryServices::class);

            $cateId = $storeProductService->value(['id' => $where['product_id']], 'cate_id');
            $cateId = explode(',', $cateId);
            $cateId = array_merge($cateId, $storeCategoryService->cateIdByPid($cateId));
            $cateId = array_diff($cateId, [0]);
            if ($where['type'] == -1) { // PC端获取优惠券
                $list = $this->dao->getPcIssueCouponList($uid, $cateId, $where['product_id']);
            } else {
                if ($where['type'] == 1) {
                    $typeId = $cateId;
                } elseif ($where['type'] == 2) {
                    $typeId = $where['product_id'];
                } else {
                    $typeId = 0;
                }
                $list = $this->dao->getIssueCouponList($uid, (int)$where['type'], $typeId, $page, $limit);
            }
        }
        foreach ($list as &$v) {
            $v['coupon_price'] = floatval($v['coupon_price']);
            $v['use_min_price'] = floatval($v['use_min_price']);
            $v['is_use'] = $uid && isset($v['used']);
            if ($v['end_use_time']) {
                $v['start_use_time'] = date('Y/m/d', $v['start_use_time']);
                $v['end_use_time'] = $v['end_use_time'] ? date('Y/m/d', $v['end_use_time']) : date('Y/m/d', time() + 86400);
            }
            if ($v['start_time']) {
                $v['start_time'] = date('Y/m/d', $v['start_time']);
                $v['end_time'] = date('Y/m/d', $v['end_time']);
            }
        }
        $data['list'] = $list;
        $data['count'] = $this->dao->getIssueCouponCount($where['product_id'], $cateId);
        return $data;
    }

    public function issueUserCoupon($id, $user)
    {
        $issueCouponInfo = $this->dao->getInfo((int)$id);
        $uid = $user->uid;
        if (!$issueCouponInfo) throw new ValidateException('领取的优惠劵已领完或已过期!');
        /** @var MemberRightServices $memberRightService */
        $memberRightService = app()->make(MemberRightServices::class);
        if ($issueCouponInfo->receive_type == 4 && (!$user->is_money_level || !$memberRightService->getMemberRightStatus("coupon"))) {
            if (!$user->is_money_level) throw new ValidateException('您不是付费会员!');
            if (!$memberRightService->getMemberRightStatus("coupon")) throw new ValidateException('暂时无法领取!');
        }
        /** @var StoreCouponIssueUserServices $issueUserService */
        $issueUserService = app()->make(StoreCouponIssueUserServices::class);
        /** @var StoreCouponUserServices $couponUserService */
        $couponUserService = app()->make(StoreCouponUserServices::class);
        if ($issueUserService->getOne(['uid' => $uid, 'issue_coupon_id' => $id])) throw new ValidateException('已领取过该优惠劵!');
        if ($issueCouponInfo->remain_count <= 0 && !$issueCouponInfo->is_permanent) throw new ValidateException('抱歉优惠券已经领取完了！');
        $this->transaction(function () use ($issueUserService, $uid, $id, $couponUserService, $issueCouponInfo) {
            $issueUserService->save(['uid' => $uid, 'issue_coupon_id' => $id, 'add_time' => time()]);
            $couponUserService->addUserCoupon($uid, $issueCouponInfo, "get");
            if ($issueCouponInfo['total_count'] > 0) {
                $issueCouponInfo['remain_count'] -= 1;
                $issueCouponInfo->save();
            }
        });
    }

    /**
     * 会员发放优惠期券
     * @param $id
     * @param $uid
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function memberIssueUserCoupon($id, $uid)
    {
        $issueCouponInfo = $this->dao->getInfo((int)$id);
        if ($issueCouponInfo) {
            /** @var StoreCouponIssueUserServices $issueUserService */
            $issueUserService = app()->make(StoreCouponIssueUserServices::class);
            /** @var StoreCouponUserServices $couponUserService */
            $couponUserService = app()->make(StoreCouponUserServices::class);
            if ($issueCouponInfo->remain_count >= 0 || $issueCouponInfo->is_permanent) {
                $this->transaction(function () use ($issueUserService, $uid, $id, $couponUserService, $issueCouponInfo) {
                    //$issueUserService->save(['uid' => $uid, 'issue_coupon_id' => $id, 'add_time' => time()]);
                    $couponUserService->addMemberUserCoupon($uid, $issueCouponInfo, "send");
                    // 如果会员劵需要限制数量时打开
                    if ($issueCouponInfo['total_count'] > 0) {
                        $issueCouponInfo['remain_count'] -= 1;
                        $issueCouponInfo->save();
                    }
                });
            }

        }

    }

    /**
     * 用户优惠劵列表
     * @param int $uid
     * @param $types
     * @return array
     */
    public function getUserCouponList(int $uid, $types)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        if (!$userServices->getUserInfo($uid)) {
            throw new ValidateException('数据不存在');
        }
        /** @var StoreCouponUserServices $storeConponUser */
        $storeConponUser = app()->make(StoreCouponUserServices::class);
        return $storeConponUser->getUserCounpon($uid, $types);
    }

    /**
     * 后台发送优惠券
     * @param $coupon
     * @param $user
     * @return bool
     */
    public function setCoupon($coupon, $user)
    {
        $data = [];
        $issueData = [];
        /** @var StoreCouponUserServices $storeCouponUser */
        $storeCouponUser = app()->make(StoreCouponUserServices::class);
        /** @var StoreCouponIssueUserServices $storeCouponIssueUser */
        $storeCouponIssueUser = app()->make(StoreCouponIssueUserServices::class);
        $uids = $storeCouponIssueUser->getColumn(['issue_coupon_id' => $coupon['id']], 'uid');
        foreach ($user as $k => $v) {
            if (in_array($v, $uids)) {
                continue;
            } else {
                $data[$k]['cid'] = $coupon['id'];
                $data[$k]['uid'] = $v;
                $data[$k]['coupon_title'] = $coupon['title'];
                $data[$k]['coupon_price'] = $coupon['coupon_price'];
                $data[$k]['use_min_price'] = $coupon['use_min_price'];
                $data[$k]['add_time'] = time();
                if ($coupon['coupon_time']) {
                    $data[$k]['start_time'] = $data[$k]['add_time'];
                    $data[$k]['end_time'] = $data[$k]['add_time'] + $coupon['coupon_time'] * 86400;
                } else {
                    $data[$k]['start_time'] = $coupon['start_use_time'];
                    $data[$k]['end_time'] = $coupon['end_use_time'];
                }
                $data[$k]['type'] = 'send';
                $issueData[$k]['uid'] = $v;
                $issueData[$k]['issue_coupon_id'] = $coupon['id'];
                $issueData[$k]['add_time'] = time();
            }
        }
        if (!empty($data)) {
            if (!$storeCouponUser->saveAll($data)) {
                throw new AdminException('发劵失败');
            }
            if (!$storeCouponIssueUser->saveAll($issueData)) {
                throw new AdminException('发劵失败');
            }
            return true;
        } else {
            throw new AdminException('选择用户已拥有该优惠券，请勿重复发放');
        }
    }

    /**
     * 获取下单可使用的优惠券列表
     * @param int $uid
     * @param $cartId
     * @param string $price
     * @param bool $new
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function beUsableCouponList(int $uid, $cartId, bool $new)
    {
        /** @var StoreCartServices $services */
        $services = app()->make(StoreCartServices::class);
        $cartGroup = $services->getUserProductCartListV1($uid, $cartId, $new);
        /** @var StoreCouponUserServices $coupServices */
        $coupServices = app()->make(StoreCouponUserServices::class);
        return $coupServices->getUsableCouponList($uid, $cartGroup);
    }

    /**获取单个优惠券类型
     * @param array $where
     * @return mixed
     */
    public function getOne(array $where)
    {
        if (!$where) throw new AdminException('参数缺失!');
        return $this->dao->getOne($where);

    }

    /**
     * 俩时间相差月份
     * @param $date1
     * @param $date2
     * @return float|int
     */
    public function getMonthNum($date1, $date2)
    {
        $date1_stamp = strtotime($date1);
        $date2_stamp = strtotime($date2);
        list($date_1['y'], $date_1['m']) = explode("-", date('Y-m', $date1_stamp));
        list($date_2['y'], $date_2['m']) = explode("-", date('Y-m', $date2_stamp));
        return abs($date_1['y'] - $date_2['y']) * 12 + $date_2['m'] - $date_1['m'];
    }

    /**
     * 给会员发放优惠券
     * @param $uid
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function sendMemberCoupon($uid, $couponId = 0)
    {
        if (!$uid) return false;
        /** @var MemberCardServices $memberCardService */
        $memberCardService = app()->make(MemberCardServices::class);
        //看付费会员是否开启
        $isOpenMember = $memberCardService->isOpenMemberCard();
        if (!$isOpenMember) return false;
        /** @var UserServices $userService */
        $userService = app()->make(UserServices::class);
        $userInfo = $userService->getUserInfo((int)$uid);
        //看是否会员过期
        $checkMember = $userService->offMemberLevel($uid, $userInfo);
        if (!$checkMember) return false;
        /** @var MemberRightServices $memberRightService */
        $memberRightService = app()->make(MemberRightServices::class);
        //看是否开启会员送券
        $isSendCoupon = $memberRightService->getMemberRightStatus("coupon");
        if (!$isSendCoupon) return false;
        if ($userInfo && (($userInfo['is_money_level'] > 0) || $userInfo['is_ever_level'] == 1)) {
            if ($couponId) {//手动点击领取
                $couponWhere['id'] = $couponId;
            } else {//主动批量发放
                $couponWhere['status'] = 1;
                $couponWhere['receive_type'] = 4;
                $couponWhere['is_del'] = 0;
            }
            $couponInfo = $this->getMemberCouponIssueList($couponWhere);
            if ($couponInfo) {
                /** @var StoreCouponUserServices $couponUserService */
                $couponUserService = app()->make(StoreCouponUserServices::class);
                $couponIds = array_column($couponInfo, 'id');
                $couponUserMonth = $couponUserService->memberCouponUserGroupBymonth(['uid' => $uid, 'couponIds' => $couponIds]);
                $getTime = array();
                if ($couponUserMonth) {
                    $getTime = array_column($couponUserMonth, 'num', 'time');
                }
                // 判断这个月是否领取过,而且领全了
                //if (in_array(date('Y-m', time()), $getTime)) return false;
                $timeKey = date('Y-m', time());
                if (array_key_exists($timeKey, $getTime) && $getTime[$timeKey] == count($couponIds)) return false;
                $monthNum = $this->getMonthNum(date('Y-m-d H:i:s', time()), date('Y-m-d H:i:s', $userInfo['overdue_time']));
                //判断是否领完所有月份
                if (count($getTime) >= $monthNum && (array_key_exists($timeKey, $getTime) && $getTime[$timeKey] == count($couponIds)) && $userInfo['is_ever_level'] != 1 && $monthNum > 0) return false;
                //看之前是否手动领取过某一张，领取过就不再领取。
                $couponUser = $couponUserService->getUserCounponByMonth(['uid' => $uid, 'cid' => $couponIds], 'id,cid');
                if ($couponUser) $couponUser = array_combine(array_column($couponUser, 'cid'), $couponUser);
                foreach ($couponInfo as $cv) {
                    if (!isset($couponUser[$cv['id']])) {
                        $this->memberIssueUserCoupon($cv['id'], $uid);
                    }
                }

            }
        }
        return true;
    }

    /**
     * 获取今日新增优惠券
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getTodayCoupon($uid)
    {
        $list = $this->dao->getTodayCoupon($uid);
        foreach ($list as $key => &$item) {
            $item['start_time'] = $item['start_time'] ? date('Y/m/d', $item['start_time']) : 0;
            $item['end_time'] = $item['end_time'] ? date('Y/m/d', $item['end_time']) : 0;
            $item['coupon_price'] = floatval($item['coupon_price']);
            $item['use_min_price'] = floatval($item['use_min_price']);
            if (isset($item['used']) && $item['used']) {
                unset($list[$key]);
            }
        }
        return array_merge($list);
    }

    /**
     * 获取新人券
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getNewCoupon()
    {
        $list = $this->dao->getNewCoupon();
        foreach ($list as &$item) {
            $item['start_time'] = $item['start_time'] ? date('Y/m/d', $item['start_time']) : 0;
            $item['end_time'] = $item['end_time'] ? date('Y/m/d', $item['end_time']) : 0;
            $item['coupon_price'] = floatval($item['coupon_price']);
            $item['use_min_price'] = floatval($item['use_min_price']);
        }
        return $list;
    }
}
