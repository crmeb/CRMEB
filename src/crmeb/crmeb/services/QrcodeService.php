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
namespace crmeb\services;

use app\services\system\attachment\SystemAttachmentServices;

class QrcodeService
{

    /**
     * 获取二维码完整路径，不存在则自动生成
     * @param string $name 路径名
     * @param string $link 需要生成二维码的跳转路径
     * @param int $type https 1 = http , 0 = https
     * @param bool $force 是否返回false
     * @return bool|mixed|string
     */
    public static function getWechatQrcodePath(string $name, string $link, bool $force = false)
    {
        /** @var SystemAttachmentServices $systemAttchment */
        $systemAttchment = app()->make(SystemAttachmentServices::class);
        try {
            $imageInfo = $systemAttchment->getInfo(['name'=>$name]);
            $siteUrl = sys_config('site_url');
            if (!$imageInfo) {
                $codeUrl = UtilService::setHttpType($siteUrl . $link, request()->isSsl() ? 0 : 1);//二维码链接
                $imageInfo = UtilService::getQRCodePath($codeUrl, $name);
                if (is_string($imageInfo) && $force)
                    return false;
                if (is_array($imageInfo)) {
                    $systemAttchment->attachmentAdd($imageInfo['name'], $imageInfo['size'], $imageInfo['type'], $imageInfo['dir'], $imageInfo['thumb_path'], 1, $imageInfo['image_type'], $imageInfo['time'], 2);
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

    /**
     * 获取小程序分享二维码
     * @param int $id
     * @param int $uid
     * @param int $type 1 = 拼团,2 = 秒杀
     * @return bool|string
     */
    public static function getRoutineQrcodePath(int $id, int $uid, int $type, array $parame = [])
    {
        $page = '';
        $namePath = '';
        $data = 'id=' . $id . '&pid=' . $uid;
        switch ($type) {
            case 1:
                $page = 'pages/activity/goods_combination_details/index';
                $namePath = 'combination_' . $id . '_' . $uid . '.jpg';
                break;
            case 2:
                $page = 'pages/activity/goods_seckill_details/index';
                $namePath = 'seckill_' . $id . '_' . $uid . '.jpg';
                if (isset($parame['stop_time']) && $parame['stop_time']) {
                    $data .= '&time=' . $parame['stop_time'];
                    $namePath = $parame['stop_time'] . $namePath;
                }
                break;
        }
        if (!$page || !$namePath) {
            return false;
        }
        try {
            $imageInfo = SystemAttachment::getInfo($namePath, 'name');
            $siteUrl = sys_config('site_url');
            if (!$imageInfo) {
                $res = MiniProgramService::qrcodeService()->appCodeUnlimit($page, $data, 280);
                if (!$res) return false;
                $uploadType = (int)sys_config('upload_type', 1);
                $upload = UploadService::init();
                $res = $upload->to('routine/product')->validate()->stream($res, $namePath);
                if ($res === false) {
                    return false;
                }
                $imageInfo = $upload->getUploadInfo();
                $imageInfo['image_type'] = $uploadType;
                if ($imageInfo['image_type'] == 1) $remoteImage = UtilService::remoteImage($siteUrl . $imageInfo['dir']);
                else $remoteImage = UtilService::remoteImage($imageInfo['dir']);
                if (!$remoteImage['status']) return false;
                SystemAttachment::attachmentAdd($imageInfo['name'], $imageInfo['size'], $imageInfo['type'], $imageInfo['dir'], $imageInfo['thumb_path'], 1, $imageInfo['image_type'], $imageInfo['time'], 2);
                $url = $imageInfo['dir'];
            } else $url = $imageInfo['att_dir'];
            if ($imageInfo['image_type'] == 1) $url = $siteUrl . $url;
            return $url;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
