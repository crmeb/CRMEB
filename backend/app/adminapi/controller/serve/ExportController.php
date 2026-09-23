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

namespace app\adminapi\controller\serve;


use app\adminapi\controller\AuthController;
use app\services\driver\express\ExpressConfigServices;
use app\services\shipping\ExpressServices;
use crmeb\services\logistics\Express;
use app\services\driver\express\ExpressDriverServices;
use crmeb\services\yihaotong\Serve;
use think\facade\App;

/**
 * 一号通平台物流服务
 * Class Export
 * @package app\adminapi\controller\serve
 */
class ExportController extends AuthController
{
    /**
     * @var ExpressServices
     */
    protected $services;



    /**
     * Export constructor.
     * @param App $app
     * @param ExpressServices $services
     */
    public function __construct(App $app, ExpressServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 物流公司
     * @return mixed
     */
    public function getExportAll()
    {
        return app('json')->success($this->services->expressList());
    }

    /**
     *
     * 获取面单信息
     * @param string $com
     * @return mixed
     */
    public function getExportTemp()
    {
        [$com] = $this->request->getMore([
            ['com', ''],
        ], true);
        /** @var ExpressDriverServices $driverServices */
        $driverServices = app()->make(ExpressDriverServices::class);
        return app('json')->success($driverServices->createExpressServiceByCode($com)->temp($com));
    }

    /**
     * 打印电子面单是否开启
     * @return mixed
     */
    public function dumpIsOpen()
    {
        $userInfo = app()->make(Serve::class, [yihaotong_config()])->getUser();
        $res = false;
        if ($userInfo['dump']['open']) {
            /** @var ExpressConfigServices $expressConfig */
            $expressConfig = app()->make(ExpressConfigServices::class);
            $config = $expressConfig->getConfig();
            if ($config['siid'] && $config['com'] && $config['to_name']
                && $config['to_tel'] && $config['to_address']
            ) {
                $res = true;
            }
        }
        return app('json')->success(['isOpen' => $res]);
    }

    /**
     * @return \think\Response
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/15
     */
    public function getShipmentOrderList()
    {
        $where = $this->request->getMore([
            ['page', 1],
            ['limit', 10],
        ]);

        /** @var ExpressDriverServices $driverServices */
        $driverServices = app()->make(ExpressDriverServices::class);
        return app('json')->success($driverServices->createExpressService()->getShipmentOrderList($where));
    }
}
