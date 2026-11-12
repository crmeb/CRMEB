<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\channel\wechat_mini\controller;

use app\Request;
use app\services\driver\upload\UploadService;
use app\services\other\QrcodeServices;
use app\services\system\attachment\SystemAttachmentServices;
use modules\channel\wechat_mini\sdk\MiniProgramService;

/**
 * 用户推广小程序码
 */
class RoutineCodeController
{
    /**
     * 获取当前用户的推广小程序码
     * @param Request $request
     * @return mixed
     */
    public function spreadCode(Request $request)
    {
        $user = $request->user();
        /** @var SystemAttachmentServices $systemAttachment */
        $systemAttachment = app()->make(SystemAttachmentServices::class);
        $name = $user['uid'] . '_user_routine.jpg';
        $imageInfo = $systemAttachment->getInfo(['real_name' => $name, 'module_type' => 2]);
        if (isset($imageInfo['att_dir']) && strstr($imageInfo['att_dir'], 'http') !== false && curl_file_exist($imageInfo['att_dir']) === false) {
            $imageInfo = null;
            $systemAttachment->delete(['name' => $name]);
        }
        $siteUrl = sys_config('site_url');
        /** @var QrcodeServices $qrCode */
        $qrCode = app()->make(QrcodeServices::class);
        if (!$imageInfo) {
            $resForever = $qrCode->qrCodeForever($user['uid'], 'spread', '', '');
            $resCode = MiniProgramService::appCodeUnlimitService($resForever->id, '', 280);
            if ($resCode) {
                $res = ['res' => $resCode, 'id' => $resForever->id];
            } else {
                $res = false;
            }
            if (!$res) {
                return app('json')->fail('二维码生成失败');
            }
            $upload = UploadService::init();
            $uploadRes = $upload->to('routine/spread/code')->validate()->setAuthThumb(false)->stream($res['res'], $name);
            if ($uploadRes === false) {
                return app('json')->fail($upload->getError());
            }
            $imageInfo = $upload->getUploadInfo();
            $imageInfo['image_type'] = upload_type();
            $systemAttachment->attachmentAdd($imageInfo['name'], $imageInfo['size'], $imageInfo['type'], $imageInfo['dir'], $imageInfo['thumb_path'], 1, $imageInfo['image_type'], $imageInfo['time'], 2);
            $qrCode->setQrcodeFind($res['id'], ['status' => 1, 'url_time' => time(), 'qrcode_url' => $imageInfo['dir']]);
            $urlCode = $imageInfo['dir'];
        } else {
            $urlCode = $imageInfo['att_dir'];
        }
        if ($imageInfo['image_type'] == 1) {
            $urlCode = $siteUrl . $urlCode;
        }
        return app('json')->success(['url' => $urlCode]);
    }
}
