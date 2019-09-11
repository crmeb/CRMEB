<?php
namespace app\admin\controller\sms;

use app\admin\controller\AuthController;
use crmeb\services\JsonService;
use crmeb\services\SMSService;
use crmeb\services\UtilService;

/**
 * 公共短信模板
 * Class SmsPublicTemp
 * @package app\admin\controller\sms
 */
class SmsPublicTemp extends AuthController
{

    public function index()
    {
        $sms = new SMSService();
        if(!$sms::$status) return $this->failed('请先填写短信配置');
        return $this->fetch();
    }

    /**
     * 异步获取公共模板列表
     */
    public function lst()
    {
        $where = UtilService::getMore([
            ['is_have',''],
            ['page',1],
            ['limit',20],
        ]);
        $templateList = SMSService::publictemp($where);
        if($templateList['status'] == 400) return JsonService::fail($templateList['msg']);
        return JsonService::successlayui($templateList['data']);
    }

    /**
     * 添加公共短信模板
     */
    public function status()
    {
        list($id, $tempId) = UtilService::postMore([
            ['id', 0],
            ['tempId', 0]
        ], null, true);
        if(!(int)$id) return JsonService::fail('参数错误');
        if(!strlen(trim($tempId))) return JsonService::fail('参数错误');
        $useStatus = SMSService::use($id, $tempId);
        if($useStatus['status'] == 400) return JsonService::fail($useStatus['msg']);
        return JsonService::success($useStatus['msg']);
    }


}