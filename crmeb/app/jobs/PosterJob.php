<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\jobs;

use app\services\other\PosterServices;
use app\services\other\QrcodeServices;
use app\services\system\attachment\SystemAttachmentServices;
use crmeb\basic\BaseJobs;
use crmeb\services\app\MiniProgramService;
use app\services\other\UploadService;
use crmeb\traits\QueueTrait;

/**
 * 海报队列
 * Class PosterJob
 * @package crmeb\jobs
 */
class PosterJob extends BaseJobs
{
    use QueueTrait;

    /**
     * 海报生成
     * @param $user
     * @param $isSsl
     * @return bool
     */
    public function spreadPoster($user, $isSsl)
    {
        /** @var SystemAttachmentServices $attachment */
        $attachment = app()->make(SystemAttachmentServices::class);

        /** @var QrcodeServices $qrcodeService */
        $qrcodeService = app()->make(QrcodeServices::class);

        $rootPath = app()->getRootPath();
        try {
            $resRoutine          = true;//小程序
            $resWap              = true;//公众号
            $siteUrl             = sys_config('site_url');
            $routineSpreadBanner = sys_data('routine_spread_banner');
            if (!count($routineSpreadBanner)) return false;
            //小程序
            $name_routine      = $user['uid'] . '_' . $user['is_promoter'] . '_user_routine.jpg';
            $name_wap          = $user['uid'] . '_' . $user['is_promoter'] . '_user_wap.jpg';
            $imageInfo_routine = $attachment->getInfo(['name' => $name_routine]);
            $imageInfo_wap     = $attachment->getInfo(['name' => $name_wap]);
            if (isset($imageInfo_routine['att_dir']) && strstr($imageInfo_routine['att_dir'], 'http') !== false && curl_file_exist($imageInfo_routine['att_dir']) === false) {
                $imageInfo_routine = null;
                $attachment->delete(['name' => $name_routine]);
            }
            if (isset($imageInfo_wap['att_dir']) && strstr($imageInfo_wap['att_dir'], 'http') !== false && curl_file_exist($imageInfo_wap['att_dir']) === false) {
                $imageInfo_wap = null;
                $attachment->delete(['name' => $name_wap]);
            }
            if (!$imageInfo_routine) {
                $resForever = $qrcodeService->getForeverQrcode('spread', $user['uid']);
                $resCode    = MiniProgramService::appCodeUnlimitService($resForever->id, '', 280);
                if ($resCode) {
                    $res = ['res' => $resCode, 'id' => $resForever->id];
                } else {
                    $res = false;
                }
                if (!$res) return false;
                $uploadType = (int)sys_config('upload_type', 1);
                $upload     = UploadService::init();
                $uploadRes  = $upload->to('routine/spread/code')->validate()->setAuthThumb(false)->stream($res['res'], $name_routine);
                if ($uploadRes === false) {
                    return false;
                }
                $imageInfo_routine               = $upload->getUploadInfo();
                $imageInfo_routine['image_type'] = $uploadType;
                $attachment->attachmentAdd($imageInfo_routine['name'], $imageInfo_routine['size'], $imageInfo_routine['type'], $imageInfo_routine['dir'], $imageInfo_routine['thumb_path'], 1, $imageInfo_routine['image_type'], $imageInfo_routine['time'], 2);
                $qrcodeService->setQrcodeFind($res['id'], ['status' => 1, 'url_time' => time(), 'qrcode_url' => $imageInfo_routine['dir']]);
                $urlCode_routine = $imageInfo_routine['dir'];
            } else $urlCode_routine = $imageInfo_routine['att_dir'];
            if ($imageInfo_routine['image_type'] == 1) $urlCode_routine = $siteUrl . $urlCode_routine;
            if (!$imageInfo_wap) {
                $codeUrl       = set_http_type($siteUrl . '?spread=' . $user['uid'], $isSsl ? 0 : 1);//二维码链接
                $imageInfo_wap = PosterServices::getQRCodePath($codeUrl, $name_wap);
                if (is_string($imageInfo_wap)) return false;
                $attachment->attachmentAdd($imageInfo_wap['name'], $imageInfo_wap['size'], $imageInfo_wap['type'], $imageInfo_wap['dir'], $imageInfo_wap['thumb_path'], 1, $imageInfo_wap['image_type'], $imageInfo_wap['time'], 2);
                $urlCode_wap = $imageInfo_wap['dir'];
            } else $urlCode_wap = $imageInfo_wap['att_dir'];
            if ($imageInfo_wap['image_type'] == 1) $urlCode_wap = $siteUrl . $urlCode_wap;
            $siteUrl  = set_http_type($siteUrl, $isSsl ? 0 : 1);
            $filelink = [
                'Bold'   => 'public\statics\font\Alibaba-PuHuiTi-Regular.otf',
                'Normal' => 'public\statics\font\Alibaba-PuHuiTi-Regular.otf',
            ];
            if (!file_exists($filelink['Bold'])) return false;
            if (!file_exists($filelink['Normal'])) return false;
            foreach ($routineSpreadBanner as $key => &$item) {
                $posterInfo_routine = '海报生成失败:(';
                $config             = array(
                    'image'      => array(
                        array(
                            'url'     => $urlCode_routine,     //二维码资源
                            'stream'  => 0,
                            'left'    => 114,
                            'top'     => 790,
                            'right'   => 0,
                            'bottom'  => 0,
                            'width'   => 120,
                            'height'  => 120,
                            'opacity' => 100
                        )
                    ),
                    'text'       => array(
                        array(
                            'text'      => $user['nickname'],
                            'left'      => 250,
                            'top'       => 840,
                            'fontPath'  => $rootPath . $filelink['Bold'],     //字体文件
                            'fontSize'  => 16,             //字号
                            'fontColor' => '40,40,40',       //字体颜色
                            'angle'     => 0,
                        ),
                        array(
                            'text'      => '邀请您加入' . sys_config('site_name'),
                            'left'      => 250,
                            'top'       => 880,
                            'fontPath'  => $rootPath . $filelink['Normal'],     //字体文件
                            'fontSize'  => 16,             //字号
                            'fontColor' => '40,40,40',       //字体颜色
                            'angle'     => 0,
                        )
                    ),
                    'background' => $item['pic']
                );
                $resRoutine         = $resRoutine && $posterInfo_routine = PosterServices::setSharePoster($config, 'routine/spread/poster', $user['uid'] . '_' . $user['is_promoter'] . '_user_routine_poster_' . $key . '.jpg');
                if (!is_array($posterInfo_routine)) return false;
                $attachment->attachmentAdd($posterInfo_routine['name'], $posterInfo_routine['size'], $posterInfo_routine['type'], $posterInfo_routine['dir'], $posterInfo_routine['thumb_path'], 1, $posterInfo_routine['image_type'], $posterInfo_routine['time'], 2);
                if ($resRoutine) {
                    if ($posterInfo_routine['image_type'] == 1)
                        $item['poster'] = $siteUrl . $posterInfo_routine['dir'];
                    else
                        $item['poster'] = set_http_type($posterInfo_routine['dir'], $isSsl ? 0 : 1);
                    $item['poster'] = str_replace('\\', '/', $item['poster']);
                }
            }
            foreach ($routineSpreadBanner as $key => &$item) {
                $posterInfo_wap = '海报生成失败:(';
                $config         = array(
                    'image'      => array(
                        array(
                            'url'     => $urlCode_wap,     //二维码资源
                            'stream'  => 0,
                            'left'    => 114,
                            'top'     => 790,
                            'right'   => 0,
                            'bottom'  => 0,
                            'width'   => 120,
                            'height'  => 120,
                            'opacity' => 100
                        )
                    ),
                    'text'       => array(
                        array(
                            'text'      => $user['nickname'],
                            'left'      => 250,
                            'top'       => 840,
                            'fontPath'  => $rootPath . $filelink['Bold'],     //字体文件
                            'fontSize'  => 16,             //字号
                            'fontColor' => '40,40,40',       //字体颜色
                            'angle'     => 0,
                        ),
                        array(
                            'text'      => '邀请您加入' . sys_config('site_name'),
                            'left'      => 250,
                            'top'       => 880,
                            'fontPath'  => $rootPath . $filelink['Normal'],     //字体文件
                            'fontSize'  => 16,             //字号
                            'fontColor' => '40,40,40',       //字体颜色
                            'angle'     => 0,
                        )
                    ),
                    'background' => $item['pic']
                );
                $resWap         = $resWap && $posterInfo_wap = PosterServices::setSharePoster($config, 'wap/spread/poster', $user['uid'] . '_' . $user['is_promoter'] . '_user_wap_poster_' . $key . '.jpg');
                if (!is_array($posterInfo_wap)) return false;
                $attachment->attachmentAdd($posterInfo_wap['name'], $posterInfo_wap['size'], $posterInfo_wap['type'], $posterInfo_wap['dir'], $posterInfo_wap['thumb_path'], 1, $posterInfo_wap['image_type'], $posterInfo_wap['time'], 2);
                if ($resWap) {
                    if ($posterInfo_wap['image_type'] == 1)
                        $item['wap_poster'] = $siteUrl . $posterInfo_wap['thumb_path'];
                    else
                        $item['wap_poster'] = set_http_type($posterInfo_wap['thumb_path'], 1);
                }
            }
            if ($resRoutine && $resWap) return true;
            else return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
