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

namespace app\adminapi\controller\setting;

use app\adminapi\controller\AuthController;
use app\services\system\module\ModuleInstaller;
use app\services\system\module\CreateModuleInstallLogTable;
use think\facade\App;

/**
 * 模块安装管理控制器
 * 管理产品类型、营销活动和独立业务模块（如 agent 分销）的安装/卸载
 * Class ModuleController
 * @package app\adminapi\controller\setting
 */
class ModuleController extends AuthController
{
    /**
     * @var ModuleInstaller
     */
    protected $services;

    /**
     * @param App $app
     * @param ModuleInstaller $services
     */
    public function __construct(App $app, ModuleInstaller $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 初始化
     */
    protected function initialize()
    {
        parent::initialize();
        // 确保安装日志表存在
        $migrate = new CreateModuleInstallLogTable();
        $migrate->up();
    }

    /**
     * 获取所有模块列表及安装状态
     * @return \think\response\Json
     */
    public function list()
    {
        $type = $this->request->param('type', '');
        if ($type) {
            $list = $this->services->getStatusListByType($type);
        } else {
            $list = $this->services->getStatusList();
        }
        return app('json')->success($list);
    }

    /**
     * 获取产品类型模块列表
     * @return \think\response\Json
     */
    public function productTypes()
    {
        $list = $this->services->getStatusListByType('product_type');
        return app('json')->success($list);
    }

    /**
     * 获取营销活动模块列表
     * @return \think\response\Json
     */
    public function activities()
    {
        $list = $this->services->getStatusListByType('activity');
        return app('json')->success($list);
    }

    /**
     * 获取独立业务模块列表（如 agent 分销、hotel 酒店等）
     * @return \think\response\Json
     */
    public function businessModules()
    {
        $list = $this->services->getStatusListByType('business');
        return app('json')->success($list);
    }

    /**
     * 安装模块
     * @param string $moduleKey 模块标识，如 product_type/hotel、activity/seckill、agent
     * @return \think\response\Json
     */
    public function install()
    {
        $moduleKey = $this->request->param('module_key', '');
        if (!$moduleKey) {
            return app('json')->fail('请传入模块标识 module_key');
        }

        try {
            $result = $this->services->install($moduleKey);
            return app('json')->success('安装成功', $result);
        } catch (\Throwable $e) {
            return app('json')->fail('安装失败: ' . $e->getMessage());
        }
    }

    /**
     * 卸载模块
     * @return \think\response\Json
     */
    public function uninstall()
    {
        $moduleKey = $this->request->param('module_key', '');
        if (!$moduleKey) {
            return app('json')->fail('请传入模块标识 module_key');
        }

        try {
            $result = $this->services->uninstall($moduleKey);
            return app('json')->success('卸载成功', $result);
        } catch (\Throwable $e) {
            return app('json')->fail('卸载失败: ' . $e->getMessage());
        }
    }

    /**
     * 启用模块
     * @return \think\response\Json
     */
    public function enable()
    {
        $moduleKey = $this->request->param('module_key', '');
        if (!$moduleKey) {
            return app('json')->fail('请传入模块标识 module_key');
        }

        try {
            $result = $this->services->enable($moduleKey);
            return app('json')->success('启用成功', $result);
        } catch (\Throwable $e) {
            return app('json')->fail('启用失败: ' . $e->getMessage());
        }
    }

    /**
     * 禁用模块
     * @return \think\response\Json
     */
    public function disable()
    {
        $moduleKey = $this->request->param('module_key', '');
        if (!$moduleKey) {
            return app('json')->fail('请传入模块标识 module_key');
        }

        try {
            $result = $this->services->disable($moduleKey);
            return app('json')->success('禁用成功', $result);
        } catch (\Throwable $e) {
            return app('json')->fail('禁用失败: ' . $e->getMessage());
        }
    }
}
