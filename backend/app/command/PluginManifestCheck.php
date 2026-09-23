<?php
declare(strict_types=1);

namespace app\command;

use app\services\system\module\ModuleInstaller;
use crmeb\plugin\PluginManifestRepository;
use crmeb\plugin\PluginDependencyResolver;
use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * 全量插件清单与复合身份一致性检查，不访问数据库。
 */
class PluginManifestCheck extends Command
{
    protected function configure(): void
    {
        $this->setName('plugin:manifest-check')
            ->setDescription('检查 plugin.json、复合身份和 Installer 元数据一致性');
    }

    protected function execute(Input $input, Output $output): int
    {
        try {
            $repository = new PluginManifestRepository(app()->getRootPath());
            $manifests = $repository->all();
            /** @var ModuleInstaller $manager */
            $manager = app()->make(ModuleInstaller::class);
            $installers = $manager->getInstallers();
            $dependencyErrors = (new PluginDependencyResolver($repository))->validateGraph();
        } catch (\Throwable $e) {
            $output->writeln('<error>插件清单检查失败：' . $e->getMessage() . '</error>');
            return 1;
        }

        $errors = [];
        array_push($errors, ...$dependencyErrors);
        array_push($errors, ...$this->validateDiyComponents($manifests, app()->getRootPath()));
        foreach ($installers as $id => $installer) {
            $manifest = $manifests[$id] ?? null;
            if (!$manifest) {
                $errors[] = "{$id}: Installer 没有对应清单";
                continue;
            }
            $expectedInstallerKey = $manifest->identity->kind === 'driver'
                ? $manifest->identity->canonicalId()
                : $manifest->identity->legacyKey;
            if ($installer->getKey() !== $expectedInstallerKey) {
                $errors[] = sprintf(
                    '%s: Installer key=%s，清单 key=%s',
                    $id,
                    $installer->getKey(),
                    $expectedInstallerKey
                );
            }
            if ($installer->getType() !== $manifest->type) {
                $errors[] = sprintf(
                    '%s: Installer type=%s，清单 type=%s',
                    $id,
                    $installer->getType(),
                    $manifest->type
                );
            }
        }

        $output->writeln(sprintf(
            '插件清单：%d（模块 %d / 驱动 %d），Installer：%d',
            count($manifests),
            count($repository->modules()),
            count($repository->drivers()),
            count($installers)
        ));

        if ($errors !== []) {
            foreach ($errors as $error) {
                $output->writeln('<error>  ✗ ' . $error . '</error>');
            }
            return 1;
        }

        $output->writeln('<info>清单、复合身份和 Installer 元数据一致</info>');
        return 0;
    }

    /**
     * 校验清单 diy.components 声明的 DIY 组件存在于前端组件源码
     *
     * 清单是组件可用性判定的唯一真源（C 端按不可用清单过滤，管理端编辑器按可用白名单
     * 放行），若清单声明了前端不存在的组件名，会导致该声明永不生效。校验规则：
     * 管理端组件按 defineOptions 的 defaultName 匹配，移动端组件按静态 name 匹配；
     * 管理端组件还须带 pluginComponent: true 标记（编辑器据此识别插件归属并按白名单
     * 放行），缺标记的组件被视为核心组件恒显示，插件不可用时不会隐藏。
     * 前端目录缺失（纯后端部署）时跳过校验。
     *
     * @param array<string, \crmeb\plugin\PluginManifest> $manifests
     * @return string[]
     */
    private function validateDiyComponents(array $manifests, string $rootPath): array
    {
        $declaredComponents = [];
        foreach ($manifests as $manifest) {
            foreach ($manifest->meta['diy']['components'] ?? [] as $component) {
                $name = is_array($component) ? (string)($component['name'] ?? '') : '';
                if ($name !== '') {
                    $declaredComponents[$manifest->identity->legacyKey][$name] = true;
                }
            }
        }
        if ($declaredComponents === []) {
            return [];
        }

        $componentFiles = $this->diyComponentFiles($rootPath);
        if ($componentFiles === []) {
            // 组件源码目录不可达（纯后端部署 / 未挂载前端），无法比对，跳过该校验
            return [];
        }

        // 收集前端组件源码中的组件名（管理端 defaultName / 移动端 name），
        // 管理端同时记录是否带 pluginComponent 标记（插件归属组件必须标记）
        $componentNames = [];
        $adminMarkers = [];
        foreach ($componentFiles as $file => $isAdmin) {
            $content = (string)file_get_contents($file);
            if ($isAdmin) {
                $name = $this->matchOptionValue($content, 'defaultName');
                if ($name !== '') {
                    $adminMarkers[$name] = ($adminMarkers[$name] ?? false) || $this->matchPluginComponentFlag($content);
                }
            } else {
                $name = $this->matchComponentName($content);
            }
            if ($name !== '') {
                $componentNames[$name] = true;
            }
        }

        $errors = [];
        foreach ($declaredComponents as $pluginKey => $names) {
            foreach (array_keys($names) as $name) {
                if (!isset($componentNames[$name])) {
                    $errors[] = sprintf(
                        '清单 %s 声明的 DIY 组件 %s 不存在于前端组件源码（管理端 defaultName / 移动端 name 均未命中）',
                        $pluginKey,
                        $name
                    );
                    continue;
                }
                // 管理端编辑器按 pluginComponent 标记识别插件归属组件，缺标记则恒显示、白名单不生效
                if (isset($adminMarkers[$name]) && !$adminMarkers[$name]) {
                    $errors[] = sprintf(
                        '清单 %s 声明的 DIY 组件 %s 对应的管理端组件缺少 pluginComponent: true 标记，插件不可用时编辑器不会隐藏该组件',
                        $pluginKey,
                        $name
                    );
                }
            }
        }

        return $errors;
    }

    /**
     * 收集 DIY 组件源码文件，值为是否属于管理端组件目录
     *
     * @return array<string, bool>
     */
    private function diyComponentFiles(string $rootPath): array
    {
        $repoRoot = dirname(rtrim($rootPath, DIRECTORY_SEPARATOR));
        $directories = [
            $repoRoot . '/frontend/admin/src/diy/components/mobilePage' => true,
            $repoRoot . '/frontend/uni-app/src/subpackage/diyComponents' => false,
            $repoRoot . '/frontend/uni-app/src/subpackage/diyComponents/productSections' => false,
        ];
        $files = [];
        foreach ($directories as $directory => $isAdmin) {
            if (!is_dir($directory)) {
                continue;
            }
            foreach (glob($directory . '/*.vue') ?: [] as $file) {
                $files[$file] = $isAdmin;
            }
        }
        return $files;
    }

    private function matchOptionValue(string $content, string $key): string
    {
        return preg_match('/' . $key . '\s*:\s*[\'"]([^\'"]+)[\'"]/', $content, $matched) ? $matched[1] : '';
    }

    /**
     * 管理端组件 defineOptions 是否带 pluginComponent: true（插件归属组件标记）
     */
    private function matchPluginComponentFlag(string $content): bool
    {
        return (bool)preg_match('/pluginComponent\s*:\s*true/', $content);
    }

    /**
     * 移动端组件的静态 name 位于 defineOptions 或 export default 选项块内
     */
    private function matchComponentName(string $content): string
    {
        if (preg_match('/defineOptions\s*\(\s*\{[\s\S]{0,300}?name\s*:\s*[\'"]([^\'"]+)[\'"]/', $content, $matched)) {
            return $matched[1];
        }
        if (preg_match('/export\s+default\s*\{[\s\S]{0,300}?name\s*:\s*[\'"]([^\'"]+)[\'"]/', $content, $matched)) {
            return $matched[1];
        }
        return '';
    }
}
