<?php

namespace app\api\controller\activity;

use app\admin\model\store\StoreDescription;
use app\admin\model\store\StoreProductAttrValue;
use app\Request;
use think\facade\Route;
use app\models\user\{
    User, WechatUser
};
use app\admin\model\system\SystemAttachment;
use app\models\routine\{
    RoutineCode, RoutineTemplate
};
use app\models\store\{StoreBargain, StoreBargainUser, StoreBargainUserHelp, StoreOrder, StoreProductAttr, StoreVisit};
use crmeb\services\{
    GroupDataService, UtilService, WechatTemplateService
};
use crmeb\services\upload\Upload;


/**
 * 砍价产品类
 * Class StoreBargainController
 * @package app\api\controller\activity
 */
class StoreBargainController
{

    /**
     * 砍价列表顶部图
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function config()
    {
        $lovely = sys_data('routine_lovely') ?? [];//banner图
        $info = isset($lovely[2]) ? $lovely[2] : [];
        return app('json')->successful($info);
    }

    /**
     * 砍价产品列表
     * @param Request $request
     * @return mixed
     */
    public function lst(Request $request)
    {
        list($page, $limit) = UtilService::getMore([
            ['page', 0],
            ['limit', 0],
        ], $request, true);
        $bargainList = StoreBargain::getList($page, $limit);
        return app('json')->successful($bargainList);
    }

    /**
     * 砍价详情和当前登录人信息
     * @param Request $request
     * @param $id
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function detail(Request $request, $id)
    {
        if (!$id) return app('json')->fail('参数错误');
        if (!($bargain = StoreBargain::getBargainTerm($id))) return app('json')->fail('砍价已结束');
        StoreBargain::addBargainLook($id);
        $bargain['time'] = time();
        $user = $request->user();
        $data['userInfo']['uid'] = $user['uid'];
        $data['userInfo']['nickname'] = $user['nickname'];
        $data['userInfo']['avatar'] = $user['avatar'];
        $bargain['description'] = htmlspecialchars_decode(StoreDescription::getDescription($id, 2));
        list($productAttr, $productValue) = StoreProductAttr::getProductAttrDetail($id, $user['uid'], 0, 2);
        foreach ($productValue as $k => $v) {
            $v['product_stock'] = StoreProductAttrValue::where('product_id', $bargain['product_id'])->where('suk', $v['suk'])->where('type', 0)->value('stock');
            $bargain['attr'] = $v;
        }
        $data['bargain'] = $bargain;
        $data['bargainSumCount'] = StoreOrder::getBargainPayCount($id);
        $data['userBargainStatus'] = StoreBargainUser::isBargainUser($id, $user['uid']);
        StoreVisit::setView($user['uid'], $id, 'bargain',$bargain['product_id'], 'viwe');
        return app('json')->successful($data);
    }

    /**
     * 砍价 观看/分享/参与次数
     * @param Request $request
     * @return mixed
     */
    public function share(Request $request)
    {
        list($bargainId) = UtilService::postMore([['bargainId', 0]], $request, true);
        $data['lookCount'] = StoreBargain::getBargainLook();//TODO 观看人数
        $data['shareCount'] = StoreBargain::getBargainShare();//TODO 分享人数
        $data['userCount'] = StoreBargainUser::count();//TODO 参与人数
        if (!$bargainId) return app('json')->successful($data);
        StoreBargain::addBargainShare($bargainId);
        $data['shareCount'] = StoreBargain::getBargainShare();//TODO 分享人数
        return app('json')->successful($data);
    }

    /**
     * 砍价开启
     * @param Request $request
     * @return mixed
     * @throws \think\Exception
     */
    public function start(Request $request)
    {
        list($bargainId) = UtilService::postMore([['bargainId', 0]], $request, true);
        if (!($bargain = StoreBargain::getBargainTerm($bargainId))) return app('json')->fail('砍价已结束');
        if (!$bargainId) return app('json')->fail('参数错误');
        $count = StoreBargainUser::isBargainUser($bargainId, $request->uid());
        if ($count === false) return app('json')->fail('参数错误');
        else if ($count) return app('json')->status('SUCCESSFUL', '参与成功');
        else $res = StoreBargainUser::setBargain($bargainId, $request->uid());
        if (!$res) return app('json')->fail('参与失败');
        else return app('json')->status('SUCCESS', '参与成功');
    }

    /**
     * 砍价 帮助好友砍价
     * @param Request $request
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function help(Request $request)
    {
        list($bargainId, $bargainUserUid) = UtilService::postMore([['bargainId', 0], ['bargainUserUid', 0]], $request, true);
        if (!$bargainId || !$bargainUserUid) return app('json')->fail('参数错误');
        $count = StoreBargainUserHelp::isBargainUserHelpCount($bargainId, $bargainUserUid, $request->uid());
        if (!$count) return app('json')->status('SUCCESSFUL', '砍价成功');
        $res = StoreBargainUserHelp::setBargainUserHelp($bargainId, $bargainUserUid, $request->uid());
        if ($res) {
            if (!StoreBargainUserHelp::getSurplusPrice($bargainId, $bargainUserUid)) {
                $bargainUserTableId = StoreBargainUser::getBargainUserTableId($bargainId, $bargainUserUid);// TODO 获取用户参与砍价表编号
                $bargainInfo = StoreBargain::get($bargainId);//TODO 获取砍价产品信息
                $bargainUserInfo = StoreBargainUser::get($bargainUserTableId);// TODO 获取用户参与砍价信息
                //TODO 砍价成功给开启砍价用户发送模板消息
                $openid = WechatUser::uidToOpenid($bargainUserUid, 'openid');
                $routineOpenid = WechatUser::uidToOpenid($bargainUserUid, 'routine_openid');
                if ($openid) {//公众号
                    $urlWeChat = Route::buildUrl('/activity/dargain_detail/' . $bargainId . '/' . $bargainUserUid)->suffix('')->domain(true)->build();
                    WechatTemplateService::sendTemplate($openid, WechatTemplateService::BARGAIN_SUCCESS, [
                        'first' => '好腻害！你的朋友们已经帮你砍到底价了！',
                        'keyword1' => $bargainInfo['title'],
                        'keyword2' => $bargainInfo['min_price'],
                        'remark' => '点击查看订单详情'
                    ], $urlWeChat);
                } else if ($routineOpenid) { //小程序
                    RoutineTemplate::sendBargainSuccess($bargainInfo, $bargainUserInfo, $bargainUserUid);
                }
            }
            return app('json')->status('SUCCESS', '砍价成功');
        } else return app('json')->fail('砍价失败');
    }

    /**
     * 砍价 砍掉金额
     * @param Request $request
     * @return mixed
     */
    public function help_price(Request $request)
    {
        list($bargainId, $bargainUserUid) = UtilService::postMore([['bargainId', 0], ['bargainUserUid', 0]], $request, true);
        if (!$bargainId || !$bargainUserUid) return app('json')->fail('参数错误');
        $bargainUserTableId = StoreBargainUser::getBargainUserTableId($bargainId, $bargainUserUid);//TODO 获取用户参与砍价表编号
        $price = StoreBargainUserHelp::getBargainUserBargainPrice($bargainId, $bargainUserTableId, $request->uid(), 'price');// TODO 获取用户砍掉的金额
        if ($price) return app('json')->successful(['price' => $price]);
        else return app('json')->fail('获取失败');
    }

    /**
     * 砍价 砍价帮总人数、剩余金额、进度条、已经砍掉的价格
     * @param Request $request
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function help_count(Request $request)
    {
        list($bargainId, $bargainUserUid) = UtilService::postMore([['bargainId', 0], ['bargainUserUid', 0]], $request, true);
        $data['userBargainStatus'] = StoreBargainUserHelp::isBargainUserHelpCount($bargainId, $bargainUserUid, $request->uid());
        if (!$bargainId || !$bargainUserUid) return app('json')->fail('参数错误');
        $bargainUserTableId = StoreBargainUser::getBargainUserTableId($bargainId, $bargainUserUid);//TODO 获取用户参与砍价表编号
        if ($bargainUserTableId) {
            $count = StoreBargainUserHelp::getBargainUserHelpPeopleCount($bargainId, $bargainUserUid);//TODO 获取砍价帮总人数
            $price = StoreBargainUserHelp::getSurplusPrice($bargainId, $bargainUserUid);//TODO 获取砍价剩余金额
            $alreadyPrice = StoreBargainUser::getBargainUserPrice($bargainUserTableId);//TODO 用户已经砍掉的价格 好友砍价之后获取用户已经砍掉的价格
            $pricePercent = StoreBargainUserHelp::getSurplusPricePercent($bargainId, $bargainUserUid);//TODO 获取砍价进度条
            $data['count'] = $count;
            $data['price'] = $price;
            $data['status'] = StoreBargainUser::getBargainUserStatusEnd($bargainUserTableId);
            $data['alreadyPrice'] = $alreadyPrice;
            $data['pricePercent'] = $pricePercent > 10 ? $pricePercent : 10;
        } else {
            $data['count'] = 0;
            $data['price'] = StoreBargain::where('id', $bargainId)->value('price - min_price');
            $data['status'] = StoreBargainUser::getBargainUserStatusEnd($bargainUserTableId);
            $data['alreadyPrice'] = 0;
            $data['pricePercent'] = 0;
        }
        return app('json')->successful($data);
    }


    /**
     * 砍价 砍价帮
     * @param Request $request
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function help_list(Request $request)
    {
        list($bargainId, $bargainUserUid, $page, $limit) = UtilService::postMore([
            ['bargainId', 0],
            ['bargainUserUid', 0],
            ['page', 0],
            ['limit', 20]
        ], $request, true);
        if (!$bargainId || !$bargainUserUid) return app('json')->fail('参数错误');
        $bargainUserTableId = StoreBargainUser::getBargainUserTableId($bargainId, $bargainUserUid); //TODO 砍价帮获取参与砍价表编号
        $storeBargainUserHelp = StoreBargainUserHelp::getList($bargainUserTableId, $page, $limit);
        return app('json')->successful($storeBargainUserHelp);
    }

    /**
     * 砍价 开启砍价用户信息
     * @param Request $request
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function start_user(Request $request)
    {
        list($bargainId, $bargainUserUid) = UtilService::postMore([
            ['bargainId', 0],
            ['bargainUserUid', 0],
        ], $request, true);
        if (!$bargainId || !$bargainUserUid) return app('json')->fail('参数错误');
//        $bargainUserTableId = StoreBargainUser::getBargainUserTableId($bargainId,$bargainUserUid);//TODO 获取用户参与砍价表编号
//        if(!$bargainUserTableId) return app('json')->fail('参数错误');
        $userInfo = User::getUserInfo($bargainUserUid, 'nickname,avatar');
        return app('json')->successful($userInfo);
    }


    /**
     * 砍价列表(已参与)
     * @param Request $request
     * @return mixed
     */
    public function user_list(Request $request)
    {
        list($page, $limit) = UtilService::getMore([
            ['page', 0],
            ['limit', 0],
        ], $request, true);
        $uid = $request->uid();
        StoreBargainUser::editBargainUserStatus($uid);// TODO 判断过期砍价活动
        $list = StoreBargainUser::getBargainUserAll($uid, $page, $limit);
        if (count($list)) return app('json')->successful($list);
        else return app('json')->fail('暂无参与砍价');
    }

    /**
     * 砍价取消
     * @param Request $request
     * @return mixed
     */
    public function user_cancel(Request $request)
    {
        list($bargainId) = UtilService::postMore([['bargainId', 0]], $request, true);
        if (!$bargainId) return app('json')->fail('参数错误');
        $status = StoreBargainUser::getBargainUserStatus($bargainId, $request->uid());
        if ($status != 1) return app('json')->fail('状态错误');
        $id = StoreBargainUser::getBargainUserTableId($bargainId, $request->uid());
        $res = StoreBargainUser::edit(['is_del' => 1], $id);
        if ($res) return app('json')->successful('取消成功');
        else return app('json')->successful('取消失败');
    }

    /**
     * 砍价海报
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function poster(Request $request)
    {
        list($bargainId, $from) = UtilService::postMore([['bargainId', 0], ['from', 'wechat']], $request, true);
        if (!$bargainId) return app('json')->fail('参数错误');
        $user = $request->user();
        $storeBargainInfo = StoreBargain::getBargain($bargainId);
        $price = StoreBargainUserHelp::getSurplusPrice($bargainId, $user['uid']);//TODO 获取砍价剩余金额
        $alreadyPrice = StoreBargainUser::getBargainUserPrice(StoreBargainUser::getBargainUserTableId($bargainId, $user['uid']));
        try {
            $siteUrl = sys_config('site_url');
            $data['title'] = $storeBargainInfo['title'];
            $data['image'] = $storeBargainInfo['image'];
            $data['price'] = bcsub($storeBargainInfo['price'], $alreadyPrice, 2);
            $data['label'] = '已砍至';
            $data['msg'] = '还差' . $price . '元即可砍价成功';
            if ($from == 'routine') {
                //小程序
                $name = $bargainId . '_' . $user['uid'] . '_' . $user['is_promoter'] . '_bargain_share_routine.jpg';
                $imageInfo = SystemAttachment::getInfo($name, 'name');
                if (!$imageInfo) {
                    $valueData = 'id=' . $bargainId . '&bargain=' . $user['uid'];
                    if ($user['is_promoter'] || sys_config('store_brokerage_statu') == 2) $valueData .= '&pid=' . $user['uid'];
                    $res = RoutineCode::getPageCode('pages/activity/goods_bargain_details/index', $valueData, 280);
                    if (!$res) return app('json')->fail('二维码生成失败');
                    $uploadType = (int)sys_config('upload_type', 1);
                    $upload = new Upload($uploadType, [
                        'accessKey' => sys_config('accessKey'),
                        'secretKey' => sys_config('secretKey'),
                        'uploadUrl' => sys_config('uploadUrl'),
                        'storageName' => sys_config('storage_name'),
                        'storageRegion' => sys_config('storage_region'),
                    ]);
                    $res = $upload->to('routine/activity/bargain/code')->validate()->stream($res, $name);
                    if ($res === false) {
                        return app('json')->fail($upload->getError());
                    }
                    $imageInfo = $upload->getUploadInfo();
                    $imageInfo['image_type'] = $uploadType;
                    if ($imageInfo['image_type'] == 1) $remoteImage = UtilService::remoteImage($siteUrl . $imageInfo['dir']);
                    else $remoteImage = UtilService::remoteImage($imageInfo['dir']);
                    if (!$remoteImage['status']) return app('json')->fail($remoteImage['msg']);
                    SystemAttachment::attachmentAdd($imageInfo['name'], $imageInfo['size'], $imageInfo['type'], $imageInfo['dir'], $imageInfo['thumb_path'], 1, $imageInfo['image_type'], $imageInfo['time'], 2);
                    $url = $imageInfo['dir'];
                } else $url = $imageInfo['att_dir'];
                $data['url'] = $url;
                if ($imageInfo['image_type'] == 1)
                    $data['url'] = $siteUrl . $url;
                $posterImage = UtilService::setShareMarketingPoster($data, 'routine/activity/bargain/poster');
                if (!is_array($posterImage)) return app('json')->fail('海报生成失败');
                SystemAttachment::attachmentAdd($posterImage['name'], $posterImage['size'], $posterImage['type'], $posterImage['dir'], $posterImage['thumb_path'], 1, $posterImage['image_type'], $posterImage['time'], 2);
                if ($posterImage['image_type'] == 1) $posterImage['dir'] = $siteUrl . $posterImage['dir'];
                $routinePosterImage = set_http_type($posterImage['dir'], 0);//小程序推广海报
                return app('json')->successful(['url' => $routinePosterImage]);
            } else if ($from == 'wechat') {
                //公众号
                $name = $bargainId . '_' . $user['uid'] . '_' . $user['is_promoter'] . '_bargain_share_wap.jpg';
                $imageInfo = SystemAttachment::getInfo($name, 'name');
                if (!$imageInfo) {
                    $codeUrl = set_http_type($siteUrl . '/activity/dargain_detail/' . $bargainId . '/' . $user['uid'] . '?spread=' . $user['uid'], 1);//二维码链接
                    $imageInfo = UtilService::getQRCodePath($codeUrl, $name);
                    if (is_string($imageInfo)) {
                        return app('json')->fail('二维码生成失败', ['error' => $imageInfo]);
                    }
                    SystemAttachment::attachmentAdd($imageInfo['name'], $imageInfo['size'], $imageInfo['type'], $imageInfo['dir'], $imageInfo['thumb_path'], 1, $imageInfo['image_type'], $imageInfo['time'], 2);
                    $url = $imageInfo['dir'];
                } else $url = $imageInfo['att_dir'];
                $data['url'] = $url;
                if ($imageInfo['image_type'] == 1) $data['url'] = $siteUrl . $url;
                $posterImage = UtilService::setShareMarketingPoster($data, 'wap/activity/bargain/poster');
                if (!is_array($posterImage)) return app('json')->fail('海报生成失败');
                SystemAttachment::attachmentAdd($posterImage['name'], $posterImage['size'], $posterImage['type'], $posterImage['dir'], $posterImage['thumb_path'], 1, $posterImage['image_type'], $posterImage['time'], 2);
                if ($posterImage['image_type'] == 1) $posterImage['dir'] = $siteUrl . $posterImage['dir'];
                $wapPosterImage = set_http_type($posterImage['dir'], 1);//公众号推广海报
                return app('json')->successful(['url' => $wapPosterImage]);
            }
            return app('json')->fail('参数错误');
        } catch (\Exception $e) {
            return app('json')->fail(['line' => $e->getLine(), 'message' => $e->getMessage()]);
        }
    }


}