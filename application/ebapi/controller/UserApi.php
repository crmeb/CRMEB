<?php
namespace app\ebapi\controller;

use Api\Express;
use app\admin\model\system\SystemAttachment;
use app\core\model\routine\RoutineQrcode;
use app\core\model\user\UserLevel;
use app\core\model\user\UserSign;
use app\core\model\routine\RoutineCode;//待完善
use app\core\model\routine\RoutineFormId;//待完善
use app\ebapi\model\store\StoreBargain;
use app\ebapi\model\store\StoreCombination;
use app\ebapi\model\store\StoreCouponUser;
use app\ebapi\model\store\StoreOrder;
use app\ebapi\model\store\StoreOrderCartInfo;
use app\ebapi\model\store\StoreProductRelation;
use app\ebapi\model\store\StoreProductReply;
use app\ebapi\model\store\StoreSeckill;
use app\ebapi\model\user\User;
use app\ebapi\model\user\UserAddress;
use app\core\model\user\UserBill;
use app\ebapi\model\user\UserExtract;
use app\ebapi\model\user\UserNotice;
use app\ebapi\model\user\UserRecharge;
use service\CacheService;
use app\core\util\GroupDataService;
use service\JsonService;
use app\core\util\SystemConfigService;
use service\UploadService;
use service\UtilService;
use think\Request;
use think\Cache;


/**
 * 小程序个人中心api接口
 * Class UserApi
 * @package app\ebapi\controller
 *
 */
class UserApi extends AuthController
{

    /*
     * 获取签到按月份查找
     * @param int $page 页码
     * @param int $limit 显示条数
     * @return json
     * */
    public function get_sign_month_list($page=1,$limit=10)
    {
        return JsonService::successful(UserSign::getSignMonthList($this->uid,$page,$limit));
    }
    /*
     * 获取用户签到记录列表
     *
     * */
    public function get_sign_list($page=1,$limit=10)
    {
        return JsonService::successful(UserSign::getSignList($this->uid,$page,$limit));
    }
    /*
   * 获取图片储存位置
   *
   * */
    public function picture_storage_location()
    {
        return JsonService::successful((int)SystemConfigService::get('upload_type'));
    }
    /*
     * 获取当前登录的用户信息
     * */
    public function get_my_user_info()
    {
        list($isSgin,$isIntegral,$isall)=UtilService::getMore([
            ['isSgin',0],
            ['isIntegral',0],
            ['isall',0],
        ],$this->request,true);
        //是否统计签到
        if($isSgin || $isall){
            $this->userInfo['sum_sgin_day']=UserSign::getSignSumDay($this->uid);
            $this->userInfo['is_day_sgin']=UserSign::getToDayIsSign($this->uid);
            $this->userInfo['is_YesterDay_sgin']=UserSign::getYesterDayIsSign($this->uid);
            if(!$this->userInfo['is_day_sgin'] && !$this->userInfo['is_YesterDay_sgin']){
                $this->userInfo['sign_num']=0;
            }
        }
        //是否统计积分使用情况
        if($isIntegral || $isall){
            $this->userInfo['sum_integral']=(int)UserBill::getRecordCount($this->uid,'integral','sign,system_add,gain');
            $this->userInfo['deduction_integral']=(int)UserBill::getRecordCount($this->uid,'integral','deduction') ? : 0;
            $this->userInfo['today_integral']=(int)UserBill::getRecordCount($this->uid,'integral','sign,system_add,gain','today');
        }
        unset($this->userInfo['pwd']);
        $this->userInfo['integral']=(int)$this->userInfo['integral'];
        if(!$this->userInfo['is_promoter']){
            $this->userInfo['is_promoter']=(int)SystemConfigService::get('store_brokerage_statu') == 2 ? true : false;
        }
        return JsonService::successful($this->userInfo);
    }
    /**
     * 获取用户信息
     * @param int $userId 用户uid
     * @return \think\response\Json
     */
    public function get_user_info_uid($userId = 0){
        if(!$userId) return JsonService::fail('参数错误');
        $res = User::getUserInfo($userId);
        if($res) return JsonService::successful($res);
        else return JsonService::fail(User::getErrorInfo());
    }
    /**
     * 个人中心
     * @return \think\response\Json
     */
    public function my(){
        $this->userInfo['couponCount'] = StoreCouponUser::getUserValidCouponCount($this->userInfo['uid']);
        $this->userInfo['like'] = StoreProductRelation::getUserIdCollect($this->userInfo['uid']);;
        $this->userInfo['orderStatusNum'] = StoreOrder::getOrderStatusNum($this->userInfo['uid']);
        $this->userInfo['notice'] = UserNotice::getNotice($this->userInfo['uid']);
        $this->userInfo['brokerage'] = UserBill::getBrokerage($this->uid);//获取总佣金
        $this->userInfo['recharge'] = UserBill::getRecharge($this->uid);//累计充值
        $this->userInfo['orderStatusSum'] = StoreOrder::getOrderStatusSum($this->uid);//累计消费
        $this->userInfo['extractTotalPrice'] = UserExtract::userExtractTotalPrice($this->uid);//累计提现
        $this->userInfo['extractPrice'] = $this->userInfo['brokerage_price'];//可提现
        $this->userInfo['statu'] = (int)SystemConfigService::get('store_brokerage_statu');
        $vipId = UserLevel::getUserLevel($this->uid);
        $this->userInfo['vip']=$vipId !==false ? true : false;
        if($this->userInfo['vip']){
            $this->userInfo['vip_id'] = $vipId;
            $this->userInfo['vip_icon'] = UserLevel::getUserLevelInfo($vipId,'icon');
            $this->userInfo['vip_name'] = UserLevel::getUserLevelInfo($vipId,'name');
        }
        if(!SystemConfigService::get('vip_open')) $this->userInfo['vip']=false;
        unset($this->userInfo['pwd']);
        return JsonService::successful($this->userInfo);
    }

    /**
     * 用户签到
     * @return \think\response\Json
     */
    public function user_sign()
    {
        $signed = UserSign::getToDayIsSign($this->userInfo['uid']);
        if($signed) return JsonService::fail('已签到');
        if(false !== $integral = UserSign::sign($this->uid))
            return JsonService::successful('签到获得'.floatval($integral).'积分',['integral'=>$integral]);
        else
            return JsonService::fail(UserSign::getErrorInfo('签到失败'));
    }

    /**
     * 获取一条用户地址
     * @param string $addressId 地址id
     * @return \think\response\Json
     */
    public function get_user_address($addressId = ''){
        $addressInfo = [];
        if($addressId && is_numeric($addressId) && UserAddress::be(['is_del'=>0,'id'=>$addressId,'uid'=>$this->userInfo['uid']])){
            $addressInfo = UserAddress::find($addressId);
        }
        return JsonService::successful($addressInfo);
    }

    /**
     * 获取默认地址
     * @return \think\response\Json
     */
    public function user_default_address()
    {
        $defaultAddress = UserAddress::getUserDefaultAddress($this->userInfo['uid'],'id,real_name,phone,province,city,district,detail,is_default');
        if($defaultAddress) return JsonService::successful('ok',$defaultAddress);
        else return JsonService::successful('empty',[]);
    }

    /**
     * 删除地址
     * @param string $addressId 地址id
     * @return \think\response\Json
     */
    public function remove_user_address($addressId = '')
    {
        if(!$addressId || !is_numeric($addressId)) return JsonService::fail('参数错误!');
        if(!UserAddress::be(['is_del'=>0,'id'=>$addressId,'uid'=>$this->userInfo['uid']]))
            return JsonService::fail('地址不存在!');
        if(UserAddress::edit(['is_del'=>'1'],$addressId,'id'))
            return JsonService::successful();
        else
            return JsonService::fail('删除地址失败!');
    }

    /**
     * 个人中心 获取订单列表
     * @param string $type
     * @param int $first
     * @param int $limit
     * @param string $search
     * @return \think\response\Json
     */
    public function get_user_order_list()
    {
        list($type,$page,$limit,$search)=UtilService::getMore([
            ['type',''],
            ['page',''],
            ['limit',''],
            ['search',''],
        ],$this->request,true);
        return JsonService::successful(StoreOrder::getUserOrderSearchList($this->uid,$type,$page,$limit,$search));
    }

    /**
     * 个人中心 订单详情页
     * @param string $order_id
     * @return \think\response\Json
     */
    public function get_order($uni = ''){
        if($uni == '') return JsonService::fail('参数错误');
        $order = StoreOrder::getUserOrderDetail($this->userInfo['uid'],$uni);
        $order = $order->toArray();
        $order['add_time_y'] = date('Y-m-d',$order['add_time']);
        $order['add_time_h'] = date('H:i:s',$order['add_time']);
        if(!$order) return JsonService::fail('订单不存在');
        return JsonService::successful(StoreOrder::tidyOrder($order,true,true));
    }

    /**
     * 个人中心 删除订单
     * @param string $uni
     * @return \think\response\Json
     */
    public function user_remove_order($uni = '')
    {
        if(!$uni) return JsonService::fail('参数错误!');
        $res = StoreOrder::removeOrder($uni,$this->userInfo['uid']);
        if($res)
            return JsonService::successful();
        else
            return JsonService::fail(StoreOrder::getErrorInfo());
    }

    /**
     * 获取用户手机号码
     * @param Request $request
     * @return \think\response\Json
     */
    public function bind_mobile(Request $request){
        list($iv,$cache_key,$encryptedData) = UtilService::postMore([
            ['iv',''],
            ['cache_key',''],
            ['encryptedData',''],
        ],$request,true);
        $iv  = urldecode(urlencode($iv));
        try{
            if(!Cache::has('eb_api_code_'.$cache_key)) return JsonService::fail('获取手机号失败');
            $session_key=Cache::get('eb_api_code_'.$cache_key);
            $userInfo = \app\core\util\MiniProgramService::encryptor($session_key,$iv,$encryptedData);
            if(!empty($userInfo['purePhoneNumber'])){
                if(User::edit(['phone'=>$userInfo['purePhoneNumber']],$this->userInfo['uid']))
                    return JsonService::successful('绑定成功',['phone'=>$userInfo['purePhoneNumber']]);
                else
                    return JsonService::fail('绑定失败');
            }else
                return JsonService::fail('获取手机号失败');
        }catch (\Exception $e){
            return JsonService::fail('error',$e->getMessage());
        }
    }
    /**
     * 个人中心 用户确认收货
     * @param string $uni
     * @return \think\response\Json
     */
    public function user_take_order($uni = '')
    {
        if(!$uni) return JsonService::fail('参数错误!');

        $res = StoreOrder::takeOrder($uni,$this->userInfo['uid']);
        if($res)
            return JsonService::successful();
        else
            return JsonService::fail(StoreOrder::getErrorInfo());
    }

    /**
     *  个人中心 充值
     * @param int $price
     * @return \think\response\Json
     */
    public function user_wechat_recharge($price = 0,$type = 0)
    {
        if(!$price || $price <=0) return JsonService::fail('参数错误');
        $storeMinRecharge = SystemConfigService::get('store_user_min_recharge');
        if($price < $storeMinRecharge) return JsonService::fail('充值金额不能低于'.$storeMinRecharge);
        switch ((int)$type){
            case 0:
                $rechargeOrder = UserRecharge::addRecharge($this->userInfo['uid'],$price,'routine');
                if(!$rechargeOrder) return JsonService::fail('充值订单生成失败!');
                try{
                    return JsonService::successful(UserRecharge::jsPay($rechargeOrder));
                }catch (\Exception $e){
                    return JsonService::fail($e->getMessage());
                }
                break;
            case 1:
                if(UserRecharge::importNowMoney($this->uid,$price)){
                    return JsonService::successful('转入成功');
                }else{
                    return JsonService::fail(UserRecharge::getErrorInfo('转入失败'));
                }
                break;
            default:
                return JsonService::fail('缺少转入类型');
                break;

        }
    }

    /**
     * 个人中心 余额使用记录
     * @param int $first
     * @param int $limit
     * @return \think\response\Json
     */
    public function user_balance_list($first = 0,$limit = 8)
    {
        return JsonService::successful(UserBill::userBillList($this->uid,$first,$limit,'now_money'));
    }

    /**
     * 个人中心 积分使用记录
     * @param int $first
     * @param int $limit
     * @return \think\response\Json
     */
    public function user_integral_list($page = 0,$limit = 8)
    {
        return JsonService::successful(UserBill::userBillList($this->uid,$page,$limit));

    }

    /**
     * 个人中心 获取一级推荐人
     * @param int $first
     * @param int $limit
     * @return \think\response\Json
     */
    public function get_spread_list($first = 0,$limit = 20)
    {
        return JsonService::successful(User::getSpreadList($this->uid,$first,$limit));
    }

    /**
     * 个人中心 获取二级推荐人
     * @param int $first
     * @param int $limit
     * @return \think\response\Json
     */
    public function get_spread_list_two($two_uid=0,$first = 0,$limit = 20)
    {
        return JsonService::successful(User::getSpreadList($two_uid,$first,$limit));
    }

    /**
     * 获取用户所有地址
     * @return \think\response\Json
     */
    public function user_address_list($page=1,$limit=8)
    {
        $list = UserAddress::getUserValidAddressList($this->userInfo['uid'],$page,$limit,'id,real_name,phone,province,city,district,detail,is_default');
        return JsonService::successful($list);
    }

    /**
     * 修改用户通知为已查看
     * @param $nid
     * @return \think\response\Json
     */
    public function see_notice($nid)
    {
        UserNotice::seeNotice($this->userInfo['uid'],$nid);
        return JsonService::successful();
    }
    /*
     * 用户提现申请
     * @param array
     * @return \think\response\Json
     * */
    public function user_extract()
    {
        $data=UtilService::postMore([
            ['alipay_code',''],
            ['extract_type',''],
            ['money',0],
            ['name',''],
            ['bankname',''],
            ['cardnum',''],
            ['weixin',''],
        ],$this->request);
        if(UserExtract::userExtract($this->userInfo,$data))
            return JsonService::successful('申请提现成功!');
        else
            return JsonService::fail(UserExtract::getErrorInfo('提现失败'));
    }
    /**
     * 用户下级的订单
     * @param int $first
     * @param int $limit
     * @return json
     */
    public function subordinateOrderlist($first = 0, $limit = 8)
    {
        list($xUid,$status)=UtilService::postMore([
            ['uid',''],
            ['status',''],
        ],$this->request,true);
        switch ($status){
            case 0:
                $type='';
                break;
            case 1:
                $type=4;
                break;
            case 2:
                $type=3;
                break;
            default:
                return JsonService::fail();
        }
        return JsonService::successful(StoreOrder::getSubordinateOrderlist($xUid,$this->uid,$type,$first,$limit));
    }

    /**
     * 个人中心 用户下级的订单
     * @param int $first
     * @param int $limit
     * @return json
     */
    public function subordinateOrderlistmoney()
    {
        $request = Request::instance();
        $lists=$request->param();
        $status = $lists['status'];
        $type = '';
        if($status == 1) $type = 4;
        elseif($status == 2) $type = 3;
        $arr = User::where('spread_uid',$this->userInfo['uid'])->column('uid');
        $list = StoreOrder::getUserOrderCount(implode(',',$arr),$type);
        $price = [];
//        if(!empty($list)) foreach ($list as $k=>$v) $price[]=$v['pay_price'];
        if(!empty($list)) foreach ($list as $k=>$v) $price[]=$v;
        $cont = count($list);
        $sum = array_sum($price);
        return JsonService::successful(['cont'=>$cont,'sum'=>$sum]);
    }

    /*
     * 用户提现记录列表
     * @param int $first 截取行数
     * @param int $limit 展示条数
     * @return json
     */
    public function extract($first = 0,$limit = 8)
    {
        return JsonService::successful(UserExtract::extractList($this->uid,$first,$limit));
    }

    /**
     * 个人中心 订单 评价订单
     * @param string $unique
     * @return \think\response\Json
     */
    public function user_comment_product($unique = '')
    {
        if(!$unique) return JsonService::fail('参数错误!');
        $cartInfo = StoreOrderCartInfo::where('unique',$unique)->find();
        $uid = $this->userInfo['uid'];
        if(!$cartInfo || $uid != $cartInfo['cart_info']['uid']) return JsonService::fail('评价产品不存在!');
        if(StoreProductReply::be(['oid'=>$cartInfo['oid'],'unique'=>$unique]))
            return JsonService::fail('该产品已评价!');
        $group = UtilService::postMore([
            ['comment',''],['pics',[]],['product_score',5],['service_score',5]
        ],Request::instance());
        $group['comment'] = htmlspecialchars(trim($group['comment']));
        if($group['product_score'] < 1) return JsonService::fail('请为产品评分');
        else if($group['service_score'] < 1) return JsonService::fail('请为商家服务评分');
        if($cartInfo['cart_info']['combination_id']) $productId = $cartInfo['cart_info']['product_id'];
        else if($cartInfo['cart_info']['seckill_id']) $productId = $cartInfo['cart_info']['product_id'];
        else if($cartInfo['cart_info']['bargain_id']) $productId = $cartInfo['cart_info']['product_id'];
        else $productId = $cartInfo['product_id'];
        $group = array_merge($group,[
            'uid'=>$uid,
            'oid'=>$cartInfo['oid'],
            'unique'=>$unique,
            'product_id'=>$productId,
            'reply_type'=>'product'
        ]);
        StoreProductReply::beginTrans();
        $res = StoreProductReply::reply($group,'product');
        if(!$res) {
            StoreProductReply::rollbackTrans();
            return JsonService::fail('评价失败!');
        }
        try{
//            HookService::listen('store_product_order_reply',$group,$cartInfo,false,StoreProductBehavior::class);
            StoreOrder::checkOrderOver($cartInfo['oid']);
        }catch (\Exception $e){
            StoreProductReply::rollbackTrans();
            return JsonService::fail($e->getMessage());
        }
        StoreProductReply::commitTrans();
        return JsonService::successful();
    }

    /*
     * 个人中心 查物流
     * @param int $uid 用户id
     * @param string $uni 订单id或者订单唯一键
     * @return json
     */
    public function express($uni = '')
    {
        if(!$uni || !($order = StoreOrder::getUserOrderDetail($this->uid,$uni))) return JsonService::fail('查询订单不存在!');
        if($order['delivery_type'] != 'express' || !$order['delivery_id']) return JsonService::fail('该订单不存在快递单号!');
        $cacheName = $uni.$order['delivery_id'];
        CacheService::rm($cacheName);
        $result = CacheService::get($cacheName,null);
        if($result === NULL){
            $result = Express::query($order['delivery_id']);
            if(is_array($result) &&
                isset($result['result']) &&
                isset($result['result']['deliverystatus']) &&
                $result['result']['deliverystatus'] >= 3)
                $cacheTime = 0;
            else
                $cacheTime = 1800;
            CacheService::set($cacheName,$result,$cacheTime);
        }
        return JsonService::successful([ 'order'=>StoreOrder::tidyOrder($order,true), 'express'=>$result ? $result : []]);
    }

    /**
     * 修改收货地址
     * @return \think\response\Json
     */
    public function edit_user_address()
    {
        $request = Request::instance();
        if(!$request->isPost()) return JsonService::fail('参数错误!');
        $addressInfo = UtilService::postMore([
            ['address',[]],
            ['is_default',false],
            ['real_name',''],
            ['post_code',''],
            ['phone',''],
            ['detail',''],
            ['id',0]
        ],$request);
        $addressInfo['province'] = $addressInfo['address']['province'];
        $addressInfo['city'] = $addressInfo['address']['city'];
        $addressInfo['district'] = $addressInfo['address']['district'];
        $addressInfo['is_default'] = $addressInfo['is_default'] == true ? 1 : 0;
        $addressInfo['uid'] = $this->userInfo['uid'];
        unset($addressInfo['address']);

        if($addressInfo['id'] && UserAddress::be(['id'=>$addressInfo['id'],'uid'=>$this->userInfo['uid'],'is_del'=>0])){
            $id = $addressInfo['id'];
            unset($addressInfo['id']);
            if(UserAddress::edit($addressInfo,$id,'id')){
                if($addressInfo['is_default'])
                    UserAddress::setDefaultAddress($id,$this->userInfo['uid']);
                return JsonService::successful();
            }else
                return JsonService::fail('编辑收货地址失败!');
        }else{
            if($address = UserAddress::set($addressInfo)){
                if($addressInfo['is_default'])
                    UserAddress::setDefaultAddress($address->id,$this->userInfo['uid']);
                return JsonService::successful(['id'=>$address->id]);
            }else
                return JsonService::fail('添加收货地址失败!');
        }
    }

    /**
     * 用户通知
     * @param int $page
     * @param int $limit
     * @return \think\response\Json
     */
    public function get_notice_list($page = 0, $limit = 8)
    {
        $list = UserNotice::getNoticeList($this->userInfo['uid'],$page,$limit);
        return JsonService::successful($list);
    }

    /*
    * 昨日推广佣金
     * @return json
    */
    public function yesterday_commission()
    {
        return JsonService::successful(UserBill::yesterdayCommissionSum($this->uid));
    }

    /*
     * 累计已提金额
     * @return json
     */
    public function extractsum()
    {
        return JsonService::successful(UserExtract::extractSum($this->uid));
    }

    /**
     * 绑定推荐人
     * @param Request $request
     * @return \think\response\Json
     */
    public function spread_uid(Request $request){
        $data = UtilService::postMore(['spread_uid',0],$request);
        if($data['spread_uid']){
            if(!$this->userInfo['spread_uid']){
                $res = User::edit(['spread_uid'=>$data['spread_uid']],$this->userInfo['uid']);
                if($res) return JsonService::successful('绑定成功');
                else return JsonService::successful('绑定失败');
            }else return JsonService::fail('已存在被推荐人');
        }else return JsonService::fail('没有推荐人');
    }

    /**
     * 设置为默认地址
     * @param string $addressId
     * @return \think\response\Json
     */
    public function set_user_default_address($addressId = '')
    {
        if(!$addressId || !is_numeric($addressId)) return JsonService::fail('参数错误!');
        if(!UserAddress::be(['is_del'=>0,'id'=>$addressId,'uid'=>$this->userInfo['uid']]))
            return JsonService::fail('地址不存在!');
        $res = UserAddress::setDefaultAddress($addressId,$this->userInfo['uid']);
        if(!$res)
            return JsonService::fail('地址不存在!');
        else
            return JsonService::successful();
    }

    /**
     * 获取分销二维码
     * @return \think\response\Json
     */
    public  function get_code(){
        header('content-type:image/jpg');
        if(!$this->userInfo['uid']) return JsonService::fail('授权失败，请重新授权');
        $path = makePathToUrl('routine/code');
        if($path == '')
            return JsonService::fail('生成上传目录失败,请检查权限!');
        $picname = $path.'/'.$this->userInfo['uid'].'.jpg';
        $domain = SystemConfigService::get('site_url').'/';
        $domainTop = substr($domain,0,5);
        if($domainTop != 'https') $domain = 'https:'.substr($domain,5,strlen($domain));
        if(file_exists($picname)) return JsonService::successful($domain.$picname);
        else{
            $res = RoutineCode::getCode($this->userInfo['uid'],$picname);
            if($res) file_put_contents($picname,$res);
            else return JsonService::fail('二维码生成失败');
        }
        return JsonService::successful($domain.$picname);
    }

    /*
     * 修改用户信息
     * */
    public function edit_user($formid=''){
        list($avatar,$nickname)=UtilService::postMore([
            ['avatar',''],
            ['nickname',''],
        ],$this->request,true);
        RoutineFormId::SetFormId($formid,$this->uid);
        if(User::editUser($avatar,$nickname,$this->uid))
            return JsonService::successful('修改成功');
        else
            return JsonService::fail('');
    }

    /*
     * 查找用户消费充值记录
     *
     * */
    public function get_user_bill_list($page=1,$limit=8,$type=0)
    {
        return JsonService::successful(UserBill::getUserBillList($this->uid,$page,$limit,$type));
    }

    /*
     * 获取活动是否存在
     * */
    public function get_activity()
    {
        $data['is_bargin']=StoreBargain::validBargain() ? true : false;
        $data['is_pink']=StoreCombination::getPinkIsOpen() ? true : false;
        $data['is_seckill']=StoreSeckill::getSeckillCount() ? true : false;
        return JsonService::successful($data);
    }

    /**
     * TODO 获取记录总和
     * @param int $type
     */
    public function get_record_list_count($type = 3)
    {
        $count = 0;
        if($type == 3) $count = UserBill::getRecordCount($this->uid, 'now_money', 'brokerage');
        else if($type == 4) $count = UserExtract::userExtractTotalPrice($this->uid);//累计提现
        $count = $count ? $count : 0;
        JsonService::successful('',$count);
    }

    /**
     * TODO 获取订单返佣记录
     * @param int $first
     * @param int $limit
     * @param string $category
     * @param string $type
     */
    public function get_record_order_list($page = 0,$limit = 8,$category = 'now_money', $type = 'brokerage'){
        $data['list'] = [];
        $data['count'] = 0;
        $data['list'] = UserBill::getRecordList($this->uid,$page,$limit,$category,$type);
        $count = UserBill::getRecordOrderCount($this->uid, $category, $type);
        $data['count'] = $count ? $count : 0;
        if(!count($data['list'])) return JsonService::successful([]);
        foreach ($data['list'] as $key=>&$value){
            $value['child'] = UserBill::getRecordOrderListDraw($this->uid, $value['time'],$category, $type);
            $value['count'] = count($value['child']);
        }
        return JsonService::successful($data);
    }

    /**
     * TODO 获取推广人列表
     * @param int $first
     * @param int $limit
     * @param int $type
     * @param int $keyword
     * @param string $order
     */
    public function user_spread_new_list($page = 0,$limit = 8,$grade = 0,$keyword  = 0,$sort = ''){
        if(!$keyword) $keyword = '';
        $data['list'] = User::getUserSpreadGrade($this->userInfo['uid'],$grade,$sort,$keyword,$page,$limit);
        $data['total'] = User::getSpreadCount($this->uid);
        $data['totalLevel'] = User::getSpreadLevelCount($this->uid);
        return JsonService::successful($data);
    }

    /**
     * 分销二维码海报生成
     */
    public function user_spread_banner_list(){
        try{
            $routineSpreadBanner = GroupDataService::getData('routine_spread_banner');
            if(!count($routineSpreadBanner)) return JsonService::fail('暂无海报');
            $name = $this->userInfo['uid'].'_'.$this->userInfo['is_promoter'].'_user.jpg';
            $imageInfo = SystemAttachment::getInfo($name,'name');
            $siteUrl = SystemConfigService::get('site_url').DS;
            //检测远程文件是否存在
            if(isset($imageInfo['att_dir']) && strstr($imageInfo['att_dir'],'http')!==false && UtilService::CurlFileExist($imageInfo['att_dir']) === false){
                $imageInfo=null;
                SystemAttachment::where(['name'=>$name])->delete();
            }
            if(!$imageInfo){
                $res = RoutineCode::getShareCode($this->uid, 'spread', '', '');
                if(!$res) return JsonService::fail('二维码生成失败');
                $imageInfo = UploadService::imageStream($name,$res['res'],'routine/spread/code');
                if(!is_array($imageInfo)) return JsonService::fail($imageInfo);
                SystemAttachment::attachmentAdd($imageInfo['name'],$imageInfo['size'],$imageInfo['type'],$imageInfo['dir'],$imageInfo['thumb_path'],1,$imageInfo['image_type'],$imageInfo['time'],2);
                RoutineQrcode::setRoutineQrcodeFind($res['id'],['status'=>1,'time'=>time(),'qrcode_url'=>$imageInfo['dir']]);
                $urlCode = $imageInfo['dir'];
            }else $urlCode = $imageInfo['att_dir'];
            if($imageInfo['image_type'] == 1) $urlCode = ROOT_PATH.$urlCode;
            $res = true;
            $domainTop = substr($siteUrl,0,5);
            if($domainTop != 'https') $siteUrl = 'https:'.substr($siteUrl,5,strlen($siteUrl));
            $filelink=[
                'Bold'=>'public/static/font/Alibaba-PuHuiTi-Regular.otf',
                'Normal'=>'public/static/font/Alibaba-PuHuiTi-Regular.otf',
            ];
            if(!file_exists($filelink['Bold'])) return JsonService::fail('缺少字体文件Bold');
            if(!file_exists($filelink['Normal'])) return JsonService::fail('缺少字体文件Normal');
            foreach ($routineSpreadBanner as $key=>&$item){
                $posterInfo = '海报生成失败:(';
                $config = array(
                    'image'=>array(
                        array(
                            'url'=>strstr($urlCode,ROOT_PATH) === false && strpos($urlCode,'http') === false ? str_replace('//','/',ROOT_PATH.$urlCode) : $urlCode,     //二维码资源
                            'stream'=>0,
                            'left'=>114,
                            'top'=>790,
                            'right'=>0,
                            'bottom'=>0,
                            'width'=>120,
                            'height'=>120,
                            'opacity'=>100
                        )
                    ),
                    'text'=>array(
                        array(
                            'text'=>$this->userInfo['nickname'],
                            'left'=>250,
                            'top'=>840,
                            'fontPath'=>ROOT_PATH.$filelink['Bold'],     //字体文件
                            'fontSize'=>16,             //字号
                            'fontColor'=>'40,40,40',       //字体颜色
                            'angle'=>0,
                        ),
                        array(
                            'text'=>'邀请您加入'.SystemConfigService::get('site_name'),
                            'left'=>250,
                            'top'=>880,
                            'fontPath'=>ROOT_PATH.$filelink['Normal'],     //字体文件
                            'fontSize'=>16,             //字号
                            'fontColor'=>'40,40,40',       //字体颜色
                            'angle'=>0,
                        )
                    ),
                    'background'=>$item['pic']
                );
                $res = $res && $posterInfo = UtilService::setSharePoster($config,'routine/spread/poster');
                if(!is_array($posterInfo)) return JsonService::fail($posterInfo);
                SystemAttachment::attachmentAdd($posterInfo['name'],$posterInfo['size'],$posterInfo['type'],$posterInfo['dir'],$posterInfo['thumb_path'],1,$posterInfo['image_type'],$posterInfo['time'],2);
                if($res){
                    if($posterInfo['image_type'] == 1) $item['poster'] = $siteUrl.$posterInfo['dir'];
                    else $item['poster'] = $posterInfo['dir'];
                    $item['poster'] = str_replace('\\','/',$item['poster']);
                    if(strstr($item['poster'],'http')===false) $item['poster']=SystemConfigService::get('site_url').$item['poster'];
                }
            }
            if($res) return JsonService::successful($routineSpreadBanner);
            else return JsonService::fail('生成图片失败');
        }catch (\Exception $e){
            return JsonService::fail('生成图片时，系统错误',['line'=>$e->getLine(),'message'=>$e->getMessage()]);
        }
    }

}