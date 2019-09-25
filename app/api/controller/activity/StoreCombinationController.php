<?php
namespace app\api\controller\activity;

use app\admin\model\system\SystemAttachment;
use app\models\routine\RoutineCode;
use app\models\store\StoreCombination;
use app\models\store\StorePink;
use app\models\store\StoreProductReply;
use app\Request;
use crmeb\services\SystemConfigService;
use crmeb\services\UploadService;
use crmeb\services\UtilService;

/**
 * 拼团类
 * Class StoreCombinationController
 * @package app\api\controller\activity
 */
class StoreCombinationController
{

    /**
     * 拼团列表
     * @param Request $request
     * @return mixed
     */
    public function lst(Request $request)
    {
        list($page, $limit) = UtilService::getMore([
            ['page',0],
            ['limit',0],
        ],$request, true);
        $combinationList = StoreCombination::getAll($page, $limit);
        if(!count($combinationList)) return app('json')->fail('暂无拼团');
        return app('json')->successful($combinationList->hidden(['info','product_id','images','mer_id','attr','sort','stock','sales','add_time','is_del','is_show','browse','cost','is_show','start_time','stop_time','description','postage','is_postage','is_host','mer_use','combination'])->toArray());
    }


    /**
     * 拼团产品详情
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function detail(Request $request, $id)
    {
        if(!$id || !($combinationOne = StoreCombination::getCombinationOne($id))) return app('json')->fail('拼团不存在或已下架');
        $combinationOne = $combinationOne->hidden(['mer_id','attr','sort','add_time','is_host','is_show','is_del','mer_use','cost','combination'])->toArray();
        $combinationOne['images'] = json_decode($combinationOne['images'],true);
        list($pink ,$pindAll)= StorePink::getPinkAll($id,true);//拼团列表
        //公众号
        $name = $id.'_combination_detail_wap.jpg';
        $imageInfo = SystemAttachment::getInfo($name,'name');
        $siteUrl = SystemConfigService::get('site_url');
        if(!$imageInfo){
            $codeUrl = UtilService::setHttpType($siteUrl.'/activity/group_detail/'.$id, 1);//二维码链接
            $imageInfo = UtilService::getQRCodePath($codeUrl, $name);
            if(!$imageInfo) return app('json')->fail('二维码生成失败');
            SystemAttachment::attachmentAdd($imageInfo['name'],$imageInfo['size'],$imageInfo['type'],$imageInfo['dir'],$imageInfo['thumb_path'],1,$imageInfo['image_type'],$imageInfo['time'],2);
            $url = $imageInfo['dir'];
        }else $url = $imageInfo['att_dir'];
        if($imageInfo['image_type'] == 1)
            $url = $siteUrl.$url;
        $combinationOne['image'] = UtilService::setSiteUrl($combinationOne['image'], $siteUrl);
        $combinationOne['image_base'] = UtilService::setSiteUrl($combinationOne['image'], $siteUrl);
        $combinationOne['code_base'] = $url;
        $combinationOne['sale_stock'] = 0;
        if($combinationOne['stock'] > 0) $combinationOne['sale_stock'] = 1;
        if(!strlen(trim($combinationOne['unit_name']))) $combinationOne['unit_name'] = '个';
        $uid = $request->uid();
        $data['pink'] = $pink;
        $data['pindAll'] = $pindAll;
        $data['storeInfo'] = $combinationOne;
        $data['pink_ok_list']=StorePink::getPinkOkList($uid);
        $data['pink_ok_sum']=StorePink::getPinkOkSumTotalNum($id);
        $data['reply'] = StoreProductReply::getRecProductReply($combinationOne['product_id']);
        $data['replyCount'] = StoreProductReply::productValidWhere()->where('product_id',$combinationOne['product_id'])->count();
        if($data['replyCount']){
            $goodReply=StoreProductReply::productValidWhere()->where('product_id',$combinationOne['product_id'])->where('product_score',5)->count();
            $data['replyChance'] = $goodReply;
            if($goodReply){
                $data['replyChance'] = bcdiv($goodReply,$data['replyCount'],2);
                $data['replyChance'] = bcmul($data['replyChance'],100,3);
            }
        }else $data['replyChance']=0;
        return app('json')->successful($data);
    }

    /**
     * 拼团 开团
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function pink(Request $request, $id)
    {
        $is_ok = 0;//判断拼团是否完成
        $userBool = 0;//判断当前用户是否在团内  0未在 1在
        $pinkBool = 0;//判断拼团是否成功  0未在 1在
        $user = $request->user();
        if(!$id) return app('json')->fail('参数错误');
        $pink = StorePink::getPinkUserOne($id);
        if(!$pink) return app('json')->fail('参数错误');
        if(isset($pink['is_refund']) && $pink['is_refund']) {
            if($pink['is_refund'] != $pink['id']){
                $id = $pink['is_refund'];
                return $this->pink($id);
            }else{
                return app('json')->fail('订单已退款');
            }
        }
        list($pinkAll,$pinkT,$count,$idAll,$uidAll) = StorePink::getPinkMemberAndPinkK($pink);
        if($pinkT['status'] == 2){
            $pinkBool = 1;
            $is_ok = 1;
        }else if($pinkT['status'] == 3){
            $pinkBool = -1;
            $is_ok = 0;
        }else{
            if($count < 1){//组团完成
                $is_ok = 1;
                $pinkBool = StorePink::PinkComplete($uidAll,$idAll,$user['uid'],$pinkT);
            }else{
                $pinkBool = StorePink::PinkFail($pinkAll,$pinkT,$pinkBool);
            }
        }
        if(!empty($pinkAll)){
            foreach ($pinkAll as $v){
                if($v['uid'] == $user['uid']) $userBool = 1;
            }
        }
        if($pinkT['uid'] == $user['uid']) $userBool = 1;
        $combinationOne = StoreCombination::getCombinationOne($pink['cid']);
        if(!$combinationOne) return app('json')->fail('拼团不存在或已下架');
        $data['userInfo']['uid'] = $user['uid'];
        $data['userInfo']['nickname'] = $user['nickname'];
        $data['userInfo']['avatar'] = $user['avatar'];
        $data['is_ok'] = $is_ok;
        $data['userBool'] = $userBool;
        $data['pinkBool'] = $pinkBool;
        $data['store_combination'] = $combinationOne->hidden(['mer_id','images','attr','info','sort','sales','stock','add_time','is_host','is_show','is_del','combination','mer_use','is_postage','postage','description','start_time','stop_time','cost','browse','product_price'])->toArray();
        $data['pinkT'] = $pinkT;
        $data['pinkAll'] = $pinkAll;
        $data['count'] = $count <= 0 ? 0 : $count;
        $data['store_combination_host'] = StoreCombination::getCombinationHost();
        $data['current_pink_order'] = StorePink::getCurrentPink($id,$user['uid']);
        return app('json')->successful($data);
    }

    /**
     * 拼团 取消开团
     * @param Request $request
     * @return mixed
     */
    public function remove(Request $request)
    {
        list($id, $cid) = UtilService::postMore([
            ['id',0],
            ['cid',0],
        ],$request, true);
        if(!$id || !$cid) return app('json')->fail('缺少参数');
        $res = StorePink::removePink($request->uid(),$cid,$id);
        if($res){
            return app('json')->successful('取消成功');
        }
        $error = StorePink::getErrorInfo();
        if(is_array($error)) return app('json')->status($error['status'],$error['msg']);
        return app('json')->fail($error);
    }


    /**
     * 拼团海报
     * @param Request $request
     * @return mixed
     */
    public function poster(Request $request)
    {
        list($pinkId, $from) = UtilService::postMore([['id',0],['from','wechat']],$request,true);
        if(!$pinkId) return app('json')->fail('参数错误');
        $user = $request->user();
        $pinkInfo = StorePink::getPinkUserOne($pinkId);
        $storeCombinationInfo = StoreCombination::getCombinationOne($pinkInfo['cid']);
        $data['title'] = $storeCombinationInfo['title'];
        $data['image'] = $storeCombinationInfo['image'];
        $data['price'] = $pinkInfo['total_price'];
        $data['label'] = $pinkInfo['people'].'人团';
        if($pinkInfo['k_id']) $pinkAll = StorePink::getPinkMember($pinkInfo['k_id']);
        else $pinkAll = StorePink::getPinkMember($pinkInfo['id']);
        $count = count($pinkAll)+1;
        $data['msg'] = '原价￥'.$storeCombinationInfo['product_price'].' 还差'.(int)bcsub((int)$pinkInfo['people'],$count,0).'人拼团成功';
        try{
            $siteUrl = SystemConfigService::get('site_url');
            if($from == 'routine'){
                //小程序
                $name = $pinkId.'_'.$user['uid'].'_'.$user['is_promoter'].'_pink_share_routine.jpg';
                $imageInfo = SystemAttachment::getInfo($name,'name');
                if(!$imageInfo){
                    $valueData = 'id='.$pinkId;
                    if($user['is_promoter'] || SystemConfigService::get('store_brokerage_statu')==2) $valueData.='&pid='.$user['uid'];
                    $res = RoutineCode::getPageCode('pages/activity/goods_combination_status/index',$valueData,280);
                    if(!$res) return app('json')->fail('二维码生成失败');
                    $imageInfo = UploadService::imageStream($name,$res,'routine/activity/pink/code');
                    if(!is_array($imageInfo)) return app('json')->fail($imageInfo);
                    if($imageInfo['image_type'] == 1) $remoteImage = UtilService::remoteImage($siteUrl.$imageInfo['dir']);
                    else $remoteImage = UtilService::remoteImage($imageInfo['dir']);
                    if(!$remoteImage['status']) return app('json')->fail($remoteImage['msg']);
                    SystemAttachment::attachmentAdd($imageInfo['name'],$imageInfo['size'],$imageInfo['type'],$imageInfo['dir'],$imageInfo['thumb_path'],1,$imageInfo['image_type'],$imageInfo['time'],2);
                    $url = $imageInfo['dir'];
                }else $url = $imageInfo['att_dir'];
                $data['url'] = $url;
                if($imageInfo['image_type'] == 1)
                    $data['url'] = $siteUrl.$url;
                $posterImage = UtilService::setShareMarketingPoster($data,'routine/activity/pink/poster');
                if(!is_array($posterImage)) return app('json')->fail('海报生成失败');
                SystemAttachment::attachmentAdd($posterImage['name'],$posterImage['size'],$posterImage['type'],$posterImage['dir'],$posterImage['thumb_path'],1,$posterImage['image_type'],$posterImage['time'],2);
                if($posterImage['image_type'] == 1)  $posterImage['dir'] = $siteUrl.$posterImage['dir'];
                $routinePosterImage = UtilService::setHttpType($posterImage['dir'], 0);//小程序推广海报
                return app('json')->successful(['url'=>$routinePosterImage]);
            }else if($from == 'wechat'){
                //公众号
                $name = $pinkId.'_'.$user['uid'].'_'.$user['is_promoter'].'_pink_share_wap.jpg';
                $imageInfo = SystemAttachment::getInfo($name,'name');
                if(!$imageInfo){
                    $codeUrl = UtilService::setHttpType($siteUrl.'/activity/group_rule/'.$pinkId.'?spread='.$user['uid'], 1);//二维码链接
                    $imageInfo = UtilService::getQRCodePath($codeUrl, $name);
                    if(!$imageInfo) return app('json')->fail('二维码生成失败');
                    SystemAttachment::attachmentAdd($imageInfo['name'],$imageInfo['size'],$imageInfo['type'],$imageInfo['dir'],$imageInfo['thumb_path'],1,$imageInfo['image_type'],$imageInfo['time'],2);
                    $url = $imageInfo['dir'];
                }else $url = $imageInfo['att_dir'];
                $data['url'] = $url;
                if($imageInfo['image_type'] == 1) $data['url'] = $siteUrl.$url;
                $posterImage = UtilService::setShareMarketingPoster($data,'wap/activity/pink/poster');
                if(!is_array($posterImage)) return app('json')->fail('海报生成失败');
                SystemAttachment::attachmentAdd($posterImage['name'],$posterImage['size'],$posterImage['type'],$posterImage['dir'],$posterImage['thumb_path'],1,$posterImage['image_type'],$posterImage['time'],2);
                if($posterImage['image_type'] == 1) $posterImage['dir'] = $siteUrl.$posterImage['dir'];
                $wapPosterImage = UtilService::setHttpType($posterImage['dir'], 1);//公众号推广海报
                return app('json')->successful(['url'=>$wapPosterImage]);
            }
            return app('json')->fail('参数错误');
        }catch (\Exception $e){
            return app('json')->fail(['line'=>$e->getLine(),'message'=>$e->getMessage()]);
        }

    }
}