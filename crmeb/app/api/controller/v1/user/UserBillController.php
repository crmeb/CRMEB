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
namespace app\api\controller\v1\user;


use app\Request;
use app\services\other\PosterServices;
use app\services\other\QrcodeServices;
use app\services\system\attachment\SystemAttachmentServices;
use app\services\system\config\SystemConfigServices;
use app\services\user\UserBillServices;
use app\services\user\UserBrokerageServices;
use app\services\user\UserMoneyServices;
use crmeb\services\app\MiniProgramService;
use app\services\other\UploadService;

/**
 * 账单类
 * Class UserBillController
 * @package app\api\controller\user
 */
class UserBillController
{
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
     * 推广数据    昨天的佣金   累计提现金额  当前佣金
     * @param Request $request
     * @return mixed
     */
    public function commission(Request $request)
    {
        $uid = (int)$request->uid();
        return app('json')->success($this->services->commission($uid));
    }

    /**
     * 推广订单
     * @param Request $request
     * @return mixed
     */
    public function spread_order(Request $request)
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
     * 推广佣金明细
     * @param Request $request
     * @param $type 0 全部  1 消费  2 充值  3 返佣  4 提现
     * @return mixed
     */
    public function spread_commission(Request $request, $type)
    {
        $uid = (int)$request->uid();
        $data = [];
        switch ($type) {
            case 0:
            case 1:
            case 2:
                /** @var UserMoneyServices $moneyService */
                $moneyService = app()->make(UserMoneyServices::class);
                $data = $moneyService->getMoneyList($uid, $type);
                break;
            case 3:
            case 4:
                /** @var UserBrokerageServices $brokerageService */
                $brokerageService = app()->make(UserBrokerageServices::class);
                $data = $brokerageService->getBrokerageList($uid, $type);
                break;
        }
        return app('json')->success($data);
    }

    /**
     * 推广 佣金/提现 总和
     * @param Request $request
     * @param $type 3 佣金  4 提现
     * @return mixed
     */
    public function spread_count(Request $request, $type)
    {
        $uid = (int)$request->uid();
        return app('json')->success(['count' => $this->services->spread_count($uid, $type)]);
    }


    /**
     * 分销二维码海报生成
     * @param Request $request
     * @return mixed
     */
    public function spread_banner(Request $request)
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
        if (!$bannerCount) return app('json')->fail(410166);
        $routineSpreadBanner = [];
        foreach ($spreadBanner as $item) {
            $routineSpreadBanner[] = ['pic' => $item];
        }
        if ($type == 1) {
            $poster = $user['uid'] . '_' . $user['is_promoter'] . '_user_routine_poster_';
        } else {
            $poster = $user['uid'] . '_' . $user['is_promoter'] . '_user_wap_poster_';
        }
        /** @var SystemAttachmentServices $systemAttachment */
        $systemAttachment = app()->make(SystemAttachmentServices::class);
        /** @var QrcodeServices $qrCode */
        $qrCode = app()->make(QrcodeServices::class);
        $count = $systemAttachment->getCount([['name', 'LIKE', "$poster%"]]);
        if ($count) {
            $SpreadBanner = $systemAttachment->getLikeNameList($poster);
            //发生变化 重新生成
            if ($bannerCount != count($SpreadBanner)) {
                $systemAttachment->delete([['name', 'like', "$poster%"]]);
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
                $name = $user['uid'] . '_' . $user['is_promoter'] . '_user_routine.jpg';
                $imageInfo = $systemAttachment->getInfo(['name' => $name]);
                //检测远程文件是否存在
                if (isset($imageInfo['att_dir']) && strstr($imageInfo['att_dir'], 'http') !== false && curl_file_exist($imageInfo['att_dir']) === false) {
                    $imageInfo = null;
                    $systemAttachment->delete(['name' => $name]);
                }
                if (!$imageInfo) {
                    $resForever = $qrCode->qrCodeForever($user['uid'], 'spread', '', '');
                    $resCode = MiniProgramService::appCodeUnlimitService($resForever->id, '', 280);
                    if ($resCode) {
                        $res = ['res' => $resCode, 'id' => $resForever->id];
                    } else {
                        $res = false;
                    }
                    if (!$res) return app('json')->fail(410167);
                    $uploadType = (int)sys_config('upload_type', 1);
                    $upload = UploadService::init();
                    $uploadRes = $upload->to('routine/spread/code')->validate()->setAuthThumb(false)->stream($res['res'], $name);
                    if ($uploadRes === false) {
                        return app('json')->fail($upload->getError());
                    }
                    $imageInfo = $upload->getUploadInfo();
                    $imageInfo['image_type'] = $uploadType;
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
                if (!file_exists($filelink['Bold'])) return app('json')->fail(410168);
                if (!file_exists($filelink['Normal'])) return app('json')->fail(410169);
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
                    $resRoutine = $resRoutine && $posterInfo = PosterServices::setSharePoster($config, 'routine/spread/poster', $user['uid'] . '_' . $user['is_promoter'] . '_user_routine_poster_' . $key . '.jpg');
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
                $name = $user['uid'] . '_' . $user['is_promoter'] . '_user_wap.jpg';
                $imageInfo = $systemAttachment->getInfo(['name' => $name]);
                //检测远程文件是否存在
                if (isset($imageInfo['att_dir']) && strstr($imageInfo['att_dir'], 'http') !== false && curl_file_exist($imageInfo['att_dir']) === false) {
                    $imageInfo = null;
                    $systemAttachment->delete(['name' => $name]);
                }
                if (!$imageInfo) {
                    $codeUrl = set_http_type($siteUrl . '?spread=' . $user['uid'], $request->isSsl() ? 0 : 1);//二维码链接
                    $imageInfo = PosterServices::getQRCodePath($codeUrl, $name);
                    if (is_string($imageInfo)) return app('json')->fail(410167, ['error' => $imageInfo]);
                    $systemAttachment->attachmentAdd($imageInfo['name'], $imageInfo['size'], $imageInfo['type'], $imageInfo['dir'], $imageInfo['thumb_path'], 1, $imageInfo['image_type'], $imageInfo['time'], 2);
                    $urlCode = $imageInfo['dir'];
                } else $urlCode = $imageInfo['att_dir'];
                if ($imageInfo['image_type'] == 1) $urlCode = $siteUrl . $urlCode;
                $siteUrl = set_http_type($siteUrl, $request->isSsl() ? 0 : 1);
                $filelink = [
                    'Bold' => 'statics' . DS . 'font' . DS . 'Alibaba-PuHuiTi-Regular.otf',
                    'Normal' => 'statics' . DS . 'font' . DS . 'Alibaba-PuHuiTi-Regular.otf',
                ];
                if (!file_exists($filelink['Bold'])) return app('json')->fail(410168);
                if (!file_exists($filelink['Normal'])) return app('json')->fail(410169);
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
                    $resWap = $resWap && $posterInfo = PosterServices::setSharePoster($config, 'wap/spread/poster', $user['uid'] . '_' . $user['is_promoter'] . '_user_wap_poster_' . $key . '.jpg');
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
            else return app('json')->fail(410170);
        } catch (\Exception $e) {
            return app('json')->fail(410171, ['line' => $e->getLine(), 'message' => $e->getMessage(), 'file' => $e->getFile()]);
        }
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
        $name = $user['uid'] . '_' . $user['is_promoter'] . '_user_routine.jpg';
        $imageInfo = $systemAttachment->getInfo(['name' => $name]);
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
            $resCode = MiniProgramService::appCodeUnlimitService($resForever->id, '', 280);
            if ($resCode) {
                $res = ['res' => $resCode, 'id' => $resForever->id];
            } else {
                $res = false;
            }
            if (!$res) return app('json')->fail(410167);
            $uploadType = (int)sys_config('upload_type', 1);
            $upload = UploadService::init();
            $uploadRes = $upload->to('routine/spread/code')->validate()->setAuthThumb(false)->stream($res['res'], $name);
            if ($uploadRes === false) {
                return app('json')->fail($upload->getError());
            }
            $imageInfo = $upload->getUploadInfo();
            $imageInfo['image_type'] = $uploadType;
            $systemAttachment->attachmentAdd($imageInfo['name'], $imageInfo['size'], $imageInfo['type'], $imageInfo['dir'], $imageInfo['thumb_path'], 1, $imageInfo['image_type'], $imageInfo['time'], 2);
            $qrCode->setQrcodeFind($res['id'], ['status' => 1, 'url_time' => time(), 'qrcode_url' => $imageInfo['dir']]);
            $urlCode = $imageInfo['dir'];
        } else $urlCode = $imageInfo['att_dir'];
        if ($imageInfo['image_type'] == 1) $urlCode = $siteUrl . $urlCode;
        return app('json')->success(['url' => $urlCode]);
    }

    /**
     * 获取海报详细信息
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSpreadInfo(Request $request)
    {
        /** @var SystemConfigServices $systemConfigServices */
        $systemConfigServices = app()->make(SystemConfigServices::class);
        $spreadBanner = $systemConfigServices->getSpreadBanner() ?? [];
        $bannerCount = count($spreadBanner);
        $routineSpreadBanner = [];
        if ($bannerCount) {
            foreach ($spreadBanner as $item) {
                $routineSpreadBanner[] = ['pic' => $item];
            }
        }

        if (sys_config('share_qrcode', 0) && request()->isWechat()) {
            /** @var QrcodeServices $qrcodeService */
            $qrcodeService = app()->make(QrcodeServices::class);
            $qrcode = $qrcodeService->getTemporaryQrcode('spread', $request->uid())->url;
        } else {
            $qrcode = '';
        }

        return app('json')->success([
            'spread' => $routineSpreadBanner,
            'qrcode' => $qrcode,
            'nickname' => $request->user('nickname'),
            'site_name' => sys_config('site_name')
        ]);
    }

    /**
     * 积分记录
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function integral_list(Request $request)
    {
        $uid = (int)$request->uid();
        $data = $this->services->getIntegralList($uid);
        return app('json')->success($data['list'] ?? []);
    }

    /**
     * 佣金排行
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function brokerage_rank(Request $request)
    {
        $data = $request->getMore([
            ['page', ''],
            ['limit'],
            ['type']
        ]);
        $uid = (int)$request->uid();
        return app('json')->success($this->services->brokerage_rank($uid, $data['type']));
    }

    /**
     * 事业部/代理商推广订单
     * @param Request $request
     * @return mixed
     */
    public function divisionOrder(Request $request)
    {
        $uid = (int)$request->uid();
        return app('json')->success($this->services->divisionOrder($uid));
    }
}
