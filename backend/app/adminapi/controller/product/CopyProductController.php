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
namespace app\adminapi\controller\product;

use app\adminapi\controller\AuthController;
use app\services\product\product\CopyProductServices;
use app\services\driver\copyproduct\CopyDriverServices;
use crmeb\services\driver\DriverRegistry;
use crmeb\services\yihaotong\Serve;
use think\facade\App;


/**
 * Class CopyProduct
 * @package app\adminapi\controller\product
 */
class CopyProductController extends AuthController
{
    /**
     * @var CopyProductServices
     */
    protected $services;


    /**
     * CopyProduct constructor.
     * @param App $app
     * @param CopyProductServices $services
     */
    public function __construct(App $app, CopyProductServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取复制商品配置信息
     * 根据 eb_driver 表中启用的驱动返回对应配置
     * @return mixed
     */
    public function getConfig()
    {
        $data = [
            'copy_type' => 2,
            'copy_num' => 0,
            'driver_key' => '',
            'driver_name' => '',
            'platforms' => [],
        ];

        // 从驱动表中获取启用的采集驱动
        $enabledDrivers = app()->make(CopyDriverServices::class)->getEnabledDrivers();
        $enabledDriver = $enabledDrivers[0] ?? null;

        if ($enabledDriver) {
            $data['driver_key'] = $enabledDriver['driver_key'];
            $data['driver_name'] = $enabledDriver['driver_name'];

            if ($enabledDriver['driver_key'] === 'yihaotong') {
                // 一号通驱动：获取剩余采集次数
                $data['copy_type'] = 1;
                try {
                    $info = app()->make(Serve::class, [yihaotong_config()])->getUser();
                } catch (\Throwable $e) {
                    $info = [];
                }
                if ($info) {
                    $data['copy_num'] = $info['copy']['num'] ?? 0;
                }
            } else {
                // 其他驱动（如 copy99api）：不需要次数，copy_type=2 前端显示密钥配置入口
                $data['copy_type'] = 2;
            }
        } else {
            // 无已启用驱动，回退到一号通
            $data['copy_type'] = 1;
            try {
                $info = app()->make(Serve::class, [yihaotong_config()])->getUser();
            } catch (\Throwable $e) {
                $info = [];
            }
            if ($info) {
                $data['copy_num'] = $info['copy']['num'] ?? 0;
            }
        }

        // 支持采集的平台列表由启用驱动声明下发，前端不得写死
        $data['platforms'] = $this->getDriverPlatforms($enabledDriver);

        return app('json')->success($data);
    }

    /**
     * 获取采集驱动声明支持的平台列表
     * 启用驱动优先；无启用驱动或其声明为空时，按注册表顺序（sort）取首个有平台声明的驱动兜底，
     * 全程经 DriverRegistry 分发，不硬编码任何驱动类
     * @param array|null $enabledDriver 启用的驱动记录，可为 null
     * @return array
     */
    protected function getDriverPlatforms(?array $enabledDriver): array
    {
        $candidates = [];
        if ($enabledDriver) {
            $candidates[] = $enabledDriver;
        }
        foreach (DriverRegistry::scan('copyproduct') as $driver) {
            if (!$enabledDriver || ($driver['driver_key'] ?? '') !== $enabledDriver['driver_key']) {
                $candidates[] = $driver;
            }
        }
        foreach ($candidates as $driver) {
            try {
                $driverKey = (string)($driver['driver_key'] ?? '');
                $class = DriverRegistry::getDriverClass('copyproduct', $driverKey);
                if (!$class || !class_exists($class)) {
                    continue;
                }
                // 内置一号通驱动的配置取全局一号通配置，其余驱动取驱动表中的配置字段
                $config = $driverKey === 'yihaotong' ? yihaotong_config() : ($driver['config_fields'] ?? []);
                $platforms = app()->make($class, [$config])->getPlatforms();
                if (is_array($platforms) && $platforms) {
                    return $platforms;
                }
            } catch (\Throwable $e) {
                continue;
            }
        }
        return [];
    }

    /**
     * 复制商品
     * @return mixed
     */
    public function copyProduct()
    {
        list($type, $id, $shopid, $url) = $this->request->postMore([
            ['type', ''],
            ['id', ''],
            ['shopid', ''],
            ['url', '']
        ], true);
        $res = $this->services->copyProduct($type, $id, $shopid, $url);
        return app('json')->success($res);
    }

}
