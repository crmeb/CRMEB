<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/12
 */

namespace app\wap\controller;


use app\admin\model\system\SystemConfig;
use app\wap\model\store\StoreBargain;
use app\wap\model\store\StoreBargainUser;
use app\wap\model\store\StoreBargainUserHelp;
use app\wap\model\store\StoreCategory;
use app\wap\model\store\StoreCoupon;
use app\wap\model\store\StoreSeckill;
use app\wap\model\store\StoreCouponIssue;
use app\wap\model\store\StoreCouponIssueUser;
use app\wap\model\store\StoreCouponUser;
use app\wap\model\store\StorePink;
use app\wap\model\store\StoreProductReply;
use app\wap\model\store\StoreCart;
use app\wap\model\store\StoreOrder;
use app\wap\model\store\StoreProduct;
use app\wap\model\store\StoreProductAttr;
use app\wap\model\store\StoreProductRelation;
use app\wap\model\user\User;
use app\wap\model\user\WechatUser;
use app\wap\model\store\StoreCombination;
use service\GroupDataService;
use service\SystemConfigService;
use service\UtilService;
use think\Cache;
use think\Request;
use think\Url;
use service\JsonService;

class Store extends AuthController
{

    public function index($keyword = '',$cid = '',$sid = '')
    {
        if($keyword != '') $keyword = base64_decode($keyword);
        $keyword = addslashes($keyword);
        $cid = intval($cid);
        $sid = intval($sid);
        $category = null;
        if($sid)
            $category = StoreCategory::get($sid);
        if($cid && !$category)
            $category = StoreCategory::get($cid);
        $this->assign(compact('keyword','cid','sid','category'));
        return $this->fetch();
    }

    public function category()
    {
        $_category = StoreCategory::all(function($query){
            $query->field('id,pid,cate_name')->where('is_show',1)->order('sort DESC');
        })->toArray();
        $category = [];
        foreach ($_category as $c){
            if(!$c['pid']){
                $c['child'] = [];
                $category[$c['id']] = $c;
            }
        }
        foreach ($_category as $c){
            if($c['pid']){
                $category[$c['pid']]['child'][] = $c;
            }
        }
        foreach ($category as $key => $value) {
           if(!isset($value['id'])){
                unset($category[$key]);
           }
        }
        $this->assign(compact('category'));
        return $this->fetch();
    }

    public function issue_coupon()
    {
        $list = $list = StoreCouponIssue::validWhere('A')->join('__STORE_COUPON__ B','A.cid = B.id')
            ->field('A.*,B.coupon_price,B.use_min_price')->order('B.sort DESC,A.id DESC')->select()->toArray();
        $sort = [];
        $lists= [];
        foreach ($list as &$v){
            $v['add_time'] = date('Y/m/d',$v['add_time']);
            $v['end_time'] = date('Y/m/d',$v['end_time']);
            if((int)$v['is_permanent']==0 && $v['remain_count'] >0){
                $sort[] = $v['is_get'] = StoreCouponIssueUser::be(['uid'=>$this->userInfo['uid'],'issue_coupon_id'=>$v['id']]) ? 1:0;
                array_push($lists,$v);
            }
        }
        array_multisort($sort,SORT_ASC,SORT_NUMERIC,$lists);
        $this->assign(compact('lists'));
        return $this->fetch();
    }

    public function detail($id = 0)
    {
        if(!$id || !($storeInfo = StoreProduct::getValidProduct($id))) return $this->failed('商品不存在或已下架!');
        $storeInfo['userLike'] = StoreProductRelation::isProductRelation($id,$this->userInfo['uid'],'like');
        $storeInfo['like_num'] = StoreProductRelation::productRelationNum($id,'like');
        $storeInfo['userCollect'] = StoreProductRelation::isProductRelation($id,$this->userInfo['uid'],'collect');
        list($productAttr,$productValue) = StoreProductAttr::getProductAttrDetail($id);
        setView($this->userInfo['uid'],$id,$storeInfo['cate_id'],'viwe');
        $urlShare = SystemConfigService::get('site_url').Url::build('wap/Store/detail',['id'=>$id,'spuid'=>$this->uid]);
        $this->assign([
            'urlShare'=>$urlShare,
            'storeInfo'=>$storeInfo,
            'similarity'=>StoreProduct::cateIdBySimilarityProduct($storeInfo['cate_id'],'id,store_name,image,price,sales,ficti',4),
            'productAttr'=>$productAttr,
            'productValue'=>$productValue,
            'reply'=>StoreProductReply::getRecProductReply($storeInfo['id']),
            'replyCount'=>StoreProductReply::productValidWhere()->where('product_id',$storeInfo['id'])->count(),
            'mer_id' => StoreProduct::where('id',$storeInfo['id'])->value('mer_id')
        ]);
        return $this->fetch();
    }

    public function reply_list($productId = '')
    {
        if(!$productId || !is_numeric($productId)) return $this->failed('商品不存在!');
        $this->assign([
            'productId'=>$productId,
            'replyCount'=>StoreProductReply::productValidWhere()->where('product_id',$productId)->count(),
            'picReplyCount'=>StoreProductReply::productValidWhere()->where('product_id',$productId)->where('pics',['<>',''],['<>','[]'])->count()
        ]);
        return $this->fetch();
    }

    public function cart()
    {
        return $this->fetch();
    }

    public function confirm_order($cartId = '')
    {
        if(!is_string($cartId) || !$cartId ){
            return $this->failed('请提交购买的商品!');
        }
        $cartGroup = StoreCart::getUserProductCartList($this->userInfo['uid'],$cartId,1);

        if(count($cartGroup['invalid']))
            return $this->failed($cartGroup['invalid'][0]['productInfo']['store_name'].'已失效!');
        if(!$cartGroup['valid']) return $this->failed('请提交购买的商品!');
        $cartInfo = $cartGroup['valid'];
        $priceGroup = StoreOrder::getOrderPriceGroup($cartInfo);
        $other = [
            'offlinePostage'=>SystemConfigService::get('offline_postage'),
            'integralRatio'=>SystemConfigService::get('integral_ratio')
        ];

        $usableCoupon = StoreCouponUser::beUsableCoupon($this->userInfo['uid'],$priceGroup['totalPrice']);
        $cartIdA = explode(',',$cartId);
        $seckill_id=0;
        $bargain_id=0;
        if(count($cartIdA) == 1){
            $cartInfoMark = StoreCart::where('id',$cartId)->find();
            if((int)$cartInfoMark['seckill_id']>0) $seckill_id = $cartInfoMark['seckill_id'];
            else if((int)$cartInfoMark['bargain_id']>0)   $bargain_id = $cartInfoMark['bargain_id'];
            else $seckill_id=0;
        }
        $this->assign([
            'usableCoupon'=>$usableCoupon,
            'seckill_id'=>$seckill_id,
            'bargain_id'=>$bargain_id,
            'cartInfo'=>$cartInfo,
            'priceGroup'=>$priceGroup,
            'orderKey'=>StoreOrder::cacheOrderInfo($this->userInfo['uid'],$cartInfo,$priceGroup,$other),
            'offlinePostage'=>$other['offlinePostage'],
            'userInfo'=>User::getUserInfo($this->userInfo['uid']),
            'integralRatio'=>$other['integralRatio']
        ]);

        return $this->fetch();
    }

    public function combination(){
        $where = array();
        $store_combination = StoreCombination::getAll(0,20);
        $this->assign('list',$store_combination);
        return $this->fetch();
    }
    //获取列表
    public function get_list(){
        return JsonService::successful(StoreCombination::get_list(20));
    }

    public function combination_detail($id = 0){
        if(!$id) return $this->failed('拼团不存在或已下架');
        $combinationOne = StoreCombination::getCombinationOne($id);
        if(!$combinationOne) return $this->failed('拼团不存在或已下架');
        $combinationOne['images'] = json_decode($combinationOne['images'],true);
        $combinationOne['userLike'] = StoreProductRelation::isProductRelation($combinationOne['product_id'],$this->userInfo['uid'],'like');
        $combinationOne['like_num'] = StoreProductRelation::productRelationNum($combinationOne['product_id'],'like');
        $combinationOne['userCollect'] = StoreProductRelation::isProductRelation($combinationOne['product_id'],$this->userInfo['uid'],'collect');
        $pink = StorePink::getPinkAll($id);//拼团列表
        setView($this->userInfo['uid'],$id,$combinationOne['cate_id'],'viwe','combination');
        StoreCombination::editIncBrowse($id);
        $pindAll = array();
        foreach ($pink as $k=>$v){
            $pink[$k]['count'] = StorePink::getPinkPeople($v['id'],$v['people']);
            $pink[$k]['h'] = date('H',$v['stop_time']);
            $pink[$k]['i'] = date('i',$v['stop_time']);
            $pink[$k]['s'] = date('s',$v['stop_time']);
            $pindAll[] = $v['id'];//开团团长ID
        }
        $user = WechatUser::get($this->uid)->toArray();//用户信息
        $site_name = SystemConfig::getValue('site_name');//网站名称
        $site_logo = SystemConfig::getValue('site_logo');//网站LOGO
        $wechat_qrcode = SystemConfig::getValue('wechat_qrcode');//公众号二维码
        $this->assign([
            'pink'=>$pink,
            'user'=>$user,
            'site_name'=>$site_name,
            'site_logo'=>$site_logo,
            'wechat_qrcode'=>$wechat_qrcode,
            'pindAll'=>$pindAll,
            'storeInfo'=>$combinationOne,
            'reply'=>StoreProductReply::getRecProductReply($combinationOne['product_id']),
            'replyCount'=>StoreProductReply::productValidWhere()->where('product_id',$combinationOne['product_id'])->count(),
            'mer_id' => StoreProduct::where('id',$combinationOne['product_id'])->value('mer_id')
        ]);

        return $this->fetch();
    }

    public function combination_order(Request $request){
        $data = UtilService::getMore([
            ['cartId',0],
            ['pinkId',0],
        ],$request);

        if($data['pinkId']) if(StorePink::getIsPinkUid($data['pinkId'])) return $this->redirect(Url::build('my/order',['uni'=>StoreOrder::getStoreIdPink($data['pinkId'])]));
        if($data['pinkId']) if(StoreOrder::getIsOrderPink($data['pinkId'])) return $this->redirect(Url::build('my/order',['uni'=>StoreOrder::getStoreIdPink($data['pinkId'])]));;
        $cartId = $data['cartId'];
        $pinkId = $data['pinkId'];
        if(!is_string($cartId) || !$cartId )
            return $this->failed('请提交购买的商品!');
        $cartGroup = StoreCart::getUserCombinationProductCartList($this->userInfo['uid'],$cartId);
        if(count($cartGroup['invalid']))
            return $this->failed($cartGroup['invalid'][0]['productInfo']['store_name'].'已失效!');
        if(!$cartGroup['valid']) return $this->failed('请提交购买的商品!');
        $cartInfo = $cartGroup['valid'];
        foreach ($cartInfo as $k=>$cart){
            if($cart['combination_id']){
                $cartInfo[$k]['productInfo']['price'] = StoreCombination::where('id',$cart['combination_id'])->value('price');
                $cartInfo[$k]['productInfo']['image'] = StoreCombination::where('id',$cart['combination_id'])->value('image');
                $cartInfo[$k]['productInfo']['slider_image'] = json_decode(StoreCombination::where('id',$cart['combination_id'])->value('images'),true);
            }
        }
        $priceGroup = StoreOrder::getCombinationOrderPriceGroup($cartInfo);
        $other = [
            'offlinePostage'=>SystemConfigService::get('offline_postage'),
            'integralRatio'=>SystemConfigService::get('integral_ratio')
        ];
        $this->assign([
            'cartInfo'=>$cartInfo,
            'priceGroup'=>$priceGroup,
            'orderKey'=>StoreOrder::cacheOrderInfo($this->userInfo['uid'],$cartInfo,$priceGroup,$other),
            'offlinePostage'=>$other['offlinePostage'],
            'userInfo'=>User::getUserInfo($this->userInfo['uid']),
            'integralRatio'=>$other['integralRatio'],
            'pinkId'=>$pinkId
        ]);

        return $this->fetch();
    }
    /**
     * 秒杀列表页
     *
     */
    public function seckill_index(){
        $storeSeckill=StoreSeckill::where('is_del',0)->where('status',1)->where('start_time','<',time())->where('stop_time','>',time())->order('sort desc')->select()->toArray();

        $this->assign('seckillProduct',$storeSeckill);

        return $this->fetch();
    }
    /**
     * 秒杀详情页
     * @param string $id
     */
    public function seckill_detail($id = ''){

        if(!$id || !($storeInfo = StoreSeckill::getValidProduct($id))) return $this->failed('商品不存在或已下架!');
        $storeInfo['userLike'] = StoreProductRelation::isProductRelation($storeInfo['product_id'],$this->userInfo['uid'],'like','product_seckill');

        $storeInfo['like_num'] = StoreProductRelation::productRelationNum($storeInfo['product_id'],'like','product_seckill');

        $storeInfo['userCollect'] = StoreProductRelation::isProductRelation($storeInfo['product_id'],$this->userInfo['uid'],'collect','product_seckill');
        list($productAttr,$productValue) = StoreProductAttr::getProductAttrDetail($storeInfo['product_id']);
        $wechatInfo = WechatUser::get($this->userInfo['uid']);
        setView($this->userInfo['uid'],$id,$storeInfo['product_id'],'viwe','seckill');
        //是否关注
        if(!$wechatInfo) $isFollow = 0;
        else $isFollow = $wechatInfo['subscribe'];
        $this->assign([
            'storeInfo'=>$storeInfo,
//            'similarity'=>StoreProduct::cateIdBySimilarityProduct($storeInfo['cate_id'],'id,store_name,image,price,sales',4),
            'productAttr'=>$productAttr,
            'productValue'=>$productValue,
            'reply'=>StoreProductReply::getRecProductReply($storeInfo['product_id']),
            'replyCount'=>StoreProductReply::productValidWhere()->where('product_id',$storeInfo['product_id'])->count(),
            'mer_id' => 0,
            'merchant'=>null,
            'site'=>SystemConfigService::more(['wechat_qrcode','wechat_name','wechat_avatar']),
            'isFollow'=>$isFollow
        ]);
        return $this->fetch();
    }

    public function cut_list(){
        $bargain = StoreBargain::getList();
        $bargain = StoreBargainUser::getUserList($bargain);
        $bargainUser = StoreBargainUser::getBargainUserStatusSuccess();
        $this->assign([
            'bargain'=>$bargain,
            'bargainUser'=>$bargainUser,
        ]);
        return $this->fetch();
    }
    public function cut_con($id = 0,$bargainUid = 0){
       if(!$id) return $this->failed('参数错误');
        //砍价产品
        $bargain = StoreBargain::getBargainTerm($id);
        $bargain['time'] = time();
        $description = htmlspecialchars_decode($bargain['description']);
        $rule = isset($bargain['rule']) ? htmlspecialchars_decode($bargain['rule']) : '';
        if(!$bargainUid)
            //判断当前登录人是不是砍价
            if(!StoreBargainUser::isBargainUser($id,$this->userInfo['uid']))
                // 参与砍价
                if(!StoreBargainUser::setBargain($id,$this->userInfo['uid'])) return $this->failed('参与失败,请重新参与砍价',Url::build('store/bargain'));
        //顶部人数
        StoreBargain::addBargainLook($id);
        $lookCount = StoreBargain::getBargainLook()['look'];//观看人数
        $shareCount = StoreBargain::getBargainShare()['share'];//观看人数
        //砍价
        $selfCut = 0;
        if(!$bargainUid){
            $res = StoreBargainUserHelp::setBargainUserHelp($id,$bargainUid ? $bargainUid : $this->userInfo['uid'],$this->userInfo['uid']);
            if($res) {
                $selfCut = 1;
                if(!StoreBargainUserHelp::getSurplusPrice($id,$bargainUid ? $bargainUid : $this->userInfo['uid'])){
                    $bargainUserTableId = StoreBargainUser::getBargainUserTableId($id,$bargainUid ? $bargainUid : $this->userInfo['uid']);
                    $bargain = StoreBargain::where('id',$id)->find()->toArray();
                    $bargainUser = StoreBargainUser::where('id',$bargainUserTableId)->find()->toArray();
                }
            }
            $userInfoBargain = $this->userInfo;
        }else $userInfoBargain = User::getUserInfo($bargainUid);
        //砍价帮
        $bargainUserTableId = StoreBargainUser::setUserBargain($id,$bargainUid ? $bargainUid : $this->userInfo['uid']);
        $storeBargainUserHelp = StoreBargainUserHelp::getList($bargainUserTableId,15);
        //获取砍价帮总人数
        $count = StoreBargainUserHelp::getBargainUserHelpPeopleCount($id,$bargainUid ? $bargainUid : $this->userInfo['uid']);
        //获取用户还剩余的砍价金额
        $price = StoreBargainUserHelp::getSurplusPrice($id,$bargainUid ? $bargainUid : $this->userInfo['uid']);
        //获取砍价进度条
        $pricePercent = StoreBargainUserHelp::getSurplusPricePercent($id,$bargainUid ? $bargainUid : $this->userInfo['uid']);
        $selfCutPrice = bcsub(bcsub($bargain['price'],$price,2),$bargain['min_price'],2);
        //判断当前登录人是否砍价   1 微砍价  2 已砍价
        $userInfoBargainBool = StoreBargainUserHelp::isBargainUserHelpCount($id,$bargainUid,$this->userInfo['uid']) ? 1 : 2;
        $this->assign([
            'userInfoBargainBool'=>$userInfoBargainBool,
            'selfCut'=>$selfCut,
            'userInfoBargain'=>$userInfoBargain,
            'selfCutPrice'=>$selfCutPrice,
            'bargain'=>$bargain,
            'description'=>$description,
            'rule'=>$rule,
            'shareCount'=>$shareCount,
            'lookCount'=>$lookCount,
            'userCount'=>StoreBargainUser::count(),
            'userHelpList'=>$storeBargainUserHelp,
            'count'=>$count,
            'price'=>$price,
            'pricePercent'=>$pricePercent,
            'bargainUid'=>$bargainUid,
        ]);
       return $this->fetch();
    }
}