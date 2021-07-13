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

use app\Request;
use app\services\message\service\StoreServiceLogServices;
use app\services\message\service\StoreServiceRecordServices;
use app\services\message\service\StoreServiceServices;
use app\services\other\CacheServices;

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
        list($uidTo,$to_uid) = $request->getMore([
            ['uidTo', 0],
            ['toUid', 0]
        ], true);
        // var_dump($to_uid);
        $serviceInfoList = $services->getServiceList(['status' => 1]);
        if (!count($serviceInfoList)) return app('json')->fail('暂无客服人员在线，请稍后联系');
        $uid = $request->uid();
        $uids = array_column($serviceInfoList['list'], 'uid');
        if (!$uidTo && !$to_uid) {
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
        // $toUid = $recordServices->value(['user_id' => $uid], 'to_uid');
        // if (!in_array($toUid, $uids)) {
        //     $toUid = 0;
        // }
        // if (!$toUid) {
        //     $toUid = $uids[array_rand($uids)] ?? 0;
        // }
        if($to_uid){
            $toUid = $uid = $to_uid;
        }else{
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
        $content = $cache->getDbCache('kf_adv','');
        return app('json')->successful(compact('content'));
    }
}
