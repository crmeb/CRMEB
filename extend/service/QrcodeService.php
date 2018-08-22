<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/10/24
 */

namespace service;


use app\admin\model\wechat\WechatQrcode as QrcodeModel;

class QrcodeService
{
    /**
     * 获取临时二维码  单个
     * */
     public static function getTemporaryQrcode($type,$id){
         return QrcodeModel::getTemporaryQrcode($type,$id)->toArray();
     }/**
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