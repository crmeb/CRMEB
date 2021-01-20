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

namespace app\kefuapi\controller;


use app\Request;
use app\services\kefu\KefuServices;
use app\services\kefu\ProductServices;
use app\services\message\service\StoreServiceRecordServices;
use app\services\order\StoreOrderServices;
use app\services\system\attachment\SystemAttachmentServices;
use app\services\user\UserAuthServices;
use app\services\user\UserServices;
use app\services\other\CacheServices;
use app\services\message\service\StoreServiceServices;
use app\api\validate\user\StoreServiceFeedbackValidate;
use app\services\message\service\StoreServiceFeedbackServices;
use crmeb\exceptions\AuthException;
use crmeb\services\UploadService;
use crmeb\utils\Arr;
use crmeb\utils\JwtAuth;
use think\facade\Cache;

class Common
{

    /**
     * 获取客服页面广告内容
     * @return mixed
     */
    public function getKfAdv()
    {
        /** @var CacheServices $cache */
        $cache = app()->make(CacheServices::class);
        $content = $cache->getDbCache('kf_adv', '');
        return app('json')->success(compact('content'));
    }

    /**
     * 游客模式下获取客服
     * @param StoreServiceServices $services
     * @param UserServices $userServices
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getServiceUser(StoreServiceServices $services, UserServices $userServices, StoreServiceRecordServices $recordServices, $token = '')
    {
        $serviceInfoList = $services->getServiceList(['status' => 1, 'online' => 1]);
        if (!count($serviceInfoList['list'])) {
            return app('json')->fail('暂无客服人员在线');
        }
        $uids = array_column($serviceInfoList['list'], 'uid');
        $toUid = $tourist_uid = $uid = 0;
        if ($token) {
            try {
                /** @var UserAuthServices $service */
                $service = app()->make(UserAuthServices::class);
                $authInfo = $service->parseToken($token);
                $uid = $authInfo['user']['uid'];
                $toUid = $recordServices->value(['user_id' => $uid], 'to_uid');
                if (!in_array($toUid, $uids)) {
                    $toUid = 0;
                }
            } catch (AuthException $e) {
            }
        } else {
            $tourist_uid = rand(100000000, 999999999);
        }
        if (!$toUid) {
            $toUid = Arr::getArrayRandKey($uids);
        }
        $userInfo = $userServices->get($toUid, ['nickname', 'avatar', 'real_name', 'uid']);
        if ($userInfo) {
            $infoList = array_column($serviceInfoList['list'], null, 'uid');
            if (isset($infoList[$toUid])) {
                if ($infoList[$toUid]['wx_name']) {
                    $userInfo['nickname'] = $infoList[$toUid]['wx_name'];
                }
                if ($infoList[$toUid]['avatar']) {
                    $userInfo['avatar'] = $infoList[$toUid]['avatar'];
                }
            }
            $userInfo['tourist_uid'] = $uid ? $uid : $tourist_uid;
            $tourist_avatar = sys_config('tourist_avatar');
            $avatar = Arr::getArrayRandKey(is_array($tourist_avatar) ? $tourist_avatar : []);
            $userInfo['tourist_avatar'] = $uid ? '' : $avatar;
            $userInfo['is_tourist'] = $tourist_uid ? true : false;
            return app('json')->success($userInfo->toArray());
        } else {
            return app('json')->fail('暂无客服人员');
        }
    }

    /**
     * 保存反馈信息
     * @param Request $request
     * @param StoreServiceFeedbackServices $services
     * @return mixed
     */
    public function saveFeedback(Request $request, StoreServiceFeedbackServices $services)
    {
        $data = $request->postMore([
            ['rela_name', ''],
            ['phone', ''],
            ['content', ''],
        ]);

        validate(StoreServiceFeedbackValidate::class)->check($data);

        $data['content'] = htmlspecialchars($data['content']);
        $data['add_time'] = time();
        $services->save($data);
        return app('json')->success('保存成功');
    }

    /**
     * 客服反馈页面头部文字
     * @return mixed
     */
    public function getFeedbackInfo()
    {
        return app('json')->success(['feedback' => sys_config('service_feedback')]);
    }

    /**
     * 聊天记录
     * @param $uid
     * @return mixed
     */
    public function getChatList(Request $request, KefuServices $services, JwtAuth $auth, $token = '')
    {
        [$uid, $upperId] = $request->postMore([
            ['uid', 0],
            ['upperId', 0],
        ], true);
        if (!$uid) {
            return app('json')->fail('缺少参数');
        }
        if (!$token) {
            return app('json')->fail('缺少登录token');
        }
        try {
            /** @var UserAuthServices $service */
            $service = app()->make(UserAuthServices::class);
            $authInfo = $service->parseToken($token);
        } catch (AuthException $e) {
            return app('json')->fail('无效的token不能查找到用户聊天记录');
        }

        return app('json')->success($services->getChatList($authInfo['user']['uid'], $uid, (int)$upperId));
    }

    /**
     * 商品详情
     * @param ProductServices $services
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getProductInfo(ProductServices $services, $id)
    {
        return app('json')->success($services->getProductInfo((int)$id));
    }

    /**
     * 获取订单信息
     * @param StoreOrderServices $services
     * @param $token
     * @param $order_id
     * @return mixed
     */
    public function getOrderInfo(StoreOrderServices $services, $token, $order_id)
    {
        try {
            /** @var UserAuthServices $service */
            $service = app()->make(UserAuthServices::class);
            $authInfo = $service->parseToken($token);
            if (!isset($authInfo['user']['uid'])) {
                return app('json')->fail('非法操作');
            }
        } catch (AuthException $e) {
            return app('json')->fail('无效的token不能查找到用户聊天记录');
        }
        return app('json')->success($services->tidyOrder($services->getUserOrderDetail($order_id, $authInfo['user']['uid'])->toArray(), true));
    }

    /**
     * 图片上传
     * @param Request $request
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function upload(Request $request, SystemAttachmentServices $services)
    {
        $data = $request->postMore([
            ['filename', 'file'],
        ]);
        try {
            /** @var UserAuthServices $service */
            $service = app()->make(UserAuthServices::class);
            $authInfo = $service->parseToken($this->request->post('token'));
            if (!isset($authInfo['user']['uid'])) {
                return app('json')->fail('非法操作');
            }
        } catch (AuthException $e) {
            return app('json')->fail('无效的token不能使用上传图片功能');
        }
        $uid = $authInfo['user']['uid'];
        if (!$data['filename']) return app('json')->fail('参数有误');
        if (Cache::has('start_uploads_' . $uid) && Cache::get('start_uploads_' . $uid) >= 100) return app('json')->fail('非法操作');
        $upload = UploadService::init();
        $info = $upload->to('store/comment')->validate()->move($data['filename']);
        if ($info === false) {
            return app('json')->fail($upload->getError());
        }
        $res = $upload->getUploadInfo();
        $services->attachmentAdd($res['name'], $res['size'], $res['type'], $res['dir'], $res['thumb_path'], 1, (int)sys_config('upload_type', 1), $res['time'], 2);
        if (Cache::has('start_uploads_' . $uid))
            $start_uploads = (int)Cache::get('start_uploads_' . $uid);
        else
            $start_uploads = 0;
        $start_uploads++;
        Cache::set('start_uploads_' . $uid, $start_uploads, 86400);
        $res['dir'] = path_to_url($res['dir']);
        if (strpos($res['dir'], 'http') === false) $res['dir'] = $request->domain() . $res['dir'];
        return app('json')->success('图片上传成功!', ['name' => $res['name'], 'url' => $res['dir']]);
    }
}
