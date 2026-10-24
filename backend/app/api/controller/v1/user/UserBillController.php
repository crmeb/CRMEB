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
namespace app\api\controller\v1\user;


use app\Request;
use app\services\other\PosterServices;
use app\services\other\QrcodeServices;
use app\services\system\attachment\SystemAttachmentServices;
use app\services\system\config\SystemConfigServices;
use app\services\user\UserBillServices;
use app\services\user\UserMoneyServices;
use app\services\driver\upload\UploadService;

/**
 * 账单类
 * Class UserBillController
 * @package app\api\controller\user
 */
class UserBillController
{
    /**
     * @var UserBillServices
     */
    protected $services;

    /**
     * UserBillController constructor.
     * @param UserBillServices $services
     */
    public function __construct(UserBillServices $services)
    {
        $this->services = $services;
    }


    /**
     * 推广订单
     * @param Request $request
     * @return mixed
     */
    public function spreadOrder(Request $request)
    {
        $orderInfo = $request->postMore([
            ['page', 1],
            ['limit', 20],
            ['category', 'now_money'],
            ['type', 'brokerage'],
        ]);
        $uid = (int)$request->uid();
        return app('json')->success($this->services->spread_order($uid, $orderInfo));
    }




    /**
     * 分销二维码海报生成
     * @param Request $request
     * @return mixed
     */
    public function spreadBanner(Request $request)
    {
        list($type) = $request->getMore([
            ['type', 2],
        ], true);
        $user = $request->user();
        $rootPath = app()->getRootPath();
        /** @var SystemConfigServices $systemConfigServices */
        $systemConfigServices = app()->make(SystemConfigServices::class);
        $spreadBanner = $systemConfigServices->getSpreadBanner() ?? [];
        $bannerCount = count($spreadBanner);
        if (!$bannerCount) return app('json')->fail('暂无海报');
        $routineSpreadBanner = [];
        foreach ($spreadBanner as $item) {
            $routineSpreadBanner[] = ['pic' => $item];
        }
        if ($type == 1) {
            $poster = $user['uid'] . '_user_routine_poster_';
        } else {
            $poster = $user['uid'] . '_user_wap_poster_';
        }
        /** @var SystemAttachmentServices $systemAttachment */
        $systemAttachment = app()->make(SystemAttachmentServices::class);
        /** @var QrcodeServices $qrCode */
        $qrCode = app()->make(QrcodeServices::class);
        $count = $systemAttachment->getCount([['real_name', 'LIKE', "$poster%"], ['module_type', '=', 2]]);
        if ($count) {
            $SpreadBanner = $systemAttachment->getLikeNameList($poster);
            //发生变化 重新生成
            if ($bannerCount != count($SpreadBanner)) {
                $systemAttachment->delete([['real_name', 'like', "$poster%"], ['module_type', '=', 2]]);
            } else {
                $siteUrl = sys_config('site_url');
                $siteUrlHttps = set_http_type($siteUrl, $request->isSsl() ? 0 : 1);
                foreach ($SpreadBanner as &$item) {
                    if ($type == 1) {
                        if ($item['image_type'] == 1)
                            $item['poster'] = $siteUrlHttps . $item['att_dir'];
                        else
                            $item['poster'] = set_http_type($item['att_dir'], $request->isSsl() ? 0 : 1);
                        $item['poster'] = str_replace('\\', '/', $item['poster']);
                    } else {
                        if ($item['image_type'] == 1)
                            $item['wap_poster'] = $siteUrl . $item['att_dir'];
                        else
                            $item['wap_poster'] = set_http_type($item['att_dir'], 1);
                    }
                }
                return app('json')->success($SpreadBanner);
            }
        }
        try {
            $resRoutine = true;//小程序
            $resWap = true;//公众号
            $siteUrl = sys_config('site_url');
            if ($type == 1) {
                //小程序
                $name = $user['uid'] . '_user_routine.jpg';
                $imageInfo = $systemAttachment->getInfo(['real_name' => $name, 'module_type' => 2]);
                //检测远程文件是否存在
                if (isset($imageInfo['att_dir']) && strstr($imageInfo['att_dir'], 'http') !== false && curl_file_exist($imageInfo['att_dir']) === false) {
                    $imageInfo = null;
                    $systemAttachment->delete(['real_name' => $name, 'module_type' => 2]);
                }
                if (!$imageInfo) {
                    $resForever = $qrCode->qrCodeForever($user['uid'], 'spread', '', '');
                    //通过事件代理获取小程序码，避免 app 层直接引用 modules
                    $_mpResults = event('ChannelServiceProxy', ['endpoint' => 'wechat_mini', 'service' => 'MiniProgramService', 'action' => 'appCodeUnlimitService', 'args' => [$resForever->id, '', 280]]);
                    $resCode = (is_array($_mpResults) && $_mpResults[0] !== null) ? $_mpResults[0] : null;
                    if ($resCode) {
                        $res = ['res' => $resCode, 'id' => $resForever->id];
                    } else {
                        $res = false;
                    }
                    if (!$res) return app('json')->fail('二维码生成失败');
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
                } else $urlCode = $imageInfo['att_dir'];
                if ($imageInfo['image_type'] == 1) $urlCode = $siteUrl . $urlCode;
                $siteUrlHttps = set_http_type($siteUrl, $request->isSsl() ? 0 : 1);
                $filelink = [
                    'Bold' => 'statics' . DS . 'font' . DS . 'Alibaba-PuHuiTi-Regular.otf',
                    'Normal' => 'statics' . DS . 'font' . DS . 'Alibaba-PuHuiTi-Regular.otf',
                ];
                if (!file_exists($filelink['Bold'])) return app('json')->fail('缺少字体文件Bold');
                if (!file_exists($filelink['Normal'])) return app('json')->fail('缺少字体文件Normal');
                foreach ($routineSpreadBanner as $key => &$item) {
                    $posterInfo = '海报生成失败:(';
                    $config = array(
                        'image' => array(
                            array(
                                'url' => $urlCode,     //二维码资源
                                'stream' => 0,
                                'left' => 114,
                                'top' => 790,
                                'right' => 0,
                                'bottom' => 0,
                                'width' => 120,
                                'height' => 120,
                                'opacity' => 100
                            )
                        ),
                        'text' => array(
                            array(
                                'text' => $user['nickname'],
                                'left' => 250,
                                'top' => 840,
                                'fontPath' => $rootPath . 'public' . DS . $filelink['Bold'],     //字体文件
                                'fontSize' => 16,             //字号
                                'fontColor' => '40,40,40',       //字体颜色
                                'angle' => 0,
                            ),
                            array(
                                'text' => '邀请您加入' . sys_config('site_name'),
                                'left' => 250,
                                'top' => 880,
                                'fontPath' => $rootPath . 'public' . DS . $filelink['Normal'],     //字体文件
                                'fontSize' => 16,             //字号
                                'fontColor' => '40,40,40',       //字体颜色
                                'angle' => 0,
                            )
                        ),
                        'background' => $item['pic']
                    );
                    $resRoutine = $resRoutine && $posterInfo = PosterServices::setSharePoster($config, 'routine/spread/poster', $user['uid'] . '_user_routine_poster_' . $key . '.jpg');
                    if (!is_array($posterInfo)) return app('json')->fail($posterInfo);
                    $systemAttachment->attachmentAdd($posterInfo['name'], $posterInfo['size'], $posterInfo['type'], $posterInfo['dir'], $posterInfo['thumb_path'], 1, $posterInfo['image_type'], $posterInfo['time'], 2);
                    if ($resRoutine) {
                        if ($posterInfo['image_type'] == 1)
                            $item['poster'] = $siteUrlHttps . $posterInfo['dir'];
                        else
                            $item['poster'] = set_http_type($posterInfo['dir'], $request->isSsl() ? 0 : 1);
                        $item['poster'] = str_replace('\\', '/', $item['poster']);
                    }
                }
            } else if ($type == 2) {
                //公众号
                $name = $user['uid'] . '_user_wap.jpg';
                $imageInfo = $systemAttachment->getInfo(['real_name' => $name, 'module_type' => 2]);
                //检测远程文件是否存在
                if (isset($imageInfo['att_dir']) && strstr($imageInfo['att_dir'], 'http') !== false && curl_file_exist($imageInfo['att_dir']) === false) {
                    $imageInfo = null;
                    $systemAttachment->delete(['real_name' => $name, 'module_type' => 2]);
                }
                if (!$imageInfo) {
                    $codeUrl = set_http_type($siteUrl . '?spread=' . $user['uid'], $request->isSsl() ? 0 : 1);//二维码链接
                    $imageInfo = PosterServices::getQRCodePath($codeUrl, $name);
                    if (is_string($imageInfo)) return app('json')->fail('二维码生成失败', ['error' => $imageInfo]);
                    $systemAttachment->attachmentAdd($imageInfo['name'], $imageInfo['size'], $imageInfo['type'], $imageInfo['dir'], $imageInfo['thumb_path'], 1, $imageInfo['image_type'], $imageInfo['time'], 2);
                    $urlCode = $imageInfo['dir'];
                } else $urlCode = $imageInfo['att_dir'];
                if ($imageInfo['image_type'] == 1) $urlCode = $siteUrl . $urlCode;
                $siteUrl = set_http_type($siteUrl, $request->isSsl() ? 0 : 1);
                $filelink = [
                    'Bold' => 'statics' . DS . 'font' . DS . 'Alibaba-PuHuiTi-Regular.otf',
                    'Normal' => 'statics' . DS . 'font' . DS . 'Alibaba-PuHuiTi-Regular.otf',
                ];
                if (!file_exists($filelink['Bold'])) return app('json')->fail('缺少字体文件Bold');
                if (!file_exists($filelink['Normal'])) return app('json')->fail('缺少字体文件Normal');
                foreach ($routineSpreadBanner as $key => &$item) {
                    $posterInfo = '海报生成失败:(';
                    $config = array(
                        'image' => array(
                            array(
                                'url' => $urlCode,     //二维码资源
                                'stream' => 0,
                                'left' => 114,
                                'top' => 790,
                                'right' => 0,
                                'bottom' => 0,
                                'width' => 120,
                                'height' => 120,
                                'opacity' => 100
                            )
                        ),
                        'text' => array(
                            array(
                                'text' => $user['nickname'],
                                'left' => 250,
                                'top' => 840,
                                'fontPath' => $rootPath . 'public' . DS . $filelink['Bold'],     //字体文件
                                'fontSize' => 16,             //字号
                                'fontColor' => '40,40,40',       //字体颜色
                                'angle' => 0,
                            ),
                            array(
                                'text' => '邀请您加入' . sys_config('site_name'),
                                'left' => 250,
                                'top' => 880,
                                'fontPath' => $rootPath . 'public' . DS . $filelink['Normal'],     //字体文件
                                'fontSize' => 16,             //字号
                                'fontColor' => '40,40,40',       //字体颜色
                                'angle' => 0,
                            )
                        ),
                        'background' => $item['pic']
                    );
                    $resWap = $resWap && $posterInfo = PosterServices::setSharePoster($config, 'wap/spread/poster', $user['uid'] . '_user_wap_poster_' . $key . '.jpg');
                    if (!is_array($posterInfo)) return app('json')->fail($posterInfo);
                    $systemAttachment->attachmentAdd($posterInfo['name'], $posterInfo['size'], $posterInfo['type'], $posterInfo['dir'], $posterInfo['thumb_path'], 1, $posterInfo['image_type'], $posterInfo['time'], 2);
                    if ($resWap) {
                        if ($posterInfo['image_type'] == 1)
                            $item['wap_poster'] = $siteUrl . $posterInfo['thumb_path'];
                        else
                            $item['wap_poster'] = set_http_type($posterInfo['thumb_path'], 1);
                    }
                }
            }
            if ($resRoutine && $resWap) return app('json')->success($routineSpreadBanner);
            else return app('json')->fail('生成图片失败');
        } catch (\Exception $e) {
            return app('json')->fail('生成图片时，系统错误', ['line' => $e->getLine(), 'message' => $e->getMessage(), 'file' => $e->getFile()]);
        }
    }

    /**
     * 分享邀新海报素材
     * 分享邀新只需要海报背景与邀请二维码，不涉及佣金/分销结算数据
     * @param Request $request
     * @return mixed
     */
    public function getSpreadInfo(Request $request)
    {
        /** @var SystemConfigServices $systemConfigServices */
        $systemConfigServices = app()->make(SystemConfigServices::class);
        // 底图优先取「用户注册设置 - 邀新海报背景图」；未配置时回退组合数据里的海报组
        $posterBg = trim((string)sys_config('invite_poster_bg', ''));
        if ($posterBg !== '') {
            $spreadBanner = [set_file_url($posterBg, sys_config('site_url'))];
        } else {
            $spreadBanner = $systemConfigServices->getSpreadBanner() ?? [];
        }
        $routineSpreadBanner = [];
        foreach ($spreadBanner as $item) {
            $routineSpreadBanner[] = ['pic' => $item];
        }
        //公众号环境返回带邀请人标识的临时二维码，其它端由前端自行生成（?spid=uid）
        if (sys_config('share_qrcode', 0) && $request->isWechat()) {
            /** @var QrcodeServices $qrcodeService */
            $qrcodeService = app()->make(QrcodeServices::class);
            $qrcode = $qrcodeService->getTemporaryQrcode('spread', (int)$request->uid())->url;
        } else {
            $qrcode = '';
        }
        return app('json')->success([
            'spread' => $routineSpreadBanner,
            'qrcode' => $qrcode,
            'nickname' => $request->user('nickname'),
            'site_name' => sys_config('site_name'),
        ]);
    }

    /**
     * 获取小程序二维码
     * @param Request $request
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRoutineCode(Request $request)
    {
        $user = $request->user();
        /** @var SystemAttachmentServices $systemAttachment */
        $systemAttachment = app()->make(SystemAttachmentServices::class);
        //小程序
        $name = $user['uid'] . '_user_routine.jpg';
        $imageInfo = $systemAttachment->getInfo(['real_name' => $name, 'module_type' => 2]);
        //检测远程文件是否存在
        if (isset($imageInfo['att_dir']) && strstr($imageInfo['att_dir'], 'http') !== false && curl_file_exist($imageInfo['att_dir']) === false) {
            $imageInfo = null;
            $systemAttachment->delete(['name' => $name]);
        }
        $siteUrl = sys_config('site_url');
        /** @var QrcodeServices $qrCode */
        $qrCode = app()->make(QrcodeServices::class);
        if (!$imageInfo) {
            $resForever = $qrCode->qrCodeForever($user['uid'], 'spread', '', '');
            //通过事件代理获取小程序码，避免 app 层直接引用 modules
            $_mpResults = event('ChannelServiceProxy', ['endpoint' => 'wechat_mini', 'service' => 'MiniProgramService', 'action' => 'appCodeUnlimitService', 'args' => [$resForever->id, '', 280]]);
            $resCode = (is_array($_mpResults) && $_mpResults[0] !== null) ? $_mpResults[0] : null;
            if ($resCode) {
                $res = ['res' => $resCode, 'id' => $resForever->id];
            } else {
                $res = false;
            }
            if (!$res) return app('json')->fail('二维码生成失败');
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
        } else $urlCode = $imageInfo['att_dir'];
        if ($imageInfo['image_type'] == 1) $urlCode = $siteUrl . $urlCode;
        return app('json')->success(['url' => $urlCode]);
    }


    /**
     * 积分记录
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function integralList(Request $request)
    {
        $uid = (int)$request->uid();
        $data = $this->services->getIntegralList($uid);
        return app('json')->success($data['list'] ?? []);
    }

    /**
     * 资金明细（0=全部, 1=消费, 2=充值）
     * @param Request $request
     * @param int $type
     * @return mixed
     */
    public function moneyList(Request $request, $type)
    {
        /** @var UserMoneyServices $moneyServices */
        $moneyServices = app()->make(UserMoneyServices::class);
        return app('json')->success($moneyServices->getMoneyList((int)$request->uid(), (int)$type));
    }

}
