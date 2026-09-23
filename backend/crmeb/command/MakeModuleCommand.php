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

namespace crmeb\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

/**
 * 模块骨架生成命令
 *
 * 按「零侵入二开」约定生成完整合规模块骨架（plugin.json 清单、App 注册入口、
 * 事件监听表、Installer 安装器、分层目录与 README），生成后开发者只需在骨架内
 * 填业务逻辑，不修改系统表与框架代码。
 *
 * 骨架结构与生命周期约定见 .crmeb/rules/架构规范详解.md 与 .crmeb/rules/插件开发规范.md；
 * 安装、菜单、种子、打包等完整流程见 .crmeb/skills/crmeb-plugin-dev/SKILL.md。
 *
 * 用法：php think make:module demo --label=演示模块 --type=service
 */
class MakeModuleCommand extends Command
{
    /**
     * 骨架分层目录（空目录补 .gitkeep 以便入库）
     */
    private const DIRS = ['config', 'route', 'controller', 'service', 'dao', 'model', 'listener', 'crontab'];

    /**
     * 模块类型选项（与 plugin.json 的 type 字段对应）
     */
    private const TYPES = ['service', 'product', 'trade', 'channel', 'marketing'];

    /**
     * 命令定义
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:module')
            ->addArgument('name', Argument::REQUIRED, '模块名（小写字母/数字/下划线，如 demo）')
            ->addOption('label', null, Option::VALUE_REQUIRED, '模块中文名（展示名，默认为「{Name}模块」）')
            ->addOption('type', null, Option::VALUE_REQUIRED, '模块类型（service/product/trade/channel/marketing，默认 service）')
            ->setDescription('创建零侵入模块骨架（plugin.json + App + event + Installer + 分层目录）');
    }

    /**
     * 执行生成
     * @param Input $input 输入对象
     * @param Output $output 输出对象
     * @return int 0=成功；1=失败
     */
    protected function execute(Input $input, Output $output): int
    {
        $name = (string)$input->getArgument('name');
        if (!preg_match('/^[a-z][a-z0-9_]*$/', $name)) {
            $output->error("模块名 {$name} 不合法：需以小写字母开头，仅含小写字母/数字/下划线");
            return 1;
        }

        $type = (string)($input->getOption('type') ?? 'service');
        if (!in_array($type, self::TYPES, true)) {
            $output->error("模块类型 {$type} 不支持：可选 " . implode(' / ', self::TYPES));
            return 1;
        }

        $label = (string)($input->getOption('label') ?? '');
        if ($label === '') {
            $label = ucfirst($name) . '模块';
        }

        $basePath = $this->app->getRootPath() . 'modules' . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR;
        if (is_dir($basePath)) {
            $output->error("模块 {$name} 已存在: {$basePath}");
            return 1;
        }

        // 变量映射：{name}=模块名 {Name}=首字母大写 {label}=中文名 {type}=类型
        $fill = static fn(string $tpl): string => strtr($tpl, [
            '{name}' => $name,
            '{Name}' => ucfirst($name),
            '{label}' => $label,
            '{type}' => $type,
        ]);

        // 1. 分层目录（空目录补 .gitkeep）
        foreach (self::DIRS as $dir) {
            $path = $basePath . $dir;
            if (!is_dir($path) && !mkdir($path, 0755, true)) {
                $output->error("目录创建失败: {$path}");
                return 1;
            }
            touch($path . DIRECTORY_SEPARATOR . '.gitkeep');
        }

        // 2. 清单与入口文件
        $files = [
            'plugin.json'   => $this->pluginJson(),
            'App.php'       => $this->appPhp(),
            'event.php'     => $this->eventPhp(),
            'Installer.php' => $this->installerPhp(),
            'README.md'     => $this->readmeMd(),
        ];
        foreach ($files as $filename => $template) {
            if (file_put_contents($basePath . $filename, $fill($template)) === false) {
                $output->error("文件写入失败: {$basePath}{$filename}");
                return 1;
            }
        }

        // 3. 完成
        $output->info("模块 {$name}（{$label}，类型 {$type}）创建成功: {$basePath}");
        $output->writeln('已生成:');
        $output->writeln('  ├── plugin.json   模块清单（后台「插件管理」据此安装/卸载）');
        $output->writeln('  ├── App.php       注册入口（register 登记服务 / boot 加载配置与事件）');
        $output->writeln('  ├── event.php     事件监听表（字符串事件 key）');
        $output->writeln('  ├── Installer.php 安装器（生命周期：runMigrate -> insertMenus -> seed）');
        $output->writeln('  └── ' . implode('、', self::DIRS) . '（分层目录）');
        $output->writeln('后续步骤: 在骨架内填业务逻辑 -> 后台插件管理安装 -> 提交前运行 php think arch:check');

        return 0;
    }

    /**
     * plugin.json 模板
     * @return string
     */
    private function pluginJson(): string
    {
        return <<<'TPL'
{
    "key": "{name}",
    "name": "{label}",
    "description": "{label}：一句话说明本模块提供的能力",
    "version": "1.0.0",
    "author": "CRMEB Team",
    "type": "{type}",
    "path": "modules/{name}",
    "default_install": false,
    "require": {
        "crmeb": "^8.0",
        "php": "^8.0"
    }
}

TPL;
    }

    /**
     * App.php 模板（注册入口，对齐模块注册机制）
     * @return string
     */
    private function appPhp(): string
    {
        return <<<'TPL'
<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\{name};

use think\App as ThinkApp;

/**
 * {label}模块注册入口
 *
 * 模块注册机制（详见 .crmeb/rules/架构规范详解.md）：
 * - register 在服务注册阶段调用：登记监听器、注册中心、定时任务等
 * - boot 在应用启动阶段调用：加载模块配置与事件监听表
 */
class App
{
    /**
     * 服务注册：登记本模块的监听器 / 任务 / 驱动等
     * @param ThinkApp $app 应用容器
     * @return void
     */
    public static function register(ThinkApp $app): void
    {
        // TODO: 按需登记本模块服务
    }

    /**
     * 应用启动：加载模块配置与事件监听表
     * @param ThinkApp $app 应用容器
     * @return void
     */
    public static function boot(ThinkApp $app): void
    {
        $eventFile = __DIR__ . '/event.php';
        if (is_file($eventFile)) {
            $event = include $eventFile;
            if (is_array($event)) {
                $app->loadEvent($event);
            }
        }
    }
}

TPL;
    }

    /**
     * event.php 模板（事件监听表）
     * @return string
     */
    private function eventPhp(): string
    {
        return <<<'TPL'
<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
declare(strict_types=1);

/**
 * {label}模块事件监听表
 *
 * - 键为事件名（字符串，不带 Listener 后缀），值为监听器类名数组
 * - 触发统一使用字符串 key：event('XxxEvent', $data)，禁用类常量
 * - 全量事件名清单见 .crmeb/rules/事件规范.md
 */
return [
    'listen' => [],
];

TPL;
    }

    /**
     * Installer.php 模板（安装器，覆写 BaseModuleInstaller 全部抽象方法）
     * @return string
     */
    private function installerPhp(): string
    {
        return <<<'TPL'
<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\{name};

use crmeb\module\BaseModuleInstaller;

/**
 * {label}模块安装器
 *
 * 生命周期（基类 install() 编排）：runMigrate（建表）-> insertMenus（后台菜单，
 * 由 getMenuConfig() 提供）-> seed（种子数据）-> 完成；卸载按 rollbackMigrate
 * 与 cleanData() 逆向清理。本骨架无独立数据表与菜单，仅保持生命周期完整，
 * 按需填充；约定见 .crmeb/rules/插件开发规范.md。
 */
class Installer extends BaseModuleInstaller
{
    /**
     * 插件唯一标识（与 plugin.json 的 key 一致）
     * @return string
     */
    public function getKey(): string
    {
        return '{name}';
    }

    /**
     * 插件展示名称
     * @return string
     */
    public function getName(): string
    {
        return '{label}';
    }

    /**
     * 插件类型
     * @return string
     */
    public function getType(): string
    {
        return '{type}';
    }

    /**
     * 插件版本
     * @return string
     */
    protected function getVersion(): string
    {
        return '1.0.0';
    }

    /**
     * 安装建表（有独立数据表时在此创建）
     * @return void
     */
    protected function runMigrate(): void
    {
        // TODO: 按需建表
    }

    /**
     * 卸载删表（与 runMigrate 对应，保证卸载可还原）
     * @return void
     */
    protected function rollbackMigrate(): void
    {
        // TODO: 按需删表
    }

    /**
     * 后台菜单配置（返回菜单树数组，基类 insertMenus() 统一插入）
     * @return array
     */
    protected function getMenuConfig(): array
    {
        // TODO: 按需返回菜单配置；无菜单时返回空数组
        return [];
    }
}

TPL;
    }

    /**
     * README.md 模板
     * @return string
     */
    private function readmeMd(): string
    {
        return <<<'TPL'
# {label}（modules/{name}）

类型 `{type}` 的模块骨架，由 `php think make:module` 生成。

## 目录

| 目录 | 说明 |
| --- | --- |
| `plugin.json` | 模块清单，后台「插件管理」据此安装 / 卸载 |
| `App.php` | 注册入口：`register` 登记服务，`boot` 加载配置与事件 |
| `event.php` | 事件监听表（事件 key 使用字符串） |
| `Installer.php` | 安装器生命周期：`runMigrate` -> `insertMenus` -> `seed` |
| `controller/` `service/` `dao/` `model/` | 分层：`route -> Controller -> Services -> Dao -> Model` |
| `listener/` `crontab/` `config/` `route/` | 监听器、定时任务、配置与路由 |

## 约定

- 零侵入：只落本目录与自有表，不修改系统表与框架代码（红线见 `.crmeb/rules/插件开发规范.md`）
- 依赖方向：本模块可调用 `crmeb/`、`app/`，不与其他模块互相引用
- 提交前运行 `php think arch:check` 确认无架构铁律违规

## 后续步骤

1. 填写业务逻辑
2. 后台「插件管理」安装本模块
3. 完整流程（菜单、种子、打包、前端资源）见 `.crmeb/skills/crmeb-plugin-dev/SKILL.md`
TPL;
    }
}
