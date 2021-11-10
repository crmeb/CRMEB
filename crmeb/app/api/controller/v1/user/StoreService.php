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
namespace app\api\controller\v1\user;

use app\api\validate\user\StoreServiceFeedbackValidate;
use app\Request;
use app\services\message\service\StoreServiceFeedbackServices;
use app\services\message\service\StoreServiceLogServices;
use app\services\message\service\StoreServiceRecordServices;
use app\services\message\service\StoreServiceServices;
use app\services\other\CacheServices;
use crmeb\services\CacheService;

/**
 * 客服类
 * Class StoreService
 * @package app\api\controller\user
 */
class StoreService
{
    /**
     * @var StoreServiceLogServices
     */
    protected $services;

    /**
     * StoreService constructor.
     * @param StoreServiceLogServices $services
     */
    public function __construct(StoreServiceLogServices $services)
    {
        $this->services = $services;
    }

    /**
     * 客服列表
     * @return mixed
     */
    public function lst(StoreServiceServices $services)
    {
        $serviceInfoList = $services->getServiceList(['status' => 1]);
        if (!count($serviceInfoList)) return app('json')->successful([]);
        return app('json')->successful($serviceInfoList['list']);
    }

    /**
     * 客服聊天记录
     * @param Request $request
     * @param $toUid
     * @return array
     */
    public function record(Request $request, StoreServiceServices $services, StoreServiceRecordServices $recordServices)
    {
        list($uidTo) = $request->getMore([
            ['uidTo', 0]
        ], true);
        $serviceInfoList = $services->getServiceList(['status' => 1]);
        if (!count($serviceInfoList)) return app('json')->fail('暂无客服人员在线，请稍后联系');
        $uid = $request->uid();
        $uids = array_column($serviceInfoList['list'], 'uid');
        if (!$uidTo) {
            //自己是客服
            if (in_array($uid, $uids)) {
                $uids = array_merge(array_diff($uids, [$uid]));
                if (!$uids) return app('json')->fail('不能和自己聊天');
            }
        } else {
            if (in_array($uid, $uids)) {
                $uid = $uidTo;
            }
        }
        if (!$uids) {
            return app('json')->fail('暂无客服人员在线，请稍后联系');
        }
        //上次聊天客服优先对话
        $toUid = $recordServices->value(['user_id' => $uid], 'to_uid');
        if (!in_array($toUid, $uids)) {
            $toUid = 0;
        }
        if (!$toUid) {
            $toUid = $uids[array_rand($uids)] ?? 0;
        }

        if (!$toUid) return app('json')->fail('暂无客服人员在线，请稍后联系');
        $result = ['serviceList' => [], 'uid' => $toUid];
        $serviceLogList = $this->services->getChatList(['uid' => $uid], $uid);
        if (!$serviceLogList) return app('json')->successful($result);
        $idArr = array_column($serviceLogList, 'id');
        array_multisort($idArr, SORT_ASC, $serviceLogList);
        $result['serviceList'] = $serviceLogList;
        return app('json')->successful($result);
    }

    /**
     * 获取客服页面广告内容
     * @return mixed
     */
    public function getKfAdv()
    {
        /** @var CacheServices $cache */
        $cache = app()->make(CacheServices::class);
        $content = $cache->getDbCache('kf_adv', '');
        return app('json')->successful(compact('content'));
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
        $data['uid'] = $request->uid();
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
     * 确认登录
     * @param Request $request
     * @param string $code
     * @return mixed
     */
    public function setLoginCode(Request $request, StoreServiceServices $services, string $code)
    {
        if (!$code) {
            return app('json')->fail('登录CODE不存在');
        }
        $cacheCode = CacheService::get($code);
        if ($cacheCode === false || $cacheCode === null) {
            return app('json')->fail('二维码已过期请重新扫描');
        }
        $userInfo = $services->get(['uid' => $request->uid()]);
        if (!$userInfo) {
            return app('json')->fail('您不是客服无法登录');
        }
        $userInfo->uniqid = $code;
        $userInfo->save();
        CacheService::set($code, '0', 600);
        return app('json')->success('登录成功');
    }

    /**
     * 获取当前客服和用户的聊天记录
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function recordList(StoreServiceRecordServices $services, Request $request, string $nickname = '', $is_tourist = 0)
    {
        return app('json')->success($services->getServiceList((int)$request->uid(), $nickname, (int)$is_tourist));
    }
}
