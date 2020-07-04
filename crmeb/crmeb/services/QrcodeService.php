<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/10/24
 */

namespace crmeb\services;


use app\admin\model\system\SystemAttachment;
use app\admin\model\wechat\WechatQrcode as QrcodeModel;

class QrcodeService
{

    /**
     * 获取临时二维码  单个
     * @param $type
     * @param $id
     * @return array
     */
    public static function getTemporaryQrcode($type, $id)
    {
        return QrcodeModel::getTemporaryQrcode($type, $id)->toArray();
    }

    /**
     * 获取永久二维码  单个
     * @param $type
     * @param $id
     * @return array
     */
    public static function getForeverQrcode($type, $id)
    {
        return QrcodeModel::getForeverQrcode($type, $id)->toArray();
    }

    /**
     * 从数据库获取二维码
     * @param $id
     * @param string $type
     * @return array|\think\Model|null
     */
    public static function getQrcode($id, $type = 'id')
    {
        return QrcodeModel::getQrcode($id, $type);
    }

    /**
     * 增加某个二维码扫描的次数
     * @param $id
     * @param string $type
     * @return mixed
     */
    public static function scanQrcode($id, $type = 'id')
    {
        return QrcodeModel::scanQrcode($id, $type);
    }

    /**
     * 获取二维码完整路径，不存在则自动生成
     * @param string $name 路径名
     * @param string $link 需要生成二维码的跳转路径
     * @param int $type https 1 = http , 0 = https
     * @param bool $force 是否返回false
     * @return bool|mixed|string
     */
    public static function getWechatQrcodePath(string $name, string $link, int $type = 1, bool $force = false)
    {
        try {
            $imageInfo = SystemAttachment::getInfo($name, 'name');
            $siteUrl = sys_config('site_url');
            if (!$imageInfo) {
                $codeUrl = UtilService::setHttpType($siteUrl . $link, $type);//二维码链接
                $imageInfo = UtilService::getQRCodePath($codeUrl, $name);
                if (is_string($imageInfo) && $force)
                    return false;
                if (is_array($imageInfo)) {
                    SystemAttachment::attachmentAdd($imageInfo['name'], $imageInfo['size'], $imageInfo['type'], $imageInfo['dir'], $imageInfo['thumb_path'], 1, $imageInfo['image_type'], $imageInfo['time'], 2);
                    $url = $imageInfo['dir'];
                } else {
                    $url = '';
                    $imageInfo = ['image_type' => 0];
                }
            } else $url = $imageInfo['att_dir'];
            if ($imageInfo['image_type'] == 1 && $url) $url = $siteUrl . $url;
            return $url;
        } catch (\Throwable $e) {
            if ($force)
                return false;
            else
                return '';
        }
    }

}