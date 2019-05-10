<?php

/**
 * Created by PhpStorm.
 * User: xurongyao <763569752@qq.com>
 * Date: 2019/4/8 7:30 PM
 */
use app\admin\model\wechat\WechatQrcode as QrcodeModel;//待完善

class Qrcode
{
    /**
     * 获取临时二维码  单个
     * */
    public static function getTemporaryQrcode($type,$id){
        return QrcodeModel::getTemporaryQrcode($type,$id)->toArray();
    }
    /**
     * 获取永久二维码  单个
     * */
    public static function getForeverQrcode($type,$id){
        return QrcodeModel::getForeverQrcode($type,$id)->toArray();
    }

    public static function getQrcode($id,$type = 'id')
    {
        return QrcodeModel::getQrcode($id,$type);
    }

    public static function scanQrcode($id,$type = 'id')
    {
        return QrcodeModel::scanQrcode($id,$type);
    }
}