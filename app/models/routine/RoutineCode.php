<?php
namespace  app\models\routine;

use crmeb\services\MiniProgramService;

/**
 * TODO 小程序二维码Model
 * Class RoutineCode
 * @package app\models\routine
 */
class RoutineCode{

    /**
     * TODO 获取小程序二维码
     * @param $thirdId
     * @param $thirdType
     * @param $page
     * @param $imgUrl
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getShareCode($thirdId, $thirdType, $page, $imgUrl){
        $res = RoutineQrcode::routineQrCodeForever($thirdId,$thirdType,$page,$imgUrl);
        $resCode = MiniProgramService::qrcodeService()->appCodeUnlimit($res->id,$page,280);
        if($resCode){
            if($res) return ['res'=>$resCode,'id'=>$res->id];
            else return false;
        }else return false;
    }

    /**
     * TODO 获取小程序页面带参数二维码不保存数据库
     * @param string $page
     * @param string $pramam
     * @param int $width
     * @return mixed
     */
    public static function getPageCode($page = '', $pramam = "?uid=1&product=1",$width = 280){
        return MiniProgramService::qrcodeService()->appCodeUnlimit($pramam,$page,$width);
    }
}