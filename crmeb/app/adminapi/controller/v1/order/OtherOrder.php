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
namespace app\adminapi\controller\v1\order;

use app\adminapi\controller\AuthController;
use app\services\order\OtherOrderServices;
use app\services\other\QrcodeServices;
use crmeb\utils\Canvas;
use think\facade\App;

/**
 * 线下收银
 * Class OtherOrder
 * @package app\adminapi\controller\v1\order
 */
class OtherOrder extends AuthController
{
    /**
     * OtherOrder constructor.
     * @param App $app
     * @param OtherOrderServices $service
     */
    public function __construct(App $app, OtherOrderServices $service)
    {
        parent::__construct($app);
        $this->services = $service;
    }

    /**
     * 线下收银订单列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function scan_list()
    {
        $where = $this->request->getMore([
            ['order_id', ''],
            ['add_time', ''],
            ['name', ''],
            ['page', 1],
            ['limit', 20],
        ]);
        $data = $this->services->getScanOrderList($where);
        return app('json')->success($data);
    }

    /**
     * 线下收银二维码
     * @return mixed
     * @throws \Exception
     */
    public function offline_scan()
    {
        [$type] = $this->request->getMore([
            ['type', 1]
        ], true);
        //生成h5地址
        $weixinPage = "/pages/annex/offline_pay/index";
        $weixinFileName = "wechat_offline_scan.png";
        /** @var QrcodeServices $QrcodeService */
        $QrcodeService = app()->make(QrcodeServices::class);
        $wechatQrcode = $QrcodeService->getWechatQrcodePath($weixinFileName, $weixinPage, false, false);
        //生成小程序地址
        $routineQrcode = $QrcodeService->getRoutineQrcodePath(4, 6, 3, [], false);
        $qrcod = ['wechat' => $wechatQrcode, 'routine' => $routineQrcode];
        $data = [];
        if ($type) {
            //生成画布
            $canvas = Canvas::instance();
            $path = 'uploads/offline/';
            $imageType = 'jpg';
            $siteUrl = sys_config('site_url');
            $canvas->setImageUrl(public_path().'statics/qrcode/offlines.jpg')->setImageHeight(730)->setImageWidth(500)->pushImageValue();
            foreach ($qrcod as $k => $v) {
                if ($v) {
                    $name = 'offline_' . $k;
                    $canvas->setImageUrl($v)->setImageHeight(344)->setImageWidth(344)->setImageLeft(76)->setImageTop(120)->pushImageValue();
                    $image = $canvas->setFileName($name)->setImageType($imageType)->setPath($path)->setBackgroundWidth(500)->setBackgroundHeight(720)->starDrawChart();
                    $data[$k] = $image ? $siteUrl . '/' . $image : '';
                } else {
                    $data[$k] = "";
                }

            }
        } else {
            $data = ['wechat' => $wechatQrcode, 'routine' => $routineQrcode];
        }
        return app('json')->success($data);
    }
}
