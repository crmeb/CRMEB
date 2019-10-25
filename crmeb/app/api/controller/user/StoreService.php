<?php
namespace app\api\controller\user;

use app\models\store\StoreServiceLog;
use app\models\store\StoreService as StoreServiceModel;
use app\Request;
use crmeb\services\UtilService;

/**
 * 客服类
 * Class StoreService
 * @package app\api\controller\user
 */
class StoreService
{

    /**
     * 客服列表
     * @param Request $request
     * @return mixed
     */
    public function lst(Request $request)
    {
        list($page, $limit) = UtilService::getMore([['page',0],['limit',0]], $request, true);
//        if(!$page || !$limit) return app('json')->successful([]);
        $serviceInfoList = StoreServiceModel::lst($page, $limit);
        if(!count($serviceInfoList)) return app('json')->successful([]);
        return app('json')->successful($serviceInfoList->hidden(['notify', 'status', 'mer_id', 'add_time'])->toArray());
    }
    /**
     * 客服聊天记录
     * @param Request $request
     * @param $toUid
     * @return array
     */
    public function record(Request $request, $toUid)
    {
        list($page, $limit) = UtilService::getMore([['page',0],['limit',0]], $request, true);
        if(!$toUid) return app('json')->fail('参数错误');
        $uid = $request->uid();
        if(!$limit || !$page) return app('json')->successful([]);
        $serviceLogList = StoreServiceLog::lst($uid, $toUid, $page, $limit);
        if(!$serviceLogList) return app('json')->successful([]);
        $serviceLogList = $serviceLogList->hidden(['mer_id'])->toArray();
        $idArr = array_column($serviceLogList, 'id');
        array_multisort($idArr,SORT_ASC,$serviceLogList);
        return app('json')->successful($serviceLogList);
    }

}