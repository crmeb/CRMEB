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
use think\facade\App;

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
     * 验证 ZIP → 提取到 plugin.json 中 path 字段对应的目录 → 返回插件元数据
     * 例：path="modules/distribution" → 解压到 backend/modules/distribution/
     *
     * @return \think\response\Json
     */
    public function upload()
    {
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

        try {
            // 临时目录放到 public/uploads/ 下，方便统一管理
            $tmpDir = $targetDir . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . '.plugin_tmp_' . uniqid() . DIRECTORY_SEPARATOR;
            if (!is_dir($tmpDir)) {
                mkdir($tmpDir, 0755, true);
            }

            $zipPath = $tmpDir . 'package.zip';
            $file->move($tmpDir, 'package.zip');

            // 解压到临时目录
            $zip = new \ZipArchive();
            if ($zip->open($zipPath) !== true) {
                $this->cleanTempDir($tmpDir);
                return app('json')->fail('无法打开 ZIP 文件，可能已损坏');
            }
            $this->validateZipEntries($zip);
            $zip->extractTo($tmpDir);
            $zip->close();

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

            // 根据 type 字段确定目标父目录
            // type=product/product_type → modules/product/{name}/（商品域：产品类型）
            // type=activity/mode        → modules/trade/{name}/（交易域：交易模式）
            // type=user     → modules/user/{name}/
            // type=channel  → modules/channel/{name}/
            // type=driver   → 按 plugin.json 的 path 安装到 driver/{category}/{name}/
            //                 （驱动发现服务只扫描 driver/pay|sms|upload|express|logistics|
            //                  invoice|email|bot|copyproduct|printer 目录，
            //                  安装到 modules/drivers 将导致驱动无法被扫描发现）
            // 其他类型      → modules/{name}/
            $typeDirMap = [
            'product'      => 'modules' . DIRECTORY_SEPARATOR . 'product',
            'product_type' => 'modules' . DIRECTORY_SEPARATOR . 'product',
            'activity'     => 'modules' . DIRECTORY_SEPARATOR . 'trade',
            'mode'         => 'modules' . DIRECTORY_SEPARATOR . 'trade',
            'user'         => 'modules' . DIRECTORY_SEPARATOR . 'user',
            'channel'      => 'modules' . DIRECTORY_SEPARATOR . 'channel',
            'marketing'    => 'modules' . DIRECTORY_SEPARATOR . 'marketing',
        ];

            $type = $meta['type'];
            // 从 path 中提取插件目录名（最后一段）
            $pluginName = basename($meta['path']);

            if ($type === 'driver') {
                // 驱动插件：path 形如 driver/{category}/{name}，必须安装到对应驱动目录
                // 命名空间与 autoload（driver\ → driver/）及各 DriverServices 扫描路径保持一致
                $driverPath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, trim((string)$meta['path'], '/'));
                $segments = explode(DIRECTORY_SEPARATOR, $driverPath);
                if (count($segments) !== 3 || $segments[0] !== 'driver'
                    || in_array('..', $segments, true) || in_array('.', $segments, true)) {
                    $this->cleanTempDir($tmpDir);
                    return app('json')->fail('驱动插件 path 格式错误，应为 driver/{category}/{name}（如 driver/sms/yunxin）');
                }
                // 驱动类别须有对应的发现服务，否则装完也不会被扫描到
                $allowedCategories = ['pay', 'sms', 'upload', 'express', 'logistics', 'invoice', 'email', 'bot', 'copyproduct', 'printer'];
                if (!in_array($segments[1], $allowedCategories, true)) {
                    $this->cleanTempDir($tmpDir);
                    return app('json')->fail('不支持的驱动类别「' . $segments[1] . '」，支持：' . implode('/', $allowedCategories));
                }
                $relativePath = $driverPath;
            } elseif (isset($typeDirMap[$type])) {
                $relativePath = $typeDirMap[$type] . DIRECTORY_SEPARATOR . $pluginName;
            } else {
                $relativePath = 'modules' . DIRECTORY_SEPARATOR . $pluginName;
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
     * 解压前验证所有 ZIP 条目，阻止目录穿越、绝对路径、符号链接和解压炸弹。
     */
    protected function validateZipEntries(\ZipArchive $zip): void
    {
        if ($zip->numFiles < 1 || $zip->numFiles > 2000) {
            throw new \RuntimeException('ZIP 文件数量必须在 1 到 2000 之间');
        }

        $totalSize = 0;
        $manifestCount = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if (!is_array($stat) || !isset($stat['name'])) {
                throw new \RuntimeException('ZIP 条目信息损坏');
            }
            $name = str_replace('\\', '/', (string) $stat['name']);
            $segments = explode('/', $name);
            if ($name === '' || str_contains($name, "\0") || str_starts_with($name, '/')
                || preg_match('/^[A-Za-z]:\//', $name) || in_array('..', $segments, true)) {
                throw new \RuntimeException("ZIP 包含非法路径: {$name}");
            }
            if (basename($name) === 'plugin.json') {
                $manifestCount++;
                if (count($segments) > 2) {
                    throw new \RuntimeException('plugin.json 只能位于 ZIP 根目录或一级插件目录');
                }
            }

            $totalSize += (int) ($stat['size'] ?? 0);
            if ($totalSize > 150 * 1024 * 1024) {
                throw new \RuntimeException('ZIP 解压后总体积不能超过 150MB');
            }

            $attributes = 0;
            $opsys = 0;
            if ($zip->getExternalAttributesIndex($i, $opsys, $attributes)) {
                $fileType = ($attributes >> 16) & 0170000;
                if ($fileType === 0120000) {
                    throw new \RuntimeException("ZIP 不允许包含符号链接: {$name}");
                }
            }
        }
        if ($manifestCount !== 1) {
            throw new \RuntimeException('ZIP 包必须且只能包含一个 plugin.json');
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
