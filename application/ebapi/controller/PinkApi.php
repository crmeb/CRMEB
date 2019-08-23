<?php
namespace app\ebapi\controller;

use app\admin\model\system\SystemAttachment;
use app\core\model\routine\RoutineCode;
use app\core\util\SystemConfigService;
use app\ebapi\model\store\StoreCombination;
use app\ebapi\model\store\StoreOrder;
use app\ebapi\model\store\StorePink;
use app\ebapi\model\store\StoreProductRelation;
use app\ebapi\model\store\StoreProductReply;
use app\ebapi\model\user\WechatUser;
use app\core\util\GroupDataService;
use service\JsonService;
use service\UploadService;
use service\UtilService;


/**
 * TODO 小程序拼团产品和拼团其他api接口
 * Class PinkApi
 * @package app\ebapi\controller
 */
class PinkApi extends AuthController
{
    /**
     * TODO 获取拼团列表
     */
    public function get_combination_list(){
        $data = UtilService::postMore([['offset',0],['limit',20]]);
        $store_combination = StoreCombination::getAll($data['offset'],$data['limit']);
        return JsonService::successful($store_combination);
    }

    /**
     * TODO 获取拼团列表顶部图
     */
    public function get_combination_list_banner(){
        return JsonService::successful();
    }

    /**
     * TODO 获取拼团产品详情
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function combination_detail(){
        list($id) = UtilService::postMore([['id',0]],null,true);
        if(!$id) return JsonService::fail('拼团不存在或已下架');
        $combinationOne = StoreCombination::getCombinationOne($id);
        if(!$combinationOne) return JsonService::fail('拼团不存在或已下架');
        $combinationOne['images'] = json_decode($combinationOne['images'],true);
        $combinationOne['userCollect'] = StoreProductRelation::isProductRelation($id,$this->userInfo['uid'],'collect','pink_product');
        list($pink ,$pindAll)= StorePink::getPinkAll($id,true);//拼团列表
        $data['pink'] = $pink;
        $data['user'] = $this->userInfo;//用户信息
        $data['pindAll'] = $pindAll;
        $data['storeInfo'] = $combinationOne;
        $data['pink_ok_list']=StorePink::getPinkOkList($this->uid);
        $data['pink_ok_sum']=StorePink::getPinkOkSumTotalNum($id);
        $data['reply'] = StoreProductReply::getRecProductReply($combinationOne['product_id']);
        $data['replyCount'] = StoreProductReply::productValidWhere()->where('product_id',$combinationOne['product_id'])->count();
        if($data['replyCount']){
            $goodReply=StoreProductReply::productValidWhere()->where('product_id',$combinationOne['product_id'])->where('product_score',5)->count();
            $data['replyChance']=bcdiv($goodReply,$data['replyCount'],2);
            $data['replyChance']=bcmul($data['replyChance'],100,3);
        }else $data['replyChance']=0;
        return JsonService::successful($data);
    }

    /**
     * 开团页面
     * @param int $id
     * @return mixed
     */
    public function get_pink($id = 0){
        $is_ok = 0;//判断拼团是否完成
        $userBool = 0;//判断当前用户是否在团内  0未在 1在
        $pinkBool = 0;//判断当前用户是否在团内  0未在 1在
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
        list($pinkAll,$pinkT,$count,$idAll,$uidAll)=StorePink::getPinkMemberAndPinkK($pink);
        if($pinkT['status'] == 2){
            $pinkBool = 1;
            $is_ok = 1;
        }else{
            if(!$count){//组团完成
                $is_ok = 1;
                $pinkBool=StorePink::PinkComplete($uidAll,$idAll,$this->userInfo['uid'],$pinkT);
            }else{
                $pinkBool=StorePink::PinkFail($pinkAll,$pinkT,$pinkBool);
            }
        }
        if(!empty($pinkAll)){
            foreach ($pinkAll as $v){
                if($v['uid'] == $this->userInfo['uid']) $userBool = 1;
            }
        }
        if($pinkT['uid'] == $this->userInfo['uid']) $userBool = 1;
        $combinationOne = StoreCombination::getCombinationOne($pink['cid']);
        if(!$combinationOne) return JsonService::fail('拼团不存在或已下架');
        $data['userInfo'] = $this->userInfo;
        $data['pinkBool'] = $pinkBool;
        $data['is_ok'] = $is_ok;
        $data['userBool'] = $userBool;
        $data['store_combination'] =$combinationOne;
        $data['pinkT'] = $pinkT;
        $data['pinkAll'] = $pinkAll;
        $data['count'] = $count;
        $data['store_combination_host'] = StoreCombination::getCombinationHost();
        $data['current_pink_order'] = StorePink::getCurrentPink($id,$this->uid);
        return JsonService::successful($data);
    }

    /**
     * 获取今天正在拼团的人的头像和名称
     * @return \think\response\Json
     */
    public function get_pink_second_one()
    {
        return JsonService::successful(StorePink::getPinkSecondOne());
    }

    /*
     * 取消开团
     * @param int $pink_id 团长id
     * */
    public function remove_pink($pink_id=0,$cid=0,$formId='')
    {
        if(!$pink_id || !$cid) return JsonService::fail('缺少参数');
        $res=StorePink::removePink($this->uid,$cid,$pink_id,$formId);
        if($res)
            return JsonService::successful('取消成功');
        else{
            $error=StorePink::getErrorInfo();
            if(is_array($error))
                return JsonService::status($error['status'],$error['msg']);
            else
                return JsonService::fail($error);
        }
    }

    /**
     * TODO 生成海报
     */
    public function pink_share_poster()
    {
        list($pinkId) = UtilService::postMore([['id',0]],null,true);
        $pinkInfo = StorePink::getPinkUserOne($pinkId);
        $storeCombinationInfo = StoreCombination::getCombinationOne($pinkInfo['cid']);
        $data['title'] = $storeCombinationInfo['title'];
        if(stripos($storeCombinationInfo['image'], '/public/uploads/'))
            $data['image'] = ROOT_PATH.substr($storeCombinationInfo['image'],stripos($storeCombinationInfo['image'], '/public/uploads/'),strlen($storeCombinationInfo['image']));
        else
            $data['image'] = $storeCombinationInfo['image'];
        $data['price'] = $pinkInfo['total_price'];
        $data['label'] = $pinkInfo['people'].'人团';
        if($pinkInfo['k_id']) $pinkAll = StorePink::getPinkMember($pinkInfo['k_id']);
        else $pinkAll = StorePink::getPinkMember($pinkInfo['id']);
        $count = count($pinkAll)+1;
        $data['msg'] = '原价￥'.$storeCombinationInfo['product_price'].' 还差'.(int)bcsub((int)$pinkInfo['people'],$count,0).'人拼团成功';
        try{
            $name = $pinkId.'_'.$this->userInfo['uid'].'_'.$this->userInfo['is_promoter'].'_pink_share.jpg';
            $imageInfo = SystemAttachment::getInfo($name,'name');
            $siteUrl = SystemConfigService::get('site_url').DS;
            if(!$imageInfo){
                $valueData = 'id='.$pinkId.'&pid='.$this->uid;
//                if($this->userInfo['is_promoter'] || SystemConfigService::get('store_brokerage_statu')==2) $valueData.='&pid='.$this->uid;
                $res = RoutineCode::getPageCode('pages/activity/goods_combination_status/index',$valueData,280);
                if(!$res) return JsonService::fail('二维码生成失败');
                $imageInfo = UploadService::imageStream($name,$res,'routine/activity/pink/code');
                if(!is_array($imageInfo)) return JsonService::fail($imageInfo);
                if($imageInfo['image_type'] == 1) $remoteImage = UtilService::remoteImage($siteUrl.$imageInfo['dir']);
                else $remoteImage = UtilService::remoteImage($imageInfo['dir']);
                if(!$remoteImage['status']) return JsonService::fail($remoteImage['msg']);
                SystemAttachment::attachmentAdd($imageInfo['name'],$imageInfo['size'],$imageInfo['type'],$imageInfo['dir'],$imageInfo['thumb_path'],1,$imageInfo['image_type'],$imageInfo['time'],2);
                $url = $imageInfo['dir'];
            }else $url = $imageInfo['att_dir'];
            if($imageInfo['image_type'] == 1)
                $data['url'] = str_replace('//','/',ROOT_PATH.$url);
            else
                $data['url'] = $url;
            $posterImage = UtilService::setShareMarketingPoster($data,'routine/activity/pink/poster');
            if(!is_array($posterImage)) return JsonService::fail('海报生成失败');
            SystemAttachment::attachmentAdd($posterImage['name'],$posterImage['size'],$posterImage['type'],$posterImage['dir'],$posterImage['thumb_path'],1,$posterImage['image_type'],$posterImage['time'],2);
            if($posterImage['image_type'] == 1) $posterImage['dir'] = $siteUrl.$posterImage['dir'];
            return JsonService::successful('ok',$posterImage['dir']);
        }catch (\Exception $e){
            return JsonService::fail('系统错误：生成图片失败',['line'=>$e->getLine(),'message'=>$e->getMessage()]);
        }

    }


}