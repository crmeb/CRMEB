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

namespace app\adminapi\controller\plugin;

use app\adminapi\controller\AuthController;
use app\services\system\module\ModuleInstaller;
use app\services\system\module\CreateModuleInstallLogTable;
use crmeb\plugin\PluginManager;
use crmeb\plugin\PluginManifest;
use crmeb\utils\SafeZip;
use think\facade\App;
use think\facade\Env;

/**
 * 插件管理控制器
 * 管理插件的安装/卸载/启用/禁用/上传
 * Class PluginController
 * @package app\adminapi\controller\plugin
 */
class PluginController extends AuthController
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
        // 确保安装日志表 + enabled 字段存在
        (new CreateModuleInstallLogTable())->up();
        $this->ensureEnabledColumn();
        $this->ensureDriverOwnershipColumns();
    }

    /**
     * 确保 enabled 字段存在
     */
    protected function ensureEnabledColumn(): void
    {
        try {
            $prefix = (string) config('database.connections.mysql.prefix', 'eb_');
            $db = config('database.connections.mysql.database');
            $result = \think\facade\Db::query(
                "SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = 'enabled'",
                [$db, $prefix . 'module_install_log']
            );
            if (empty($result)) {
                \think\facade\Db::execute(
                    "ALTER TABLE `{$prefix}module_install_log` ADD COLUMN `enabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0未启用 1已启用' AFTER `status`"
                );
            }
        } catch (\Throwable $e) {
            // 忽略，可能已存在
        }
    }

    /** 兼容已安装系统：为统一驱动字典补齐运行元数据和插件所有权。 */
    protected function ensureDriverOwnershipColumns(): void
    {
        try {
            $prefix = (string) config('database.connections.mysql.prefix', 'eb_');
            $db = (string) config('database.connections.mysql.database');
            $table = $prefix . 'driver';
            $rows = \think\facade\Db::query(
                'SELECT COLUMN_NAME FROM information_schema.columns'
                . ' WHERE table_schema = ? AND table_name = ?'
                . " AND column_name IN ('namespace', 'owner_plugin_id')",
                [$db, $table]
            );
            $columns = array_column($rows, 'COLUMN_NAME');
            if (!in_array('namespace', $columns, true)) {
                \think\facade\Db::execute(
                    "ALTER TABLE `{$table}` ADD COLUMN `namespace` varchar(160) NOT NULL DEFAULT '' COMMENT '驱动命名空间' AFTER `class_name`"
                );
            }
            if (!in_array('owner_plugin_id', $columns, true)) {
                \think\facade\Db::execute(
                    "ALTER TABLE `{$table}` ADD COLUMN `owner_plugin_id` varchar(96) NOT NULL DEFAULT '' COMMENT '所属驱动插件复合标识' AFTER `namespace`"
                );
            }
            $indexes = \think\facade\Db::query(
                'SELECT INDEX_NAME FROM information_schema.statistics'
                . ' WHERE table_schema = ? AND table_name = ? AND index_name = ?',
                [$db, $table, 'idx_owner_plugin_id']
            );
            if (empty($indexes)) {
                \think\facade\Db::execute(
                    "ALTER TABLE `{$table}` ADD INDEX `idx_owner_plugin_id` (`owner_plugin_id`)"
                );
            }
        } catch (\Throwable $e) {
            // 安装插件时会再给出明确的表结构错误，不在列表接口阶段中断后台。
        }
    }

    /**
     * 上传并安装插件 ZIP 包
     *
     * 接收 multipart/form-data，字段名 plugin_package
     * 验证 ZIP → 按 plugin.json 的 path 落盘 → 返回插件元数据
     * 例：path="modules/service/outapi" → 解压到 backend/modules/service/outapi/
     *
     * @return \think\response\Json
     */
    public function upload()
    {
        if (!Env::get('app_debug', false)) {
            return app('json')->fail('生产环境无法上传插件，如需使用请修改.env文件中app_debug项为true');
        }

        $file = $this->request->file('plugin_package');
        if (!$file) {
            return app('json')->fail('请上传插件安装包（ZIP 格式）');
        }

        // 验证文件类型
        $suffix = strtolower(pathinfo($file->getOriginalName(), PATHINFO_EXTENSION));
        if ($suffix !== 'zip') {
            return app('json')->fail('仅支持 ZIP 格式的插件安装包');
        }
        if ((int) $file->getSize() > 50 * 1024 * 1024) {
            return app('json')->fail('插件安装包不能超过 50MB');
        }

        $targetDir = app()->getRootPath();
        $tmpDir = '';

        try {
            // 临时目录放在 runtime（Web 根外），避免解压物被 HTTP 直接访问执行
            $tmpRoot = $targetDir . 'runtime' . DIRECTORY_SEPARATOR . 'plugin_upload' . DIRECTORY_SEPARATOR;
            if (!is_dir($tmpRoot) && !mkdir($tmpRoot, 0755, true) && !is_dir($tmpRoot)) {
                return app('json')->fail('无法创建插件上传临时目录');
            }
            $tmpDir = $tmpRoot . '.plugin_tmp_' . uniqid('', true) . DIRECTORY_SEPARATOR;
            if (!mkdir($tmpDir, 0755, true) && !is_dir($tmpDir)) {
                return app('json')->fail('无法创建插件上传临时目录');
            }

            $zipPath = $tmpDir . 'package.zip';
            $file->move($tmpDir, 'package.zip');
            SafeZip::extract($zipPath, $tmpDir, ['require_plugin_json' => true]);

            // 查找 plugin.json（可能在根目录或一级子目录）
            $pluginJson = $tmpDir . 'plugin.json';
            if (!is_file($pluginJson)) {
                // 查找一级子目录中的 plugin.json
                $subDirs = glob($tmpDir . '*/plugin.json');
                if (count($subDirs) !== 1) {
                    $this->cleanTempDir($tmpDir);
                    return app('json')->fail('ZIP 包必须且只能包含一个根级插件目录和 plugin.json');
                }
                $pluginJson = $subDirs[0];
            }

            $meta = json_decode(file_get_contents($pluginJson), true);
            if (empty($meta['path']) || empty($meta['type'])) {
                $this->cleanTempDir($tmpDir);
                return app('json')->fail('plugin.json 格式不完整，缺少 path 或 type 字段');
            }

            // 安装目标以 plugin.json 的 path 为准（与 PluginManifest 校验一致），
            // type 仅用于驱动类别与常见域目录约束，禁止再按 type 改写为 modules/{name}。
            // type=product/product_type → path 须落在 modules/product/
            // type=activity/mode        → path 须落在 modules/trade/
            // type=user/channel/marketing → path 须落在同名 modules/{type}/
            // type=driver               → path 须为 driver/{category}/{name}
            // 其他（如 service）         → 允许 modules/{name} 或 modules/{category}/{name}
            $typeDirMap = [
                'product'      => 'modules/product',
                'product_type' => 'modules/product',
                'activity'     => 'modules/trade',
                'mode'         => 'modules/trade',
                'user'         => 'modules/user',
                'channel'      => 'modules/channel',
                'marketing'    => 'modules/marketing',
            ];

            $type = (string) $meta['type'];
            $pathNormalized = trim(str_replace('\\', '/', (string) $meta['path']), '/');
            if (str_contains($pathNormalized, '..')
                || !preg_match('#^(modules|driver)/[A-Za-z0-9_.-]+(?:/[A-Za-z0-9_.-]+){0,2}$#', $pathNormalized)) {
                $this->cleanTempDir($tmpDir);
                return app('json')->fail('插件清单 path 非法: ' . $pathNormalized);
            }

            $pluginName = basename($pathNormalized);
            $relativePath = str_replace('/', DIRECTORY_SEPARATOR, $pathNormalized);

            if ($type === 'driver') {
                // 驱动插件：path 形如 driver/{category}/{name}，必须安装到对应驱动目录
                // 命名空间与 autoload（driver\ → driver/）及各 DriverServices 扫描路径保持一致
                $segments = explode('/', $pathNormalized);
                if (count($segments) !== 3 || $segments[0] !== 'driver') {
                    $this->cleanTempDir($tmpDir);
                    return app('json')->fail('驱动插件 path 格式错误，应为 driver/{category}/{name}（如 driver/sms/yunxin）');
                }
                // 驱动类别须有对应的发现服务，否则装完也不会被扫描到
                $allowedCategories = ['pay', 'sms', 'upload', 'express', 'logistics', 'invoice', 'email', 'bot', 'copyproduct', 'printer'];
                if (!in_array($segments[1], $allowedCategories, true)) {
                    $this->cleanTempDir($tmpDir);
                    return app('json')->fail('不支持的驱动类别「' . $segments[1] . '」，支持：' . implode('/', $allowedCategories));
                }
            } elseif (isset($typeDirMap[$type])) {
                $expectedParent = $typeDirMap[$type];
                if (dirname($pathNormalized) !== $expectedParent) {
                    $this->cleanTempDir($tmpDir);
                    return app('json')->fail("插件 path 与 type 约定目录不一致，期望 {$expectedParent}/{$pluginName}");
                }
            }

            $destDir = $targetDir . $relativePath . DIRECTORY_SEPARATOR;

            if (is_dir($destDir)) {
                // An existing directory is an upgrade candidate. The package
                // must identify exactly the same plugin and carry a higher version.
                $currentManifest = PluginManifest::fromFile($destDir . 'plugin.json', $targetDir);
                $newPath = trim(str_replace('\\', '/', (string) $meta['path']), '/');
                if ($newPath !== trim(str_replace('\\', '/', $relativePath), '/')) {
                    $this->cleanTempDir($tmpDir);
                    return app('json')->fail('插件 path 与安装目标不一致');
                }
                if ((string) ($meta['key'] ?? '') !== $currentManifest->identity->legacyKey
                    || (string) ($meta['type'] ?? '') !== $currentManifest->type) {
                    $this->cleanTempDir($tmpDir);
                    return app('json')->fail('升级包不是当前插件的同一版本线');
                }

                $newVersion = (string) ($meta['version'] ?? '');
                if ($newVersion === '' || !version_compare($newVersion, $currentManifest->version, '>')) {
                    $this->cleanTempDir($tmpDir);
                    return app('json')->fail("新版本 {$newVersion} 必须高于当前版本 {$currentManifest->version}");
                }

                $sourceDir = dirname($pluginJson);
                $migration = $this->services->runUpgradeMigrations(
                    $currentManifest->identity->canonicalId(),
                    $sourceDir . DIRECTORY_SEPARATOR . 'upgrades',
                    $newVersion
                );

                $backupRoot = $targetDir . 'backup' . DIRECTORY_SEPARATOR . '.plugin_backup' . DIRECTORY_SEPARATOR;
                $backupDir = $backupRoot . basename($relativePath) . '_' . date('YmdHis') . DIRECTORY_SEPARATOR;
                if (!is_dir($backupRoot) && !mkdir($backupRoot, 0755, true) && !is_dir($backupRoot)) {
                    throw new \RuntimeException('无法创建插件备份目录');
                }
                if (!rename($destDir, $backupDir)) {
                    throw new \RuntimeException('无法备份旧插件文件');
                }
                try {
                    $this->copyDir($sourceDir, $destDir);
                    PluginManifest::fromFile($destDir . 'plugin.json', $targetDir);
                } catch (\Throwable $e) {
                    $this->cleanTempDir($destDir);
                    @rename($backupDir, $destDir);
                    throw $e;
                }

                $this->services->updateInstalledVersion($currentManifest->identity->canonicalId(), $newVersion);
                $this->cleanTempDir($tmpDir);
                PluginManager::resetCache();
                \crmeb\plugin\ModuleStateChecker::reload();
                return app('json')->success('插件升级成功', [
                    'action' => 'upgrade',
                    'id' => $currentManifest->identity->canonicalId(),
                    'key' => $meta['key'],
                    'name' => $meta['name'] ?? $currentManifest->name,
                    'from_version' => $migration['from_version'],
                    'version' => $newVersion,
                    'steps' => $migration['steps'],
                ]);
            }

            // 从临时目录复制到目标目录
            $sourceDir = dirname($pluginJson);
            $this->copyDir($sourceDir, $destDir);

            // 复制完成后使用统一清单模型做目录、字段、驱动信息和复合身份校验。
            try {
                $manifest = PluginManifest::fromFile($destDir . 'plugin.json', $targetDir);
            } catch (\Throwable $e) {
                $this->cleanTempDir($destDir);
                throw $e;
            }

            //清理临时目录
            $this->cleanTempDir($tmpDir);

            // 重置 ModuleInstaller 缓存，使新插件可被发现
            PluginManager::resetCache();

            return app('json')->success('插件上传成功', [
                'id'          => $manifest->identity->canonicalId(),
                'key'         => $meta['key'] ?? $relativePath,
                'name'        => $meta['name'] ?? $meta['key'] ?? $pluginName,
                'type'        => $meta['type'],
                'version'     => $meta['version'] ?? '1.0.0',
                'description' => $meta['description'] ?? '',
                'author'      => $meta['author'] ?? '',
            ]);
        } catch (\Throwable $e) {
            $this->cleanTempDir($tmpDir ?? '');
            return app('json')->fail('上传失败: ' . $e->getMessage());
        }
    }

    /**
     * 递归复制目录
     */
    protected function copyDir(string $src, string $dst): void
    {
        if (!is_dir($dst)) {
            mkdir($dst, 0755, true);
        }
        $items = scandir($src);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $s = $src . DIRECTORY_SEPARATOR . $item;
            $d = $dst . DIRECTORY_SEPARATOR . $item;
            if (is_dir($s)) {
                $this->copyDir($s, $d);
            } else {
                copy($s, $d);
            }
        }
    }

    /**
     * 清理临时目录
     *
     * 跳过 __MACOSX 等 macOS ZIP 产物，处理过程中可能存在的断链等情况。
     */
    protected function cleanTempDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = @scandir($dir);
        if ($items === false) {
            return;
        }
        foreach (array_diff($items, ['.', '..']) as $item) {
            // 跳过 macOS 特殊目录（ZIP 产物）
            if ($item === '__MACOSX' || str_starts_with($item, '._')) {
                $this->removeForce($dir . DIRECTORY_SEPARATOR . $item);
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->cleanTempDir($path);
                @rmdir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }

    /**
     * 强制删除文件或目录（处理 macOS 特殊文件/断链）
     */
    protected function removeForce(string $path): void
    {
        if (!file_exists($path) && !is_link($path)) {
            return;
        }
        if (is_dir($path) && !is_link($path)) {
            $this->cleanTempDir($path);
            @rmdir($path);
        } else {
            @unlink($path);
        }
    }

    /**
     * 获取所有插件列表及状态
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
     * 安装插件
     * @return \think\response\Json
     */
    public function install()
    {
        $pluginKey = $this->request->param('plugin_key', '');
        if (!$pluginKey) {
            return app('json')->fail('请传入插件标识 plugin_key');
        }

        try {
            $result = $this->services->install($pluginKey);
            return app('json')->success('安装成功', $result);
        } catch (\Throwable $e) {
            return app('json')->fail('安装失败: ' . $e->getMessage());
        }
    }

    /**
     * 卸载插件
     * @return \think\response\Json
     */
    public function uninstall()
    {
        $pluginKey = $this->request->param('plugin_key', '');
        if (!$pluginKey) {
            return app('json')->fail('请传入插件标识 plugin_key');
        }

        try {
            $result = $this->services->uninstall($pluginKey);
            return app('json')->success('卸载成功', $result);
        } catch (\Throwable $e) {
            return app('json')->fail('卸载失败: ' . $e->getMessage());
        }
    }

    /**
     * 启用插件
     * @return \think\response\Json
     */
    public function enable()
    {
        $pluginKey = $this->request->param('plugin_key', '');
        if (!$pluginKey) {
            return app('json')->fail('请传入插件标识 plugin_key');
        }

        try {
            $result = $this->services->enable($pluginKey);
            PluginManager::resetCache();
            return app('json')->success('启用成功', $result);
        } catch (\Throwable $e) {
            return app('json')->fail('启用失败: ' . $e->getMessage());
        }
    }

    /**
     * 禁用插件
     * @return \think\response\Json
     */
    public function disable()
    {
        $pluginKey = $this->request->param('plugin_key', '');
        if (!$pluginKey) {
            return app('json')->fail('请传入插件标识 plugin_key');
        }

        try {
            $result = $this->services->disable($pluginKey);
            PluginManager::resetCache();
            return app('json')->success('禁用成功', $result);
        } catch (\Throwable $e) {
            return app('json')->fail('禁用失败: ' . $e->getMessage());
        }
    }

    /**
     * 刷新插件消息模板（TEMPORARY-PLUGIN-NOTICE-REFRESH：临时功能，后续移除）。
     * 以插件 Installer::getNoticeDefinitions 为准，覆盖式更新该插件的通知/渠道模板。
     * @return \think\response\Json
     */
    public function refreshNotices()
    {
        $pluginKey = $this->request->param('plugin_key', '');
        if (!$pluginKey) {
            return app('json')->fail('请传入插件标识 plugin_key');
        }

        try {
            $result = $this->services->refreshNotices($pluginKey);
            return app('json')->success('刷新成功', $result);
        } catch (\Throwable $e) {
            return app('json')->fail('刷新失败: ' . $e->getMessage());
        }
    }
}
