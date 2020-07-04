<?php

namespace app\api\controller\user;

use app\http\validates\user\AddressValidate;
use app\models\system\SystemCity;
use think\exception\ValidateException;
use app\Request;
use app\models\user\UserLevel;
use app\models\user\UserSign;
use app\models\store\StoreBargain;
use app\models\store\StoreCombination;
use app\models\store\StoreCouponUser;
use app\models\store\StoreOrder;
use app\models\store\StoreProductRelation;
use app\models\store\StoreSeckill;
use app\models\user\User;
use app\models\user\UserAddress;
use app\models\user\UserBill;
use app\models\user\UserExtract;
use app\models\user\UserNotice;
use crmeb\services\GroupDataService;
use crmeb\services\UtilService;

/**
 * 用户类
 * Class UserController
 * @package app\api\controller\store
 */
class UserController
{

    /**
     * 获取用户信息
     * @param Request $request
     * @return mixed
     */
    public function userInfo(Request $request)
    {
        $info = $request->user()->toArray();
        $broken_time = intval(sys_config('extract_time'));
        $search_time = time() - 86400 * $broken_time;
        //返佣 +
        $brokerage_commission = UserBill::where(['uid' => $info['uid'], 'category' => 'now_money', 'type' => 'brokerage'])
            ->where('add_time', '>', $search_time)
            ->where('pm', 1)
            ->sum('number');
        //退款退的佣金 -
        $refund_commission = UserBill::where(['uid' => $info['uid'], 'category' => 'now_money', 'type' => 'brokerage'])
            ->where('add_time', '>', $search_time)
            ->where('pm', 0)
            ->sum('number');
        $info['broken_commission'] = bcsub($brokerage_commission, $refund_commission, 2);
        if ($info['broken_commission'] < 0)
            $info['broken_commission'] = 0;
        $info['commissionCount'] = bcsub($info['brokerage_price'], $info['broken_commission'], 2);
        if ($info['commissionCount'] < 0)
            $info['commissionCount'] = 0;
        return app('json')->success($info);
    }

    /**
     * 用户资金统计
     * @param Request $request
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function balance(Request $request)
    {
        $uid = $request->uid();
        $user['now_money'] = User::getUserInfo($uid, 'now_money')['now_money'];//当前总资金
        $user['recharge'] = UserBill::getRecharge($uid);//累计充值
        $user['orderStatusSum'] = StoreOrder::getOrderStatusSum($uid);//累计消费
        return app('json')->successful($user);
    }

    /**
     * 个人中心
     * @param Request $request
     * @return mixed
     */
    public function user(Request $request)
    {
        $user = $request->user();
        $user = $user->toArray();
        $user['couponCount'] = StoreCouponUser::getUserValidCouponCount($user['uid']);
        $user['like'] = StoreProductRelation::getUserIdCollect($user['uid']);
        $user['orderStatusNum'] = StoreOrder::getOrderData($user['uid']);
        $user['notice'] = UserNotice::getNotice($user['uid']);
//        $user['brokerage'] = UserBill::getBrokerage($user['uid']);//获取总佣金
        $user['recharge'] = UserBill::getRecharge($user['uid']);//累计充值
        $user['orderStatusSum'] = StoreOrder::getOrderStatusSum($user['uid']);//累计消费
        $user['extractTotalPrice'] = UserExtract::userExtractTotalPrice($user['uid']);//累计提现
        $user['extractPrice'] = $user['brokerage_price'];//可提现
        $user['statu'] = (int)sys_config('store_brokerage_statu');
        $broken_time = intval(sys_config('extract_time'));
        $search_time = time() - 86400 * $broken_time;
        if (!$user['is_promoter'] && $user['statu'] == 2) {
            $price = StoreOrder::where(['paid' => 1, 'refund_status' => 0, 'uid' => $user['uid']])->sum('pay_price');
            $status = is_brokerage_statu($price);
            if ($status) {
                User::where('uid', $user['uid'])->update(['is_promoter' => 1]);
                $user['is_promoter'] = 1;
            } else {
                $storeBrokeragePrice = sys_config('store_brokerage_price', 0);
                $user['promoter_price'] = bcsub($storeBrokeragePrice, $price, 2);
            }
        }
        //可提现佣金
        //返佣 +
        $brokerage_commission = UserBill::where(['uid' => $user['uid'], 'category' => 'now_money', 'type' => 'brokerage'])
            ->where('add_time', '>', $search_time)
            ->where('pm', 1)
            ->sum('number');
        //退款退的佣金 -
        $refund_commission = UserBill::where(['uid' => $user['uid'], 'category' => 'now_money', 'type' => 'brokerage'])
            ->where('add_time', '>', $search_time)
            ->where('pm', 0)
            ->sum('number');
        $user['broken_commission'] = bcsub($brokerage_commission, $refund_commission, 2);
        if ($user['broken_commission'] < 0)
            $user['broken_commission'] = 0;
        $user['commissionCount'] = bcsub($user['brokerage_price'], $user['broken_commission'], 2);
        if ($user['commissionCount'] < 0)
            $user['commissionCount'] = 0;
        if (!sys_config('vip_open'))
            $user['vip'] = false;
        else {
            $vipId = UserLevel::getUserLevel($user['uid']);
            $user['vip'] = $vipId !== false ? true : false;
            if ($user['vip']) {
                $user['vip_id'] = $vipId;
                $user['vip_icon'] = UserLevel::getUserLevelInfo($vipId, 'icon');
                $user['vip_name'] = UserLevel::getUserLevelInfo($vipId, 'name');
            }
        }
        $user['yesterDay'] = UserBill::yesterdayCommissionSum($user['uid']);
        $user['recharge_switch'] = (int)sys_config('recharge_switch');//充值开关
        $user['adminid'] = (boolean)\app\models\store\StoreService::orderServiceStatus($user['uid']);
        if ($user['phone'] && $user['user_type'] != 'h5') {
            $user['switchUserInfo'][] = $request->user();
            if ($h5UserInfo = User::where('account', $user['phone'])->where('user_type', 'h5')->find()) {
                $user['switchUserInfo'][] = $h5UserInfo;
            }
        } else if ($user['phone'] && $user['user_type'] == 'h5') {
            if ($wechatUserInfo = User::where('phone', $user['phone'])->where('user_type', '<>', 'h5')->find()) {
                $user['switchUserInfo'][] = $wechatUserInfo;
            }
            $user['switchUserInfo'][] = $request->user();
        } else if (!$user['phone']) {
            $user['switchUserInfo'][] = $request->user();
        }

        return app('json')->successful($user);
    }

    /**
     * 地址 获取单个
     * @param Request $request
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function address(Request $request, $id)
    {
        $addressInfo = [];
        if ($id && is_numeric($id) && UserAddress::be(['is_del' => 0, 'id' => $id, 'uid' => $request->uid()])) {
            $addressInfo = UserAddress::find($id)->toArray();
        }
        return app('json')->successful($addressInfo);
    }

    /**
     * 地址列表
     * @param Request $request
     * @param $page
     * @param $limit
     * @return mixed
     */
    public function address_list(Request $request)
    {
        list($page, $limit) = UtilService::getMore([['page', 0], ['limit', 20]], $request, true);
        $list = UserAddress::getUserValidAddressList($request->uid(), $page, $limit, 'id,real_name,phone,province,city,district,detail,is_default');
        return app('json')->successful($list);
    }

    /**
     * 设置默认地址
     *
     * @param Request $request
     * @return mixed
     */
    public function address_default_set(Request $request)
    {
        list($id) = UtilService::getMore([['id', 0]], $request, true);
        if (!$id || !is_numeric($id)) return app('json')->fail('参数错误!');
        if (!UserAddress::be(['is_del' => 0, 'id' => $id, 'uid' => $request->uid()]))
            return app('json')->fail('地址不存在!');
        $res = UserAddress::setDefaultAddress($id, $request->uid());
        if (!$res)
            return app('json')->fail('地址不存在!');
        else
            return app('json')->successful();
    }

    /**
     * 获取默认地址
     * @param Request $request
     * @return mixed
     */
    public function address_default(Request $request)
    {
        $defaultAddress = UserAddress::getUserDefaultAddress($request->uid(), 'id,real_name,phone,province,city,district,detail,is_default');
        if ($defaultAddress) {
            $defaultAddress = $defaultAddress->toArray();
            return app('json')->successful('ok', $defaultAddress);
        }
        return app('json')->successful('empty', []);
    }

    /**
     * 修改 添加地址
     * @param Request $request
     * @return mixed
     */
    public function address_edit(Request $request)
    {
        $addressInfo = UtilService::postMore([
            ['address', []],
            ['is_default', false],
            ['real_name', ''],
            ['post_code', ''],
            ['phone', ''],
            ['detail', ''],
            ['id', 0],
            ['type', 0]
        ], $request);
        if (!isset($addressInfo['address']['province'])) return app('json')->fail('收货地址格式错误!');
        if (!isset($addressInfo['address']['city'])) return app('json')->fail('收货地址格式错误!');
        if (!isset($addressInfo['address']['district'])) return app('json')->fail('收货地址格式错误!');
        if (!isset($addressInfo['address']['city_id']) && $addressInfo['type'] == 0) {
            return app('json')->fail('收货地址格式错误!请重新选择!');
        } else if ($addressInfo['type'] == 1 && !$addressInfo['id']) {
            $city = $addressInfo['address']['city'];
            $cityId = SystemCity::where('name', $city)->where('parent_id', '<>', 0)->value('city_id');
            if ($cityId) {
                $addressInfo['address']['city_id'] = $cityId;
            } else {
                if (!($cityId = SystemCity::where('parent_id', '<>', 0)->where('name', 'like', "%$city%")->value('city_id'))) {
                    return app('json')->fail('收货地址格式错误!修改后请重新导入!');
                }
            }
        }

        $addressInfo['province'] = $addressInfo['address']['province'];
        $addressInfo['city'] = $addressInfo['address']['city'];
        $addressInfo['city_id'] = $addressInfo['address']['city_id'] ?? 0;
        $addressInfo['district'] = $addressInfo['address']['district'];
        $addressInfo['is_default'] = (int)$addressInfo['is_default'] == true ? 1 : 0;
        $addressInfo['uid'] = $request->uid();
        unset($addressInfo['address'], $addressInfo['type']);
        try {
            validate(AddressValidate::class)->check($addressInfo);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }
        //编辑
        if ($addressInfo['id'] && UserAddress::be(['id' => $addressInfo['id'], 'uid' => $request->uid(), 'is_del' => 0])) {
            $id = $addressInfo['id'];
            unset($addressInfo['id']);
            if ($addressInfo['city_id'] == 0)
                unset($addressInfo['city_id']);
            if (UserAddress::edit($addressInfo, $id, 'id')) {
                if ($addressInfo['is_default'])
                    UserAddress::setDefaultAddress($id, $request->uid());
                return app('json')->successful();
            } else
                return app('json')->fail('编辑收货地址失败!');
        } else {
            $addressInfo['add_time'] = time();
            if ($address = UserAddress::create($addressInfo)) {
                if ($addressInfo['is_default']) {
                    UserAddress::setDefaultAddress($address->id, $request->uid());
                }
                return app('json')->successful(['id' => $address->id]);
            } else {
                return app('json')->fail('添加收货地址失败!');
            }
        }
    }

    /**
     * 删除地址
     *
     * @param Request $request
     * @return mixed
     */
    public function address_del(Request $request)
    {
        list($id) = UtilService::postMore([['id', 0]], $request, true);
        if (!$id || !is_numeric($id)) return app('json')->fail('参数错误!');
        if (!UserAddress::be(['is_del' => 0, 'id' => $id, 'uid' => $request->uid()]))
            return app('json')->fail('地址不存在!');
        if (UserAddress::edit(['is_del' => '1'], $id, 'id'))
            return app('json')->successful();
        else
            return app('json')->fail('删除地址失败!');
    }


    /**
     * 获取收藏产品
     *
     * @param Request $request
     * @return mixed
     */
    public function collect_user(Request $request)
    {
        list($page, $limit) = UtilService::getMore([
            ['page', 0],
            ['limit', 0]
        ], $request, true);
        if (!(int)$limit) return app('json')->successful([]);
        $productRelationList = StoreProductRelation::getUserCollectProduct($request->uid(), (int)$page, (int)$limit);
        return app('json')->successful($productRelationList);
    }

    /**
     * 添加收藏
     * @param Request $request
     * @param $id
     * @param $category
     * @return mixed
     */
    public function collect_add(Request $request)
    {
        list($id, $category) = UtilService::postMore([['id', 0], ['category', 'product']], $request, true);
        if (!$id || !is_numeric($id)) return app('json')->fail('参数错误');
        $res = StoreProductRelation::productRelation($id, $request->uid(), 'collect', $category);
        if (!$res) return app('json')->fail(StoreProductRelation::getErrorInfo());
        else return app('json')->successful();
    }

    /**
     * 取消收藏
     *
     * @param Request $request
     * @return mixed
     */
    public function collect_del(Request $request)
    {
        list($id, $category) = UtilService::postMore([['id', 0], ['category', 'product']], $request, true);
        if (!$id || !is_numeric($id)) return app('json')->fail('参数错误');
        $res = StoreProductRelation::unProductRelation($id, $request->uid(), 'collect', $category);
        if (!$res) return app('json')->fail(StoreProductRelation::getErrorInfo());
        else return app('json')->successful();
    }

    /**
     * 批量收藏
     * @param Request $request
     * @return mixed
     */
    public function collect_all(Request $request)
    {
        $collectInfo = UtilService::postMore([
            ['id', []],
            ['category', 'product'],
        ], $request);
        if (!count($collectInfo['id'])) return app('json')->fail('参数错误');
        $productIdS = $collectInfo['id'];
        $res = StoreProductRelation::productRelationAll($productIdS, $request->uid(), 'collect', $collectInfo['category']);
        if (!$res) return app('json')->fail(StoreProductRelation::getErrorInfo());
        else return app('json')->successful('收藏成功');
    }

    /**
     * 添加点赞
     *
     * @param Request $request
     * @return mixed
     */
//    public function like_add(Request $request)
//    {
//        list($id, $category) = UtilService::postMore([['id',0], ['category','product']], $request, true);
//        if(!$id || !is_numeric($id))  return app('json')->fail('参数错误');
//        $res = StoreProductRelation::productRelation($id,$request->uid(),'like',$category);
//        if(!$res) return  app('json')->fail(StoreProductRelation::getErrorInfo());
//        else return app('json')->successful();
//    }

    /**
     * 取消点赞
     *
     * @param Request $request
     * @return mixed
     */
//    public function like_del(Request $request)
//    {
//        list($id, $category) = UtilService::postMore([['id',0], ['category','product']], $request, true);
//        if(!$id || !is_numeric($id)) return app('json')->fail('参数错误');
//        $res = StoreProductRelation::unProductRelation($id, $request->uid(),'like',$category);
//        if(!$res) return app('json')->fail(StoreProductRelation::getErrorInfo());
//        else return app('json')->successful();
//    }

    /**
     * 签到 配置
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function sign_config()
    {
        $signConfig = sys_data('sign_day_num') ?? [];
        return app('json')->successful($signConfig);
    }

    /**
     * 签到 列表
     * @param Request $request
     * @param $page
     * @param $limit
     * @return mixed
     */
    public function sign_list(Request $request)
    {
        list($page, $limit) = UtilService::getMore([
            ['page', 0],
            ['limit', 0]
        ], $request, true);
        if (!$limit) return app('json')->successful([]);
        $signList = UserSign::getSignList($request->uid(), (int)$page, (int)$limit);
        if ($signList) $signList = $signList->toArray();
        return app('json')->successful($signList);
    }

    /**
     * 签到
     * @param Request $request
     * @return mixed
     */
    public function sign_integral(Request $request)
    {
        $signed = UserSign::getIsSign($request->uid());
        if ($signed) return app('json')->fail('已签到');
        if (false !== ($integral = UserSign::sign($request->uid())))
            return app('json')->successful('签到获得' . floatval($integral) . '积分', ['integral' => $integral]);
        return app('json')->fail(UserSign::getErrorInfo('签到失败'));
    }

    /**
     * 签到用户信息
     * @param Request $request
     * @return mixed
     */
    public function sign_user(Request $request)
    {
        list($sign, $integral, $all) = UtilService::postMore([
            ['sign', 0],
            ['integral', 0],
            ['all', 0],
        ], $request, true);
        $user = $request->user();
        //是否统计签到
        if ($sign || $all) {
            $user['sum_sgin_day'] = UserSign::getSignSumDay($user['uid']);
            $user['is_day_sgin'] = UserSign::getIsSign($user['uid']);
            $user['is_YesterDay_sgin'] = UserSign::getIsSign($user['uid'], 'yesterday');
            if (!$user['is_day_sgin'] && !$user['is_YesterDay_sgin']) {
                $user['sign_num'] = 0;
            }
        }
        //是否统计积分使用情况
        if ($integral || $all) {
            $user['sum_integral'] = (int)UserBill::getRecordCount($user['uid'], 'integral', 'sign,system_add,gain');
            $refund_integral = (int)UserBill::where(['uid' => $user['uid'], 'category' => 'integral', 'status' => 1, 'pm' => 0])->where('type', 'in', 'sign,system_add,gain')->sum('number');
            if ($user['sum_integral'] > $refund_integral)
                $user['sum_integral'] = $user['sum_integral'] - $refund_integral;
            else
                $user['sum_integral'] = 0;
            $user['deduction_integral'] = (int)UserBill::getRecordCount($user['uid'], 'integral', 'deduction', '', true) ?? 0;
            $user['today_integral'] = (int)UserBill::getRecordCount($user['uid'], 'integral', 'sign,system_add,gain', 'today');
        }
        unset($user['pwd']);
        if (!$user['is_promoter']) {
            $user['is_promoter'] = (int)sys_config('store_brokerage_statu') == 2 ? true : false;
        }
        return app('json')->successful($user->hidden(['account', 'real_name', 'birthday', 'card_id', 'mark', 'partner_id', 'group_id', 'add_time', 'add_ip', 'phone', 'last_time', 'last_ip', 'spread_uid', 'spread_time', 'user_type', 'status', 'level', 'clean_time', 'addres'])->toArray());
    }

    /**
     * 签到列表（年月）
     *
     * @param Request $request
     * @return mixed
     */
    public function sign_month(Request $request)
    {
        list($page, $limit) = UtilService::getMore([
            ['page', 0],
            ['limit', 0]
        ], $request, true);
        if (!$limit) return app('json')->successful([]);
        $userSignList = UserSign::getSignMonthList($request->uid(), (int)$page, (int)$limit);
        return app('json')->successful($userSignList);
    }

    /**
     * 获取活动状态
     * @return mixed
     */
    public function activity()
    {
        $data['is_bargin'] = StoreBargain::validBargain() ? true : false;
        $data['is_pink'] = StoreCombination::getPinkIsOpen() ? true : false;
        $data['is_seckill'] = StoreSeckill::getSeckillCount() ? true : false;
        return app('json')->successful($data);
    }

    /**
     * 用户修改信息
     * @param Request $request
     * @return mixed
     */
    public function edit(Request $request)
    {
        list($avatar, $nickname) = UtilService::postMore([
            ['avatar', ''],
            ['nickname', ''],
        ], $request, true);
        if (User::editUser($avatar, $nickname, $request->uid())) return app('json')->successful('修改成功');
        return app('json')->fail('修改失败');
    }

    /**
     * 推广人排行
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function rank(Request $request)
    {
        $data = UtilService::getMore([
            ['page', ''],
            ['limit', ''],
            ['type', '']
        ], $request);
        $users = User::getRankList($data);
        return app('json')->success($users);
    }

    /**
     * 佣金排行
     * @param Request $request
     * @return mixed
     */
    public function brokerage_rank(Request $request)
    {
        $data = UtilService::getMore([
            ['page', ''],
            ['limit'],
            ['type']
        ], $request);
        $users = User::brokerageRank($data);
        foreach ($users as $key => $item) {
            if ($item['brokerage_price'] == '0.00' || $item['brokerage_price'] == 0 || !$item['brokerage_price']) {
                unset($users[$key]);
            }
        }
        $position_tmp = User::brokerageRank(['type' => $data['type'], 'page' => 0, 'limit' => 99999]);
        $position_tmp_one = array_column($position_tmp, 'uid');
        $position_tmp_two = array_column($position_tmp, 'brokerage_price', 'uid');
        if (!in_array($request->uid(), $position_tmp_one)) {
            $position = 0;
        } else {
            if ($position_tmp_two[$request->uid()] == 0.00) {
                $position = 0;
            } else {
                $position = array_search($request->uid(), $position_tmp_one) + 1;
            }
        }
        return app('json')->success([
            'rank' => $users,
            'position' => $position
        ]);

    }

}