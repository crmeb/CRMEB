<?php
declare(strict_types=1);

namespace app\adminapi\controller\user\member;

use app\adminapi\controller\AuthController;
use app\services\user\OfflineReceiptServices;
use app\services\other\QrcodeServices;
use crmeb\utils\Canvas;
use think\facade\App;

/**
 * 线下收银控制器
 * 负责线下收银相关的后台管理操作，包括线下收银订单列表、线下付款二维码生成等
 */
class OfflineReceiptController extends AuthController
{
    /**
     * @var OfflineReceiptServices
     */
    protected $services;

    /**
     * 初始化服务层实例
     * OfflineReceiptController constructor.
     * @param App $app
     * @param OfflineReceiptServices $services
     */
    public function __construct(App $app, OfflineReceiptServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取线下收银订单列表
     * 查询线下付款订单记录，支持按订单ID、时间、用户名筛选
     * @return mixed 返回分页后的订单列表数据
     */
    public function scanList()
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
     * 获取线下付款二维码
     * 生成H5和小程序端的线下付款二维码，支持是否合成背景图
     * @return mixed 返回二维码图片路径
     */
    public function offlineScan()
    {
        [$type] = $this->request->getMore([
            ['type', 1]
        ], true);
        $weixinPage = "/pages/annex/offline_pay/index";
        $weixinFileName = "wechat_offline_scan.png";
        /** @var QrcodeServices $QrcodeService */
        $QrcodeService = app()->make(QrcodeServices::class);
        $wechatQrcode = $QrcodeService->getWechatQrcodePath($weixinFileName, $weixinPage, false, false);
        $routineQrcode = $QrcodeService->getRoutineQrcodePath(4, 6, 3, [], false);
        $qrcod = ['wechat' => $wechatQrcode, 'routine' => $routineQrcode];
        $data = [];
        //需要合成背景图
        if ($type) {
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
