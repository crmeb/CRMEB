<?php
namespace app\routine\controller;

use Api\Express;
use app\routine\model\routine\RoutineCode;
use app\routine\model\routine\RoutineFormId;
use app\routine\model\routine\RoutineTemplate;
use app\routine\model\store\StoreCombination;
use behavior\routine\RoutineBehavior;
use service\JsonService;
use service\GroupDataService;
use service\RoutineBizDataCrypt;
use service\SystemConfigService;
use service\UploadService;
use service\UtilService;
use think\Request;
use behavior\wap\StoreProductBehavior;
use service\WechatTemplateService;
use service\CacheService;
use service\HookService;
use think\Url;
use app\routine\model\store\StoreCouponUser;
use app\routine\model\store\StoreOrder;
use app\routine\model\store\StoreProductRelation;
use app\routine\model\store\StoreProductAttr;
use app\routine\model\store\StoreProductAttrValue;
use app\routine\model\store\StoreProductReply;
use app\routine\model\store\StoreCart;
use app\routine\model\store\StoreCategory;
use app\routine\model\store\StoreProduct;
use app\routine\model\store\StoreSeckill;
use app\routine\model\user\User;
use app\routine\model\user\UserNotice;
use app\routine\model\store\StoreCouponIssue;
use app\routine\model\store\StoreCouponIssueUser;
use app\routine\model\store\StoreOrderCartInfo;
use app\routine\model\store\StorePink;
use app\routine\model\store\StoreService;
use app\routine\model\store\StoreServiceLog;
use app\routine\model\user\UserAddress;
use app\routine\model\user\UserBill;
use app\routine\model\user\UserExtract;
use app\routine\model\user\UserRecharge;
use app\routine\model\user\UserSign;
use app\routine\model\user\WechatUser;
use app\admin\model\system\SystemConfig;
use app\routine\model\store\StoreBargain;
use app\routine\model\store\StoreBargainUser;
use app\routine\model\store\StoreBargainUserHelp;
use app\routine\model\article\Article as ArticleModel;

/**
 * 小程序接口
 * Class AuthApi
 * @package app\routine\controller
 *
 *
 *
 *
 * https://qipei.9gt.net/routine/auth_api/index  首页
 * https://qipei.9gt.net/routine/auth_api/store  分类页面
 * https://qipei.9gt.net/routine/auth_api/get_pid_cate  一级分类
 * https://qipei.9gt.net/routine/auth_api/get_id_cate  二级分类
 * https://qipei.9gt.net/routine/auth_api/get_product_list  分类页面产品
 */
class AuthApi extends AuthController{

    /**
     * 获取用户信息
     * @return \think\response\Json
     */
    public function get_user_info(){
        return JsonService::successful($this->userInfo);
    }

    /**
     * 获取退款理由
     */
    public function get_refund_reason(){
        $reason = SystemConfig::getValue('stor_reason')?:[];//退款理由
        $reason = str_replace("\r\n","\n",$reason);//防止不兼容
        $reason = explode("\n",$reason);
        return JsonService::successful($reason);
    }

    /**
     * 获取提现银行
     */
    public function get_user_extract_bank(){
        $extractBank = SystemConfig::getValue('user_extract_bank')?:[];//提现银行
        $extractBank = explode('=',$extractBank);
        return JsonService::successful($extractBank);
    }
    /**
     * 首页
     */
    public function index(){
        $banner = GroupDataService::getData('routine_home_banner')?:[];//banner图
        $menus = GroupDataService::getData('routine_home_menus')?:[];//banner图
        $lovely = GroupDataService::getData('routine_lovely')?:[];//猜你喜欢图
        $best = StoreProduct::getBestProduct('id,image,store_name,cate_id,price,unit_name,sort',8);//精品推荐
        $new = StoreProduct::getNewProduct('id,image,store_name,cate_id,price,unit_name,sort',3);//今日上新
        $hot = StoreProduct::getHotProduct('id,image,store_name,cate_id,price,unit_name,sort',6);//猜你喜欢
        $data['banner'] = $banner;
        $data['lovely'] = $lovely[0];
        $data['menus'] = $menus;
        $data['best'] = $best;
        $data['new'] = $new;
        $data['hot'] = $hot;
        return JsonService::successful($data);
    }

    /**
     * 猜你喜欢  加载
     * @param Request $request
     */
    public function get_hot_product(Request $request){
        $data = UtilService::postMore([['offset',0],['limit',0]],$request);
        $hot = StoreProduct::getHotProductLoading('id,image,store_name,cate_id,price,unit_name,sort',$data['offset'],$data['limit']);//猜你喜欢
        return JsonService::successful($hot);
    }
    /**
     * 分类搜索页面
     * @param Request $request
     * @return \think\response\Json
     */
    public function store(){
        $model = StoreProduct::validWhere();
        if($_GET){$data = $_GET['value'];
            if($data!=''){
                $model = $model->where('store_name','LIKE',"%$data%")->whereOr('keyword','LIKE',"%$data%");
                if((int)$data) $model = $model->whereOr('id',$data);
            }
            $list = $model->field('id,store_name,cate_id,image,sales,price,stock')->select()->toArray();
            return JsonService::successful($list);
        }
    }
    /**
     * 分类页面
     * @param Request $request
     * @return \think\response\Json
     */
    public function store1(Request $request){
        $data = UtilService::postMore([['keyword',''],['cid',''],['sid','']],$request);
        $keyword = addslashes($data['keyword']);
        $cid = intval($data['cid']);
        $sid = intval($data['sid']);
        $category = null;
        if($sid) $category = StoreCategory::get($sid);
        if($cid && !$category) $category = StoreCategory::get($cid);
        $data['keyword'] = $keyword;
        $data['cid'] = $cid;
        $data['sid'] = $sid;
        return JsonService::successful($data);
    }
    /**
     * 一级分类
     * @return \think\response\Json
     */
    public function get_pid_cate(){
        $data = StoreCategory::pidByCategory(0,'id,cate_name');//一级分类
        return JsonService::successful($data);
    }
    /**
     * 最小提现金额
     * @return \think\response\Json
     */
    public function minmoney(){
        $data = SystemConfig::getValue('user_extract_min_price');//最小提现金额
        return JsonService::successful($data);
    }
    /**
     * 二级分类
     * @param Request $request
     * @return \think\response\Json
     */
    public function get_id_cate(Request $request){
        $data = UtilService::postMore([['id',0]],$request);
        $dataCateA = [];
        $dataCateA[0]['id'] = $data['id'];
        $dataCateA[0]['cate_name'] = '全部商品';
        $dataCateA[0]['pid'] = 0;
        $dataCateE = StoreCategory::pidBySidList($data['id']);//根据一级分类获取二级分类
        if($dataCateE) $dataCateE = $dataCateE->toArray();
        $dataCate = [];
        $dataCate = array_merge_recursive($dataCateA,$dataCateE);
        return JsonService::successful($dataCate);
    }
    /**
     * 分类页面产品
     * @param string $keyword
     * @param int $cId
     * @param int $sId
     * @param string $priceOrder
     * @param string $salesOrder
     * @param int $news
     * @param int $first
     * @param int $limit
     * @return \think\response\Json
     */
    public function get_product_list(Request $request)
    {
       $data = UtilService::getMore([
           ['sid',0],
           ['cid',0],
           ['keyword',''],
           ['priceOrder',''],
           ['salesOrder',''],
           ['news',0],
           ['first',0],
           ['limit',0]
       ],$request);
        $sId = $data['sid'];
        $cId = $data['cid'];
        $keyword = $data['keyword'];
        $priceOrder = $data['priceOrder'];
        $salesOrder = $data['salesOrder'];
        $news = $data['news'];
        $first = $data['first'];
        $limit = $data['limit'];
        $model = StoreProduct::validWhere();
        if($sId){
            $model->where('cate_id',$sId);
        }elseif($cId){
            $sids = StoreCategory::pidBySidList($cId)?:[];
            if($sids){
                $sidsr = [];
                foreach($sids as $v){
                    $sidsr[] = $v['id'];
                }
                $model->where('cate_id','IN',$sidsr);
            }
        }
        if(!empty($keyword)) $model->where('keyword|store_name','LIKE',htmlspecialchars("%$keyword%"));
        if($news!=0) $model->where('is_new',1);
        $baseOrder = '';
        if($priceOrder) $baseOrder = $priceOrder == 'desc' ? 'price DESC' : 'price ASC';
//        if($salesOrder) $baseOrder = $salesOrder == 'desc' ? 'sales DESC' : 'sales ASC';//真实销量
        if($salesOrder) $baseOrder = $salesOrder == 'desc' ? 'ficti DESC' : 'ficti ASC';//虚拟销量
        if($baseOrder) $baseOrder .= ', ';
        $model->order($baseOrder.'sort DESC, add_time DESC');
        $list = $model->limit($first,$limit)->field('id,store_name,cate_id,image,sales,ficti,price,stock')->select()->toArray();
        return JsonService::successful($list);
    }
    /**
     * 购物车
     * @return \think\response\Json
     */
    public function get_cart_list(){
        return JsonService::successful(StoreCart::getUserProductCartList($this->userInfo['uid']));
    }
    /**
     * 商品详情页
     * @param Request $request
     */
    public function details(Request $request){
        $data = UtilService::postMore(['id'],$request);
        $id = $data['id'];
        if(!$id || !($storeInfo = StoreProduct::getValidProduct($id))) return JsonService::fail('商品不存在或已下架');
        $storeInfo['userCollect'] = StoreProductRelation::isProductRelation($id,$this->userInfo['uid'],'collect');
        list($productAttr,$productValue) = StoreProductAttr::getProductAttrDetail($id);
        setView($this->userInfo['uid'],$id,$storeInfo['cate_id'],'viwe');
        foreach ($productAttr as $k=>$v){
            $attr = $v['attr_values'];
//            unset($productAttr[$k]['attr_values']);
            foreach ($attr as $kk=>$vv){
                $productAttr[$k]['attr_value'][$kk]['attr'] =  $vv;
                $productAttr[$k]['attr_value'][$kk]['check'] =  false;
            }
        }
        $data['storeInfo'] = $storeInfo;
        $data['similarity'] = StoreProduct::cateIdBySimilarityProduct($storeInfo['cate_id'],'id,store_name,image,price,sales,ficti',4);
        $data['productAttr'] = $productAttr;
        $data['productValue'] = $productValue;
        $data['reply'] = StoreProductReply::getRecProductReply($storeInfo['id']);
        $data['replyCount'] = StoreProductReply::productValidWhere()->where('product_id',$storeInfo['id'])->count();
        $data['mer_id'] = StoreProduct::where('id',$storeInfo['id'])->value('mer_id');
        return JsonService::successful($data);
    }

    /**
     * 获取产品评论
     * @param int $productId
     * @return \think\response\Json
     */
    public function get_product_reply($productId = 0){
         if(!$productId) return JsonService::fail('参数错误');
         $replyCount = StoreProductReply::productValidWhere()->where('product_id',$productId)->count();
         $reply = StoreProductReply::getRecProductReply($productId);
        return JsonService::successful(['replyCount'=>$replyCount,'reply'=>$reply]);
}
    /**
     * 订单页面
     * @param Request $request
     * @return \think\response\Json
     */
    public function confirm_order(Request $request){
        $data = UtilService::postMore(['cartId'],$request);
        $cartId = $data['cartId'];
        if(!is_string($cartId) || !$cartId ) return JsonService::fail('请提交购买的商品');
        $cartGroup = StoreCart::getUserProductCartList($this->userInfo['uid'],$cartId,1);
        if(count($cartGroup['invalid'])) return JsonService::fail($cartGroup['invalid'][0]['productInfo']['store_name'].'已失效!');
        if(!$cartGroup['valid']) return JsonService::fail('请提交购买的商品');
        $cartInfo = $cartGroup['valid'];
        $priceGroup = StoreOrder::getOrderPriceGroup($cartInfo);
        $other = [
            'offlinePostage'=>SystemConfigService::get('offline_postage'),
            'integralRatio'=>SystemConfigService::get('integral_ratio')
        ];
        $usableCoupon = StoreCouponUser::beUsableCoupon($this->userInfo['uid'],$priceGroup['totalPrice']);
        $cartIdA = explode(',',$cartId);
        if(count($cartIdA) > 1) $seckill_id=0;
        else{
            $seckillinfo = StoreCart::where('id',$cartId)->find();
            if((int)$seckillinfo['seckill_id']>0) $seckill_id=$seckillinfo['seckill_id'];
            else $seckill_id=0;
        }
        $data['usableCoupon'] = $usableCoupon;
        $data['seckill_id'] = $seckill_id;
        $data['cartInfo'] = $cartInfo;
        $data['priceGroup'] = $priceGroup;
        $data['orderKey'] = StoreOrder::cacheOrderInfo($this->userInfo['uid'],$cartInfo,$priceGroup,$other);
        $data['offlinePostage'] = $other['offlinePostage'];
        $data['userInfo'] = User::getUserInfo($this->userInfo['uid']);
        $data['integralRatio'] = $other['integralRatio'];
        return JsonService::successful($data);
    }

    /**
     * 获取可以使用的优惠券
     * @param int $totalPrice
     * @return \think\response\Json
     */
    public function get_use_coupon_order($totalPrice = 0){
        return JsonService::successful(StoreCouponUser::beUsableCouponList($this->userInfo['uid'],$totalPrice));
    }
    /**
     * 秒杀列表页
     * @return \think\response\Json
     */
    public function seckill_index(){
        $lovely = GroupDataService::getData('routine_lovely')?:[];//banner图
        $seckill = StoreSeckill::where('is_del',0)->where('status',1)->where('start_time','<',time())->where('stop_time','>',time())->order('sort desc')->select()->toArray();
        $data['seckill'] = $seckill;
        $data['lovely'] = $lovely[1];
        return JsonService::successful($data);
    }
    /**
     * 秒杀详情页
     * @param Request $request
     * @return \think\response\Json
     */
    public function seckill_detail(Request $request){
        $data = UtilService::postMore(['id'],$request);
        $id = $data['id'];
        if(!$id || !($storeInfo = StoreSeckill::getValidProduct($id))) return JsonService::fail('商品不存在或已下架!');
        $storeInfo['userLike'] = StoreProductRelation::isProductRelation($storeInfo['product_id'],$this->userInfo['uid'],'like','product_seckill');
        $storeInfo['like_num'] = StoreProductRelation::productRelationNum($storeInfo['product_id'],'like','product_seckill');
        $storeInfo['userCollect'] = StoreProductRelation::isProductRelation($storeInfo['product_id'],$this->userInfo['uid'],'collect','product_seckill');
        $data['storeInfo'] = $storeInfo;
        setView($this->userInfo['uid'],$id,$storeInfo['product_id'],'viwe');
        $data['reply'] = StoreProductReply::getRecProductReply($storeInfo['product_id']);
        $data['replyCount'] = StoreProductReply::productValidWhere()->where('product_id',$storeInfo['id'])->count();
        return JsonService::successful($data);
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
        $this->userInfo['statu'] = (int)SystemConfig::getValue('store_brokerage_statu');
//        $this->userInfo['service_phone'] = SystemConfig::getValue('service_phone');
//        $this->userInfo['service_phone_str'] = SystemConfig::getValue('service_phone_str');
        return JsonService::successful($this->userInfo);
    }


    /**
     * 用户签到
     * @return \think\response\Json
     */
    public function user_sign()
    {
        $signed = UserSign::checkUserSigned($this->userInfo['uid']);
        if($signed) return JsonService::fail('已签到');
        if(false !== $integral = UserSign::sign($this->userInfo))
            return JsonService::successful('签到获得'.floatval($integral).'积分');
        else
            return JsonService::fail('签到失败!');
    }
    /**
     * 过度查$uniqueId
     * @param string $productId
     * @param int $cartNum
     * @param string $uniqueId
     * @return \think\response\Json
     */
    public function unique(){
        $productId=$_GET['productId'];
        if(!$productId || !is_numeric($productId)) return JsonService::fail('参数错误');
        $uniqueId=StoreProductAttrValue::where('product_id',$productId)->value('unique');
        $data=$this->set_cart($productId,$cartNum = 1,$uniqueId);
        if($data==true){
            return JsonService::successful('ok');
        }

    }
    /**
     * 加入到购物车
     * @param string $productId
     * @param int $cartNum
     * @param string $uniqueId
     * @return \think\response\Json
     */
    public function set_cart($productId = '',$cartNum = 1,$uniqueId = ''){
        if(!$productId || !is_numeric($productId)) return JsonService::fail('参数错误');
        $res = StoreCart::setCart($this->userInfo['uid'],$productId,$cartNum,$uniqueId,'product');
        if(!$res) return JsonService::fail(StoreCart::getErrorInfo());
        else{
//            HookService::afterListen('store_product_set_cart_after',$res,$this->userInfo,false,StoreProductBehavior::class);
            return JsonService::successful('ok',['cartId'=>$res->id]);
        }
    }

    /**
     * 拼团 秒杀 砍价 加入到购物车
     * @param string $productId
     * @param int $cartNum
     * @param string $uniqueId
     * @param int $combinationId
     * @param int $secKillId
     * @return \think\response\Json
     */
    public function now_buy($productId = '',$cartNum = 1,$uniqueId = '',$combinationId = 0,$secKillId = 0,$bargainId = 0){
        if(!$productId || !is_numeric($productId)) return JsonService::fail('参数错误');
        if($bargainId && StoreBargainUserHelp::getSurplusPrice($bargainId,$this->userInfo['uid'])) return JsonService::fail('请先砍价');
        $res = StoreCart::setCart($this->userInfo['uid'],$productId,$cartNum,$uniqueId,'product',1,$combinationId,$secKillId,$bargainId);
        if(!$res) return JsonService::fail(StoreCart::getErrorInfo());
        else  return JsonService::successful('ok',['cartId'=>$res->id]);
    }

    /**
     * 添加点赞
     * @param string $productId
     * @param string $category
     * @return \think\response\Json
     */
    public function like_product($productId = '',$category = 'product'){
        if(!$productId || !is_numeric($productId))  return JsonService::fail('参数错误');
        $res = StoreProductRelation::productRelation($productId,$this->userInfo['uid'],'like',$category);
        if(!$res) return  JsonService::fail(StoreProductRelation::getErrorInfo());
        else return JsonService::successful();
    }

    /**
     * 取消点赞
     * @param string $productId
     * @param string $category
     * @return \think\response\Json
     */
    public function unlike_product($productId = '',$category = 'product'){
        if(!$productId || !is_numeric($productId)) return JsonService::fail('参数错误');
        $res = StoreProductRelation::unProductRelation($productId,$this->userInfo['uid'],'like',$category);
        if(!$res) return JsonService::fail(StoreProductRelation::getErrorInfo());
        else return JsonService::successful();
    }

    /**
     * 添加收藏
     * @param $productId
     * @param string $category
     * @return \think\response\Json
     */
    public function collect_product($productId,$category = 'product'){
        if(!$productId || !is_numeric($productId)) return JsonService::fail('参数错误');
        $res = StoreProductRelation::productRelation($productId,$this->userInfo['uid'],'collect',$category);
        if(!$res) return JsonService::fail(StoreProductRelation::getErrorInfo());
        else return JsonService::successful();
    }

    /**
     * 批量收藏
     * @param string $productId
     * @param string $category
     * @return \think\response\Json
     */
    public function collect_product_all($productId = '',$category = 'product'){
        if($productId == '') return JsonService::fail('参数错误');
        $productIdS = explode(',',$productId);
        $res = StoreProductRelation::productRelationAll($productIdS,$this->userInfo['uid'],'collect',$category);
        if(!$res) return JsonService::fail(StoreProductRelation::getErrorInfo());
        else return JsonService::successful();
    }

    /**
     * 取消收藏
     * @param $productId
     * @param string $category
     * @return \think\response\Json
     */
    public function uncollect_product($productId,$category = 'product'){
        if(!$productId || !is_numeric($productId)) return JsonService::fail('参数错误');
        $res = StoreProductRelation::unProductRelation($productId,$this->userInfo['uid'],'collect',$category);
        if(!$res) return JsonService::fail(StoreProductRelation::getErrorInfo());
        else return JsonService::successful();
    }

    /**
     * 获取购物车数量
     * @return \think\response\Json
     */
    public function get_cart_num(){
        return JsonService::successful('ok',StoreCart::getUserCartNum($this->userInfo['uid'],'product'));
    }

    /**
     * 修改购物车产品数量
     * @param string $cartId
     * @param string $cartNum
     * @return \think\response\Json
     */
    public function change_cart_num($cartId = '',$cartNum = ''){
        if(!$cartId || !$cartNum || !is_numeric($cartId) || !is_numeric($cartNum)) return JsonService::fail('参数错误!');
        StoreCart::changeUserCartNum($cartId,$cartNum,$this->userInfo['uid']);
        return JsonService::successful();
    }

    /**
     * 删除购物车产品
     * @param string $ids
     * @return \think\response\Json
     */
    public function remove_cart($ids=''){
        if(!$ids) return JsonService::fail('参数错误!');
        StoreCart::removeUserCart($this->userInfo['uid'],$ids);
        return JsonService::successful();
    }


    /**
     * 获取用户优惠券
     * @return \think\response\Json
     */
    public function get_use_coupons(){

        if($_GET){
            if($_GET['types']==0||$_GET['types']==''){
                $list= StoreCouponUser::getUserAllCoupon($this->userInfo['uid']);
            }elseif($_GET['types']==1){
                $list=StoreCouponUser::getUserValidCoupon($this->userInfo['uid']);
            }elseif($_GET['types']==2){
                $list=StoreCouponUser::getUserAlreadyUsedCoupon($this->userInfo['uid']);
            }else{
                $list=StoreCouponUser::getUserBeOverdueCoupon($this->userInfo['uid']);
            }
            foreach ($list as &$v){
                $v['add_time'] = date('Y/m/d',$v['add_time']);
                $v['end_time'] = date('Y/m/d',$v['end_time']);
            }
            return JsonService::successful($list);
        }

    }
    /**
     * 获取用户优惠券
     * @return \think\response\Json
     */
    public function get_use_coupon(){

        return JsonService::successful('',StoreCouponUser::getUserAllCoupon($this->userInfo['uid']));
    }

    /**
     * 获取收藏产品
     * @param int $first
     * @param int $limit
     * @return \think\response\Json
     */
    public function get_user_collect_product($first = 0,$limit = 8)
    {
        $list = StoreProductRelation::where('A.uid',$this->userInfo['uid'])
            ->field('B.id pid,B.store_name,B.price,B.ot_price,B.sales,B.image,B.is_del,B.is_show')->alias('A')
            ->where('A.type','collect')->where('A.category','product')
            ->order('A.add_time DESC')->join('__STORE_PRODUCT__ B','A.product_id = B.id')
            ->limit($first,$limit)->select()->toArray();
        foreach ($list as $k=>$product){
            if($product['pid']){
                $list[$k]['is_fail'] = $product['is_del'] && $product['is_show'];
            }else{
                unset($list[$k]);
            }
        }
        return JsonService::successful($list);
    }
    /**
     * 获取收藏产品删除
     * @param int $first
     * @param int $limit
     * @return \think\response\Json
     */
    public function get_user_collect_product_del()
    {
        if($_GET){
            $list = StoreProductRelation::where('uid',$this->userInfo['uid'])->where('product_id',$_GET['pid'])->delete();
            return JsonService::successful($list);
        }

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
                return JsonService::successful();
            }else
                return JsonService::fail('添加收货地址失败!');
        }
    }

    /**
     * 获取一条用户地址
     * @param string $addressId
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
     * @param string $addressId
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
     * 创建订单
     * @param string $key
     * @return \think\response\Json
     */
    public function create_order($key = '')
    {
        if(!$key) return JsonService::fail('参数错误!');
        if(StoreOrder::be(['order_id|unique'=>$key,'uid'=>$this->userInfo['uid'],'is_del'=>0]))
            return JsonService::status('extend_order','订单已生成',['orderId'=>$key,'key'=>$key]);
        list($addressId,$couponId,$payType,$useIntegral,$mark,$combinationId,$pinkId,$seckill_id,$formId,$bargainId) = UtilService::postMore([
            'addressId','couponId','payType','useIntegral','mark',['combinationId',0],['pinkId',0],['seckill_id',0],['formId',''],['bargainId','']
        ],Request::instance(),true);
        $payType = strtolower($payType);
        if($bargainId) StoreBargainUser::setBargainUserStatus($bargainId,$this->userInfo['uid']);//修改砍价状态
        if($pinkId) if(StorePink::getIsPinkUid($pinkId,$this->userInfo['uid'])) return JsonService::status('ORDER_EXIST','订单生成失败，你已经在该团内不能再参加了',['orderId'=>StoreOrder::getStoreIdPink($pinkId,$this->userInfo['uid'])]);
        if($pinkId) if(StoreOrder::getIsOrderPink($pinkId,$this->userInfo['uid'])) return JsonService::status('ORDER_EXIST','订单生成失败，你已经参加该团了，请先支付订单',['orderId'=>StoreOrder::getStoreIdPink($pinkId,$this->userInfo['uid'])]);
        $order = StoreOrder::cacheKeyCreateOrder($this->userInfo['uid'],$key,$addressId,$payType,$useIntegral,$couponId,$mark,$combinationId,$pinkId,$seckill_id,$bargainId);
        $orderId = $order['order_id'];
        $info = compact('orderId','key');
        if($orderId){
            if($payType == 'weixin'){
                $orderInfo = StoreOrder::where('order_id',$orderId)->find();
                if(!$orderInfo || !isset($orderInfo['paid'])) exception('支付订单不存在!');
                if($orderInfo['paid']) exception('支付已支付!');
                if(bcsub((float)$orderInfo['pay_price'],0,2) <= 0){
                    if(StoreOrder::jsPayPrice($orderId,$this->userInfo['uid'],$formId))
                        return JsonService::status('success','微信支付成功',$info);
                    else
                        return JsonService::status('pay_error',StoreOrder::getErrorInfo());
                }else{
                    try{
                        $jsConfig = StoreOrder::jsPay($orderId);
                    }catch (\Exception $e){
                        return JsonService::status('pay_error',$e->getMessage(),$info);
                    }
                    $info['jsConfig'] = $jsConfig;
                    return JsonService::status('wechat_pay','订单创建成功',$info);
                }
            }else if($payType == 'yue'){
                if(StoreOrder::yuePay($orderId,$this->userInfo['uid'],$formId))
                    return JsonService::status('success','余额支付成功',$info);
                else
                    return JsonService::status('pay_error',StoreOrder::getErrorInfo());
            }else if($payType == 'offline'){
//                RoutineTemplate::sendOrderSuccess($formId,$orderId);//发送模板消息
                return JsonService::status('success','订单创建成功',$info);
            }
        }else{
            return JsonService::fail(StoreOrder::getErrorInfo('订单生成失败!'));
        }
    }

    /**
     * 获取订单列表
     * @param string $type
     * @param int $first
     * @param int $limit
     * @param string $search
     * @return \think\response\Json
     */
    public function get_user_order_list($type = '',$first = 0, $limit = 8,$search = '')
    {
//        StoreOrder::delCombination();//删除拼团未支付订单
        if($search){
            $order = StoreOrder::searchUserOrder($this->userInfo['uid'],$search)?:[];
            $list = $order == false ? [] : [$order];
        }else{
            $list = StoreOrder::getUserOrderList($this->userInfo['uid'],$type,$first,$limit);
        }
        foreach ($list as $k=>$order){
            $list[$k] = StoreOrder::tidyOrder($order,true);
            if($list[$k]['_status']['_type'] == 3){
                foreach ($order['cartInfo']?:[] as $key=>$product){
                    $list[$k]['cartInfo'][$key]['is_reply'] = StoreProductReply::isReply($product['unique'],'product');
                }
            }
        }
        return JsonService::successful($list);
    }

    /**
     * 订单详情页
     * @param string $order_id
     * @return \think\response\Json
     */
    public function get_order($uni = ''){
        if($uni == '') return JsonService::fail('参数错误');
        $order = StoreOrder::getUserOrderDetail($this->userInfo['uid'],$uni);
        $order = $order->toArray();
        $order['add_time'] = date('Y-m-d H:i:s',$order['add_time']);
        if(!$order) return JsonService::fail('订单不存在');
        return JsonService::successful(StoreOrder::tidyOrder($order,true));
    }

    /**
     * 获取订单内的某个产品信息
     * @param string $uni
     * @param string $productId
     * @return \think\response\Json
     */
    public function get_order_product($unique = ''){
        if(!$unique || !StoreOrderCartInfo::be(['unique'=>$unique]) || !($cartInfo = StoreOrderCartInfo::where('unique',$unique)->find())) return JsonService::fail('评价产品不存在!');
        return JsonService::successful($cartInfo);
    }
    /**
     * 删除订单
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
     * 支付订单
     * @param string $uni
     * @return \think\response\Json
     */
    public function pay_order($uni = '')
    {
        if(!$uni) return JsonService::fail('参数错误!');
        $order= StoreOrder::getUserOrderDetail($this->userInfo['uid'],$uni);
        if(!$order) return JsonService::fail('订单不存在!');
        if($order['paid']) return JsonService::fail('该订单已支付!');
        if($order['pink_id']) if(StorePink::isPinkStatus($order['pink_id'])) return JsonService::fail('该订单已失效!');
        if($order['pay_type'] == 'weixin'){
            try{
                $jsConfig = StoreOrder::jsPay($order);
            }catch (\Exception $e){
                return JsonService::fail($e->getMessage());
            }
            return JsonService::status('wechat_pay',['jsConfig'=>$jsConfig,'order_id'=>$order['order_id']]);
        }else if($order['pay_type'] == 'yue'){
            if($res = StoreOrder::yuePay($order['order_id'],$this->userInfo['uid']))
                return JsonService::successful('余额支付成功');
            else
                return JsonService::fail(StoreOrder::getErrorInfo());
        }else if($order['pay_type'] == 'offline'){
            StoreOrder::createOrderTemplate($order);
            return JsonService::successful('订单创建成功');
        }
    }


    /**
     * 申请退款
     * @param string $uni
     * @param string $text
     * @return \think\response\Json
     */
    public function apply_order_refund(Request $request,$uni = '')
    {
        $data = UtilService::postMore([
            ['text',''],
            ['refund_reason_wap_img',''],
            ['refund_reason_wap_explain',''],
        ],$request);
        if($data['refund_reason_wap_img']) $data['refund_reason_wap_img'] = explode(',',$data['refund_reason_wap_img']);
        if(!$uni || $data['text'] == '') return JsonService::fail('参数错误!');
        $res = StoreOrder::orderApplyRefund($uni,$this->userInfo['uid'],$data['text'],$data['refund_reason_wap_explain'],$data['refund_reason_wap_img']);
        if($res)
            return JsonService::successful();
        else
            return JsonService::fail(StoreOrder::getErrorInfo());
    }
    /**
     * 用户确认收货
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
     * 充值
     * @param int $price
     * @return \think\response\Json
     */
    public function user_wechat_recharge($price = 0)
    {
        if(!$price || $price <=0) return JsonService::fail('参数错误');
        $storeMinRecharge = SystemConfigService::get('store_user_min_recharge');
        if($price < $storeMinRecharge) return JsonService::fail('充值金额不能低于'.$storeMinRecharge);
        $rechargeOrder = UserRecharge::addRecharge($this->userInfo['uid'],$price);
        if(!$rechargeOrder) return JsonService::fail('充值订单生成失败!');
        try{
            return JsonService::successful(UserRecharge::jsPay($rechargeOrder));
        }catch (\Exception $e){
            return JsonService::fail($e->getMessage());
        }
    }

    /**
     * 余额使用记录
     * @param int $first
     * @param int $limit
     * @return \think\response\Json
     */
    public function user_balance_list($first = 0,$limit = 8)
    {
        $list = UserBill::where('uid',$this->userInfo['uid'])->where('category','now_money')
            ->field('mark,pm,number,add_time')
            ->where('status',1)->order('add_time DESC')->limit($first,$limit)->select()->toArray();
        foreach ($list as &$v){
            $v['add_time'] = date('Y/m/d H:i',$v['add_time']);
        }
        return JsonService::successful($list);
    }

    /**
     * 积分使用记录
     * @param int $first
     * @param int $limit
     * @return \think\response\Json
     */
    public function user_integral_list($first = 0,$limit = 8)
    {
        $list = UserBill::where('uid',$this->userInfo['uid'])->where('category','integral')
            ->field('mark,pm,number,add_time')
            ->where('status',1)->order('add_time DESC')->limit($first,$limit)->select()->toArray();
        foreach ($list as &$v){
            $v['add_time'] = date('Y/m/d H:i',$v['add_time']);
            $v['number'] = floatval($v['number']);
        }
        return JsonService::successful($list);

    }

    /**
     * 评价订单
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

    /**
     * 上传图片
     * @param string $filename
     * @return \think\response\Json
     */
    public function upload(Request $request)
    {
        $data = UtilService::postMore([['filename','']],$request);
        $res = UploadService::image($data['filename'],'store/comment');
        if($res->status == 200)
            return JsonService::successful('图片上传成功!',['name'=>$res->fileInfo->getSaveName(),'url'=>UploadService::pathToUrl($res->dir)]);
        else
            return JsonService::fail($res->error);
    }

    /**
     * 获取一级和二级分类
     * @return \think\response\Json
     */
    public function get_product_category()
    {
        $parentCategory = StoreCategory::pidByCategory(0,'id,cate_name')->toArray();
        foreach ($parentCategory as $k=>$category){
            $category['child'] = StoreCategory::pidByCategory($category['id'],'id,cate_name')->toArray();
            $parentCategory[$k] = $category;
        }
        return JsonService::successful($parentCategory);
    }

    /**
     * 获取一级推荐人
     * @param int $first
     * @param int $limit
     * @return \think\response\Json
     */
    public function get_spread_list($first = 0,$limit = 20)
    {
        $list = User::where('spread_uid',$this->userInfo['uid'])->field('uid,nickname,avatar,add_time')->limit($first,$limit)->order('add_time DESC')->select()->toArray();
        foreach ($list as $k=>$user){
            $list[$k]['add_time'] = date('Y/m/d',$user['add_time']);
            $list[$k]['price'] = StoreOrder::getUserPrice($user['uid']);
        }
        $count = User::where('spread_uid',$this->userInfo['uid'])->field('uid,nickname,avatar,add_time')->count();
        $data['count'] = $count;
        $data['list'] = $list;
        return JsonService::successful($data);
    }

    /**
     * 获取二级推荐人
     * @param int $first
     * @param int $limit
     * @return \think\response\Json
     */
    public function get_spread_list_two($two_uid=0,$first = 0,$limit = 20)
    {
        $list = User::where('spread_uid',$two_uid)->field('uid,nickname,avatar,add_time')->limit($first,$limit)->order('add_time DESC')->select()->toArray();
        foreach ($list as $k=>$user){
            $list[$k]['add_time'] = date('Y/m/d',$user['add_time']);
            $list[$k]['price'] = StoreOrder::getUserPrice($user['uid']);
        }
        $count = User::where('spread_uid',$two_uid)->field('uid,nickname,avatar,add_time')->count();
        $data['count'] = $count;
        $data['list'] = $list;
        return JsonService::successful($data);
    }

    /**
     * 领取优惠券
     * @param string $couponId
     * @return \think\response\Json
     */
    public function user_get_coupon($couponId = '')
    {
        if(!$couponId || !is_numeric($couponId)) return JsonService::fail('参数错误!');
        if(StoreCouponIssue::issueUserCoupon($couponId,$this->userInfo['uid'])){
            return JsonService::successful('领取成功');
        }else{
            return JsonService::fail(StoreCouponIssue::getErrorInfo('领取失败!'));
        }
    }


    /**
     * 获取产品评论
     * @param string $productId
     * @param int $first
     * @param int $limit
     * @param string $filter
     * @return \think\response\Json
     */
    public function product_reply_list($productId = '',$first = 0,$limit = 8, $filter = 'all')
    {
        if(!$productId || !is_numeric($productId)) return JsonService::fail('参数错误!');
        $list = StoreProductReply::getProductReplyList($productId,$filter,$first,$limit);
        if($list){
            foreach ($list as $k=>$v){
                foreach ($v['pics'] as $kk=>$vv){
                    $list[$k]['pics'] = explode(',',$vv);
                }
            }
        }
        return JsonService::successful($list);
    }

    /**
     * 获取商品属性数据
     * @param string $productId
     * @return \think\response\Json
     */
    public function product_attr_detail($productId = '')
    {
        if(!$productId || !is_numeric($productId)) return JsonService::fail('参数错误!');
        list($productAttr,$productValue) = StoreProductAttr::getProductAttrDetail($productId);
        return JsonService::successful(compact('productAttr','productValue'));

    }

    /**
     * 获取用户所有地址
     * @return \think\response\Json
     */
    public function user_address_list()
    {
        $list = UserAddress::getUserValidAddressList($this->userInfo['uid'],'id,real_name,phone,province,city,district,detail,is_default');
        return JsonService::successful($list);
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

    /**
     * 修改用户通知为已查看
     * @param $nid
     * @return \think\response\Json
     */
    public function see_notice($nid){
        UserNotice::seeNotice($this->userInfo['uid'],$nid);
        return JsonService::successful();
    }

    /**
     * 客服提醒
     * @param Request $request
     * @return \think\response\Json
     */
    public function refresh_msn(Request $request)
    {
        $params = $request->post();
        $remind_where = "mer_id = ".$params["mer_id"]." AND uid = ".$params["uid"]." AND to_uid = ".$params["to_uid"]." AND type = 0 AND remind = 0";
        $remind_list = StoreServiceLog::where($remind_where)->order("add_time asc")->select();
        foreach ($remind_list as $key => $value) {
            if(time() - $value["add_time"] > 3){
                StoreServiceLog::edit(array("remind"=>1),$value["id"]);
                $now_user = StoreService::field("uid,nickname")->where(array("uid"=>$params["uid"]))->find();
                if(!$now_user)$now_user = User::field("uid,nickname")->where(array("uid"=>$params["uid"]))->find();
                if($params["to_uid"]) {
                    $head = '您有新的消息，请注意查收！';
                    $head .= $params["mer_id"] > 0 ? "\n商户名称：".Merchant::where('id',$params["mer_id"])->value('mer_name') : '';
                    WechatTemplateService::sendTemplate(WechatUser::uidToOpenid($params["to_uid"]),WechatTemplateService::SERVICE_NOTICE,[
                        'first'=>$head,
                        'keyword1'=>$now_user["nickname"],
                        'keyword2'=>"客服提醒",
                        'keyword3'=> preg_replace('/<img.*? \/>/','[图片]',$value["msn"]),
                        'keyword4'=>date('Y-m-d H:i:s',time()),
                        'remark'=>'点击立即查看消息'
                    ],Url::build('service/service_ing',['to_uid'=>$now_user["uid"],'mer_id'=>$params["mer_id"]],true,true));
                }
            }
        }
        $where = "mer_id = ".$params["mer_id"]." AND uid = ".$params["to_uid"]." AND to_uid = ".$params["uid"]." AND type = 0";
        $list = StoreServiceLog::where($where)->order("add_time asc")->select()->toArray();
        $ids = [];
        foreach ($list as $key => $value) {
            //设置发送人与接收人区别
            if($value["uid"] == $params["uid"])
                $list[$key]['my'] = "my";
            else
                $list[$key]['my'] = "to";

            array_push($ids,$value["id"]);
        }

        //设置这些消息为已读
        StoreServiceLog::where(array("id"=>array("in",$ids)))->update(array("type"=>1,"remind"=>1));
        return JsonService::successful($list);
    }

    public function add_msn(Request $request){
        $params = $request->post();
        if($params["type"] == "html")
            $data["msn"] = htmlspecialchars_decode($params["msn"]);
        else
            $data["msn"] = $params["msn"];
        $data["uid"] = $params["uid"];
        $data["to_uid"] = $params["to_uid"];
        $data["mer_id"] = $params["mer_id"] > 0 ? $params["mer_id"] : 0;
        $data["add_time"] = time();
        StoreServiceLog::set($data);
        return JsonService::successful();
    }

    public function get_msn(Request $request){
        $params = $request->post();
        $size = 10;
        $page = $params["page"]>=0 ? $params["page"] : 1;
        $where = "(mer_id = ".$params["mer_id"]." AND uid = ".$params["uid"]." AND to_uid = ".$params["to_uid"].") OR (mer_id = ".$params["mer_id"]." AND uid = ".$params["to_uid"]." AND to_uid = ".$params["uid"].")";
        $list = StoreServiceLog::where($where)->limit(($page-1)*$size,$size)->order("add_time desc")->select()->toArray();
        foreach ($list as $key => $value) {
            //设置发送人与接收人区别
            if($value["uid"] == $params["uid"])
                $list[$key]['my'] = "my";
            else
                $list[$key]['my'] = "to";

            //设置这些消息为已读
            if($value["uid"] == $params["to_uid"] && $value["to_uid"] == $params["uid"])StoreServiceLog::edit(array("type"=>1,"remind"=>1),$value["id"]);
        }
        $list=array_reverse($list);
        return JsonService::successful($list);
    }

    public function refresh_msn_new(Request $request){
        $params = $request->post();
        $now_user = User::getUserInfo($this->userInfo['uid']);
        if($params["last_time"] > 0)
            $where = "(uid = ".$now_user["uid"]." OR to_uid = ".$now_user["uid"].") AND add_time>".$params["last_time"];
        else
            $where = "uid = ".$now_user["uid"]." OR to_uid = ".$now_user["uid"];


        $msn_list = StoreServiceLog::where($where)->order("add_time desc")->select()->toArray();
        $info_array = $list = [];
        foreach ($msn_list as $key => $value){
            $to_uid = $value["uid"] == $now_user["uid"] ? $value["to_uid"] : $value["uid"];
            if(!in_array(["to_uid"=>$to_uid,"mer_id"=>$value["mer_id"]],$info_array)){
                $info_array[count($info_array)] = ["to_uid"=>$to_uid,"mer_id"=>$value["mer_id"]];

                $to_user = StoreService::field("uid,nickname,avatar")->where(array("uid"=>$to_uid))->find();
                if(!$to_user)$to_user = User::field("uid,nickname,avatar")->where(array("uid"=>$to_uid))->find();
                $to_user["mer_id"] = $value["mer_id"];
                $to_user["mer_name"] = $value["mer_id"] > 0 ? "[".Merchant::where('id',$value["mer_id"])->value('mer_name')."]" : '';
                $value["to_info"] = $to_user;
                $value["count"] = StoreServiceLog::where(array("mer_id"=>$value["mer_id"],"uid"=>$to_uid,"to_uid"=>$now_user["uid"],"type"=>0))->count();
                $list[count($list)] = $value;
            }
        }
        return JsonService::successful($list);
    }

    public function get_user_brokerage_list($uid, $first = 0,$limit = 8)
    {
        if(!$uid)
            return $this->failed('用户不存在');
        $list = UserBill::field('A.mark,A.add_time,A.number,A.pm')->alias('A')->limit($first,$limit)
            ->where('A.category','now_money')->where('A.type','brokerage')
            ->where('A.uid',$this->userInfo['uid'])
            ->join('__STORE_ORDER__ B','A.link_id = B.id AND B.uid = '.$uid)->select()->toArray();
        return JsonService::successful($list);
    }
    
    public function user_extract()
    {   $request = Request::instance();
        $list=$request->param();
        $data=$list['lists'];
        if(UserExtract::userExtract($this->userInfo,$data))
            return JsonService::successful('申请提现成功!');
        else
            return JsonService::fail(UserExtract::getErrorInfo());
    }

/*
 * 提现列表
 */
    public function extract($first = 0,$limit = 8)
    {
        $list=UserExtract::where('uid',$this->userInfo['uid'])->order('add_time desc')->limit($first,$limit)->select()->toArray();
        foreach($list as &$v){
            $v['add_time']=date('Y/m/d',$v['add_time']);
        }
       
        return JsonService::successful($list);

    }

    /**
     * 用户下级的订单
     * @param int $first
     * @param int $limit
     */
    public function subordinateOrderlist($first = 0, $limit = 8){
        $request = Request::instance();
        $lists=$request->param();
        $xUid = $lists['uid'];
        $status = $lists['status'];
        if($status == 0) $type='';
        elseif($status == 1) $type=4;
        elseif($status == 2) $type=3;
        else return false;
        $list = [];
        if(!$xUid){
            $arr = User::where('spread_uid',$this->userInfo['uid'])->column('uid');
            foreach($arr as $v) $list = StoreOrder::getUserOrderList($v,$type,$first,$limit);
        }else $list = StoreOrder::getUserOrderList($xUid,$type,$first,$limit);
        foreach ($list as $k=>$order){
            $list[$k] = StoreOrder::tidyOrder($order,true);
            if($list[$k]['_status']['_type'] == 3){
                foreach ($order['cartInfo']?:[] as $key=>$product){
                    $list[$k]['cartInfo'][$key]['is_reply'] = StoreProductReply::isReply($product['unique'],'product');
                }
            }
        }
        return JsonService::successful($list);
    }
    /**
     * 用户下级的订单
     * @param int $first
     * @param int $limit
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
        if(!empty($list)) foreach ($list as $k=>$v) $price[]=$v['pay_price'];
        $cont = count($list);
        $sum = array_sum($price);
        return JsonService::successful(['cont'=>$cont,'sum'=>$sum]);
    }
    /*
   * 昨日推广佣金
   */
    public function yesterdayCommission(){
        $money=UserBill::where('uid',$this->userInfo['uid'])->where('category','now_money')->where('type','brokerage')->where('pm',1)->where('status',1)->whereTime('add_time', 'yesterday')->column('number');
        $sum= array_sum($money);
        return JsonService::successful($sum);
    }
    /*
 * 累计已提金额
 */
    public function extractsum(){
        $money=UserExtract::where('uid',$this->userInfo['uid'])->where('status',1)->column('extract_price');
        $sum= array_sum($money);
        return JsonService::successful($sum);
    }
    /**
     * 获取一条优惠券
     * @param int $couponId
     * @return \think\response\Json
     */
    public function get_coupon_rope($couponId = 0){
        if(!$couponId) return JsonService::fail('参数错误');
        $couponUser = StoreCouponUser::validAddressWhere()->where('id',$couponId)->where('uid',$this->userInfo['uid'])->find();
        return JsonService::successful($couponUser);
    }
    /**
     * 获取  可以领取的优惠券
     * @param int $limit
     * @return \think\response\Json
     */
    public function get_issue_coupon_list($limit = 2)
    {
        $list = StoreCouponIssue::validWhere('A')->join('__STORE_COUPON__ B','A.cid = B.id')
            ->field('A.*,B.coupon_price,B.use_min_price')->order('B.sort DESC,A.id DESC')->limit($limit)->select()->toArray()?:[];
        foreach ($list as $k=>$v){
            $list[$k]['is_use'] = StoreCouponIssueUser::be(['uid'=>$this->userInfo['uid'],'issue_coupon_id'=>$v['id']]);
        }
        return JsonService::successful($list);
    }

    public function clear_cache($uni = '')
    {
        if($uni)CacheService::clear();
    }

    /**
     * 获取今天正在拼团的人的头像和名称
     * @return \think\response\Json
     */
    public function get_pink_second_one(){
        $addTime =  mt_rand(time()-30000,time());
        $storePink = StorePink::where('p.add_time','GT',$addTime)->alias('p')->where('p.status',1)->join('User u','u.uid=p.uid')->field('u.nickname,u.avatar as src')->find();
        return JsonService::successful($storePink);
    }

    /**
     * 再来一单
     * @param string $uni
     */
    public function order_details($uni = ''){

        if(!$uni) return JsonService::fail('参数错误!');
        $order = StoreOrder::getUserOrderDetail($this->userInfo['uid'],$uni);
        if(!$order) return JsonService::fail('订单不存在!');
        $order = StoreOrder::tidyOrder($order,true);
        $res = array();
        foreach ($order['cartInfo'] as $v) {
            if($v['combination_id']) return JsonService::fail('拼团产品不能再来一单，请在拼团产品内自行下单!');
            else  $res[] = StoreCart::setCart($this->userInfo['uid'], $v['product_id'], $v['cart_num'], isset($v['productInfo']['attrInfo']['unique']) ? $v['productInfo']['attrInfo']['unique'] : '', 'product', 0, 0);
        }
        $cateId = [];
        foreach ($res as $v){
            if(!$v) return JsonService::fail('再来一单失败，请重新下单!');
            $cateId[] = $v['id'];
        }
        return JsonService::successful('ok',implode(',',$cateId));

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
        $picname = $path.DS.$this->userInfo['uid'].'.jpg';
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
     * 获取砍价列表
     * @return \think\response\Json
     */
    public function get_bargain_list($limit = 10){
        $bargain = StoreBargain::getList();
        $bargain = StoreBargainUser::getUserList($bargain,$limit);
        $lovely = GroupDataService::getData('routine_lovely')?:[];//banner图
        $banner = GroupDataService::getData('bargain_banner')?:[];//banner图
        $banner = $banner[0];
        $bargainUser = StoreBargainUser::getBargainUserStatusSuccess();
        $data['bargain'] = $bargain;
        $data['lovely'] = $lovely[2];
        $data['banner'] = $banner;
        $data['bargainUser'] = $bargainUser;
        return JsonService::successful($data);
    }

    /**
     * 砍价详情
     * @param int $bargainId
     * @return \think\response\Json
     */
    public function get_bargain($bargainId = 0){
        if(!$bargainId) return JsonService::fail('参数错误');
        $bargain = StoreBargain::getBargainTerm($bargainId);
        if(empty($bargain)) return JsonService::fail('砍价已结束');
        $bargain['time'] = time();
        return JsonService::successful($bargain);
    }

    /**
     * 获取人数
     * @param int $count
     * @return \think\response\Json
     */
    public function get_bargain_count($count = 0){
        $data['lookCount'] = StoreBargain::getBargainLook()['look'];//观看人数
        $data['shareCount'] = StoreBargain::getBargainShare()['share'];//观看人数
        $data['userCount'] = StoreBargainUser::count();//参与人数
        return JsonService::successful($data);
    }

    /**
     * 添加砍价分享次数
     * @param int $bargainId
     */
    public function add_share_bargain($bargainId = 0){
        if(!$bargainId) return JsonService::successful();
        StoreBargain::addBargainShare($bargainId);
        return JsonService::successful();
    }

    /**
     * 添加砍价浏览次数
     * @param int $bargainId
     */
    public function add_look_bargain($bargainId = 0){
        if(!$bargainId) return JsonService::successful();
        StoreBargain::addBargainLook($bargainId);
        return JsonService::successful();
    }

    /**
     * 获取砍价帮
     * @param int $bargainId
     * @param int $uid
     * @return \think\response\Json
     */
    public function get_bargain_user($bargainId = 0,$bargainUid = 0,$limit = 15){
        if(!$bargainId || !$bargainUid) return JsonService::fail('参数错误');
        $bargainUserTableId = StoreBargainUser::setUserBargain($bargainId,$bargainUid);
        $storeBargainUserHelp = StoreBargainUserHelp::getList($bargainUserTableId,$limit);
        return JsonService::successful($storeBargainUserHelp);
    }
    /**
     * 我的砍价
     * @param int $bargainId
     * @return \think\response\Json
     */
    public function mycut($bargainId = 0){
        if(!$bargainId ) return JsonService::fail('参数错误');
        $data= StoreBargainUser::where('bargain_id',$bargainId)->where('uid',$this->userInfo['uid'])->where('status',1)->find();
        return JsonService::successful($data);
    }
    /**
     * 参与砍价产品
     * @param int $bargainId
     * @return \think\response\Json
     */
    public function set_bargain($bargainId = 0){
        if(!$bargainId) return JsonService::fail('参数错误');
        $res = StoreBargainUser::setBargain($bargainId,$this->userInfo['uid']);
        if($res) {
            $data['id'] =  $res->id;
            return JsonService::successful('参与成功',$data);
        }
        else return JsonService::fail('参与失败');
    }

    /**
     * 判断当前登录人是否参与砍价
     * @param int $bargainId
     * @return \think\response\Json
     */
    public function is_bargain_user($bargainId = 0){
        if(!$bargainId) return JsonService::fail('参数错误');
        $data=StoreBargainUser::isBargainUser($bargainId,$this->userInfo['uid']);
        if($data) return JsonService::successful($data);
        else return JsonService::fail('没有参与砍价');
    }
    /*
    * 已砍掉的金额
    */
    public function speed_of_progress($bargainId = 0){
        if(!$bargainId) return JsonService::fail('参数错误');
        $price= StoreBargainUser::where('bargain_id',$bargainId)->where('status',1)->where('uid',$this->userInfo['uid'])->value('price');
        return JsonService::successful($price);
    }
    /**
     * 帮好友砍价
     * @param int $bargainId
     * @param int $bargainUserId
     * @return \think\response\Json
     */
    public function set_bargain_help($bargainId = 0,$bargainUserId = 0){
        if(!$bargainId || !$bargainUserId) return JsonService::fail('参数错误');
        $res = StoreBargainUserHelp::setBargainUserHelp($bargainId,$bargainUserId,$this->userInfo['uid']);
        if($res) {
            if(!StoreBargainUserHelp::getSurplusPrice($bargainId,$bargainUserId)){
                $bargainUserTableId = StoreBargainUser::getBargainUserTableId($bargainId,$bargainUserId);
                $bargain = StoreBargain::where('id',$bargainId)->find()->toArray();
                $bargainUser = StoreBargainUser::where('id',$bargainUserTableId)->find()->toArray();
                RoutineTemplate::sendBargainSuccess($bargain,$bargainUser,$bargainUserId);//发送模板消息
            }
            return JsonService::successful('砍价成功',$res);
        }
        else return JsonService::fail('砍价失败');
    }


    /**
     * 获取砍价帮总人数、剩余金额、进度条
     * @param int $bargainId
     * @param int $bargainUserId
     * @return \think\response\Json
     */
    public function get_bargain_help_count($bargainId = 0,$bargainUserId = 0){
        if(!$bargainId || !$bargainUserId) return JsonService::fail('参数错误');
        $count = StoreBargainUserHelp::getBargainUserHelpPeopleCount($bargainId,$bargainUserId);
        $price = StoreBargainUserHelp::getSurplusPrice($bargainId,$bargainUserId);
        $pricePercent = StoreBargainUserHelp::getSurplusPricePercent($bargainId,$bargainUserId);
        $data['count'] = $count;
        $data['price'] = $price;
        $data['pricePercent'] = $pricePercent;
        return JsonService::successful($data);
    }

    /**
     * 判断当前砍价是否开启
     * @param int $bargainId
     * @return \think\response\Json
     */
    public function is_bargain_status($bargainId = 0){
        if(!$bargainId) return JsonService::fail('参数错误');
        if(!StoreBargain::setBargainStatus($bargainId)) return JsonService::successful();
        else return JsonService::fail();
    }


    /**
     * 判断用户是否可以砍价
     * @param int $bargainId
     * @param int $bargainUserId
     */
    public function is_bargain_user_help($bargainId = 0,$bargainUserId = 0){
        if(!$bargainId || !$bargainUserId) return JsonService::fail('参数错误');
        if(StoreBargainUserHelp::isBargainUserHelpCount($bargainId,$bargainUserId,$this->userInfo['uid'])) return JsonService::successful('请稍后在帮助好友砍价');
        else return JsonService::fail('您不能再帮忙砍价了');
    }

    /**
     * 修改砍价状态为失败
     * @param int $bargainUserTableId
     */
    public function set_user_bargain_status($bargainUserTableId = 0){
        if(!$bargainUserTableId) return JsonService::fail('参数错误');
        if(StoreBargainUser::editBargainUserStatus($bargainUserTableId)) return JsonService::successful('ok');
        else return JsonService::fail('no');
    }



    /**
     * 获取用户信息
     * @param int $uid
     * @return \think\response\Json
     */
    public function get_user_info_uid($userId = 0){
        if(!$userId) return JsonService::fail('参数错误');
        $res = User::getUserInfo($userId);
        if($res) return JsonService::successful($res);
        else return JsonService::fail(User::getErrorInfo());
    }

    /**
     * 获取砍价产品  个人中心 我的砍价
     */
    public function get_user_bargain_all(){
        $list = StoreBargainUser::getBargainUserAll($this->userInfo['uid']);
        if($list){
            foreach ($list as $k=>$v){
                $list[$k]['helpCount'] = StoreBargainUserHelp::getBargainUserHelpPeopleCount($v['bargain_id'],$this->userInfo['uid']);
            }
            return JsonService::successful($list);
        }else return JsonService::fail('暂无参与砍价');
    }
    /*
     * 查物流
     */
    public function express($uid,$uni = '')
    {
        if(!$uni || !($order = StoreOrder::getUserOrderDetail($uid,$uni))) return JsonService::fail('查询订单不存在!');
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

        if($result) return JsonService::successful([ 'order'=>$order, 'express'=>$result]);
    }

    /**
     * 收集发送模板信息的formID
     * @param string $formId
     */
    public function get_form_id($formId = ''){
        if((int)$formId == '' || $formId == 'the formId is a mock one') return JsonService::fail('no');
        $data['form_id'] = $formId;
        $data['uid'] = $this->userInfo['uid'];
        $data['status'] = 1;
        $data['stop_time'] = bcadd(time(),bcmul(6,86400,0),0);
        RoutineFormId::set($data);
        return JsonService::successful();
    }

    /**
     * 获取拼团列表
     * @param int $offset
     * @param int $limit
     */
    public function get_combination_list($offset=0,$limit=20){
        $store_combination = StoreCombination::getAll($offset,$limit);
        return JsonService::successful($store_combination);
    }

    /**
     * 获取拼团列表顶部图
     * @param int $offset
     * @param int $limit
     */
    public function get_combination_list_banner(){
        $lovely = GroupDataService::getData('routine_lovely')?:[];//banner图
        return JsonService::successful($lovely[3]);
    }

    /**
     * 获取拼团产品详情
     * @param int $id
     */
    public function combination_detail($id = 0){
        if(!$id) return JsonService::fail('拼团不存在或已下架');
        $combinationOne = StoreCombination::getCombinationOne($id);
        if(!$combinationOne) return JsonService::fail('拼团不存在或已下架');
        $combinationOne['images'] = json_decode($combinationOne['images'],true);
//        $combinationOne['userLike'] = StoreProductRelation::isProductRelation($combinationOne['product_id'],$this->userInfo['uid'],'like');
//        $combinationOne['like_num'] = StoreProductRelation::productRelationNum($combinationOne['product_id'],'like');
        $combinationOne['userCollect'] = StoreProductRelation::isProductRelation($id,$this->userInfo['uid'],'collect','pink_product');
        $pink = StorePink::getPinkAll($id);//拼团列表
        $pindAll = array();
        foreach ($pink as $k=>$v){
            $pink[$k]['count'] = StorePink::getPinkPeople($v['id'],$v['people']);
            $pink[$k]['h'] = date('H',$v['stop_time']);
            $pink[$k]['i'] = date('i',$v['stop_time']);
            $pink[$k]['s'] = date('s',$v['stop_time']);
            $pindAll[] = $v['id'];//开团团长ID
        }
        $user = WechatUser::get($this->userInfo['uid'])->toArray();//用户信息
        $data['pink'] = $pink;
        $data['user'] = $user;
        $data['pindAll'] = $pindAll;
        $data['storeInfo'] = $combinationOne;
        $data['reply'] = StoreProductReply::getRecProductReply($combinationOne['product_id']);
        $data['replyCount'] = StoreProductReply::productValidWhere()->where('product_id',$combinationOne['product_id'])->count();
//        $data['mer_id'] = StoreProduct::where('id',$combinationOne['product_id'])->value('mer_id');
        return JsonService::successful($data);
    }

    /**
     * 开团页面
     * @param int $id
     * @return mixed
     */
    public function get_pink($id = 0){
        if(!$id) return JsonService::fail('参数错误');
        $pink = StorePink::getPinkUserOne($id);
        if(isset($pink['is_refund']) && $pink['is_refund']) {
            if($pink['is_refund'] != $pink['id']){
                $id = $pink['is_refund'];
                return $this->get_pink($id);
            }else{
                return JsonService::fail('订单已退款');
            }
        }
        if(!$pink) return JsonService::fail('参数错误');
        $pinkAll = array();//参团人  不包括团长
        $pinkT = array();//团长
        if($pink['k_id']){
            $pinkAll = StorePink::getPinkMember($pink['k_id']);
            $pinkT = StorePink::getPinkUserOne($pink['k_id']);
        }else{
            $pinkAll = StorePink::getPinkMember($pink['id']);
            $pinkT = $pink;
        }
        $store_combination = StoreCombination::getCombinationOne($pink['cid']);//拼团产品
        $count = count($pinkAll)+1;
        $count = (int)$pinkT['people']-$count;//剩余多少人
        $is_ok = 0;//判断拼团是否完成
        $idAll =  array();
        $uidAll =  array();
        if(!empty($pinkAll)){
            foreach ($pinkAll as $k=>$v){
                $idAll[$k] = $v['id'];
                $uidAll[$k] = $v['uid'];
            }
        }

        $userBool = 0;//判断当前用户是否在团内  0未在 1在
        $pinkBool = 0;//判断当前用户是否在团内  0未在 1在
        $idAll[] = $pinkT['id'];
        $uidAll[] = $pinkT['uid'];
        if($pinkT['status'] == 2){
            $pinkBool = 1;
            $is_ok = 1;
        }else{
            if(!$count){//组团完成
                $is_ok = 1;
                $idAll = implode(',',$idAll);
                $orderPinkStatus = StorePink::setPinkStatus($idAll);
                if($orderPinkStatus){
                    if(in_array($this->userInfo['uid'],$uidAll)){
                        StorePink::setPinkStopTime($idAll);
                        if(StorePink::isTpl($uidAll,$pinkT['id'])) StorePink::orderPinkAfter($uidAll,$pinkT['id']);
                        $pinkBool = 1;
                    }else  $pinkBool = 3;
                }else $pinkBool = 6;
            }
            else{
                if($pinkT['stop_time'] < time()){//拼团时间超时  退款
                    if($pinkAll){
                        foreach ($pinkAll as $v){
                            if($v['uid'] == $this->userInfo['uid']){
                                $res = StoreOrder::orderApplyRefund(StoreOrder::where('id',$v['order_id_key'])->value('order_id'),$this->userInfo['uid'],'拼团时间超时');
                                if($res){
                                    if(StorePink::isTpl($v['uid'],$pinkT['id'])) StorePink::orderPinkAfterNo($v['uid'],$v['k_id']);
                                    $pinkBool = 2;
                                }else return JsonService::fail(StoreOrder::getErrorInfo());
                            }
                        }
                    }
                    if($pinkT['uid'] == $this->userInfo['uid']){
                        $res = StoreOrder::orderApplyRefund(StoreOrder::where('id',$pinkT['order_id_key'])->value('order_id'),$this->userInfo['uid'],'拼团时间超时');
                        if($res){
                            if(StorePink::isTpl($pinkT['uid'],$pinkT['id']))  StorePink::orderPinkAfterNo($pinkT['uid'],$pinkT['id']);
                            $pinkBool = 2;
                        }else return JsonService::fail(StoreOrder::getErrorInfo());
                    }
                    if(!$pinkBool) $pinkBool = 3;
                }
            }
        }
        $store_combination_host =  StoreCombination::getCombinationHost();//获取推荐的拼团产品
        if(!empty($pinkAll)){
            foreach ($pinkAll as $v){
                if($v['uid'] == $this->userInfo['uid']) $userBool = 1;
            }
        }
        if($pinkT['uid'] == $this->userInfo['uid']) $userBool = 1;
        $combinationOne = StoreCombination::getCombinationOne($pink['cid']);
        if(!$combinationOne) return JsonService::fail('拼团不存在或已下架');
        $store_combination['userInfo'] = $this->userInfo;
        $data['pinkBool'] = $pinkBool;
        $data['is_ok'] = $is_ok;
        $data['userBool'] = $userBool;
        $data['store_combination'] = $store_combination;
        $data['pinkT'] = $pinkT;
        $data['pinkAll'] = $pinkAll;
        $data['count'] = $count;
        $data['store_combination_host'] = $store_combination_host;
        $data['current_pink_order'] = StorePink::getCurrentPink($id);
        return JsonService::successful($data);
    }

    /**
     * 购物车库存修改
     * @param int $cartId
     * @param int $cartNum
     */
    public function set_buy_cart_num($cartId = 0,$cartNum = 0){
        if(!$cartId) return JsonService::fail('参数错误');
        $res = StoreCart::edit(['cart_num'=>$cartNum],$cartId);
        if($res) return JsonService::successful();
        else return JsonService::fail('修改失败');
    }

    /**
     * 获取后台联系方式
     */
    public function get_site_phone(){
        $data =  SystemConfig::getValue('site_service_phone');
        return JsonService::successful($data);
    }

    /**
     * 获取产品链接的二维码
     * @param string $path
     * @param int $width
     */
//    public function get_pages($path = '',$productId = 0,$width = 430){
//        if($path == '' || !$productId) return JsonService::fail('参数错误'); header('content-type:image/jpg');
//        if(!$this->userInfo['uid']) return JsonService::fail('授权失败，请重新授权');
//        $path = 'public/uploads/routinepage/'.$productId.'.jpg';
//        if(file_exists($path)) return JsonService::successful($path);
//        else file_put_contents($path,RoutineCode::getCode($this->userInfo['uid']));
//        return JsonService::successful($path);
//    }

    /**
     * 文章列表
     * @param int $cid
     * @param int $first
     * @param int $limit
     */
    public function get_cid_article($cid = 0,$first = 0,$limit = 8){
        $list = ArticleModel::cidByArticleList($cid,$first,$limit,'id,title,image_input,visit,add_time,synopsis,url')?:[];
        foreach ($list as &$article){
            $article['add_time'] = date('Y-m-d H:i',$article['add_time']);
        }
        $data['list'] = $list;
        return JsonService::successful($list);
    }

    /**
     * 获取热门文章
     */
    public function get_article_hot(){
        $hot = ArticleModel::getArticleListHot('id,title');
        return JsonService::successful($hot);
    }

    /**
     * 获取热门banner文章
     */
    public function get_article_banner(){
        $banner = ArticleModel::getArticleListBanner('id,title,image_input');
        return JsonService::successful($banner);
    }

    /**
     * 获取文章详情
     * @param int $id
     */
    public function visit($id = 0)
    {
        $content = ArticleModel::getArticleOne($id);
        if(!$content || !$content["status"]) return JsonService::fail('此文章已经不存在!');
        $content["visit"] = $content["visit"] + 1;
        $content['add_time'] = date('Y-m-d H:i:s',$content['add_time']);
        ArticleModel::edit(['visit'=>$content["visit"]],$id);//增加浏览次数
        return JsonService::successful($content);
    }

    /**
     * 产品海报二维码
     * @param int $id
     */
    public function product_promotion_code($id = 0){
        if(!$id) return JsonService::fail('参数错误');
        $count = StoreProduct::validWhere()->count();
        if(!$count) return JsonService::fail('参数错误');
        $path = 'public'.DS.'uploads'.DS.'codepath'.DS.'product';
        $codePath = $path.DS.$id.'_'.$this->userInfo['uid'].'.jpg';
        $domain = SystemConfigService::get('site_url').'/';
        if(!file_exists($codePath)){
            if(!is_dir($path)) mkdir($path,0777,true);
            $res = RoutineCode::getCode($this->userInfo['uid'],$codePath,[],'/pages/product-con/index?id='.$id,'product_spread');
            if($res) file_put_contents($codePath,$res);
            else return JsonService::fail('二维码生成失败');
        }
        return JsonService::successful($domain.$codePath);
    }


    public function poster($id = 0){
        if(!$id) return JsonService::fail('参数错误');
        $productInfo = StoreProduct::getValidProduct($id,'store_name,id,price,image,code_path');
        if(empty($productInfo)) return JsonService::fail('参数错误');
        if(strlen($productInfo['code_path'])< 10) {
            $path = 'public'.DS.'uploads'.DS.'codepath'.DS.'product';
            $codePath = $path.DS.$productInfo['id'].'.jpg';
            if(!file_exists($codePath)){
                if(!is_dir($path)) mkdir($path,0777,true);
                $res = file_put_contents($codePath,RoutineCode::getPages('pages/product-con/index?id='.$productInfo['id']));
            }
            $res = StoreProduct::edit(['code_path'=>$codePath],$id);
            if($res) $productInfo['code_path'] = $codePath;
            else return JsonService::fail('没有查看权限');
        }
        $posterPath = createPoster($productInfo);
        return JsonService::successful($posterPath);

//        if(!$id) return JsonService::fail('参数错误');
//        $productInfo = StoreProduct::getValidProduct($id,'store_name,id,price,image,code_path');
//        if(empty($productInfo)) return JsonService::fail('参数错误');
//        if($productInfo['code_path'] == '') {
//            $path = 'public'.DS.'uploads'.DS.'codepath'.DS.'product';
//            $codePath = $path.DS.$productInfo['id'].'.jpg';
//            if(!file_exists($codePath)){
//                //$dir = iconv("UTF-8", "GBK", "public".DS."uploads".DS."codepath".DS."product");
//                if(!is_dir($path))
//                    mkdir($path,0777,true);
//                file_put_contents($codePath,RoutineCode::getPages('pages/product-con/index?id='.$productInfo['id']));
//            }
//            $res = StoreProduct::edit(['code_path'=>$codePath],$id);
//            if($res) $productInfo['code_path'] = $codePath;
//            else return JsonService::fail('没有查看权限');
//        }
//        $posterPath = createPoster($productInfo);
//        return JsonService::successful($posterPath);
    }


    /**
     * 刷新数据缓存
     */
    public function refresh_cache(){
        `php think optimize:schema`;
        `php think optimize:autoload`;
        `php think optimize:route`;
        `php think optimize:config`;
    }



}