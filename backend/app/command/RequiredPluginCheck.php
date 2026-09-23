<?php
declare(strict_types=1);

namespace app\command;

use app\services\system\module\ModuleInstaller;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

/**
 * 必需插件安装基线检查。
 *
 * manifest-only 模式不访问数据库，适合代码审查和 CI 静态检查；默认模式还会
 * 校验 required=true 的插件是否已经安装并启用。
 */
class RequiredPluginCheck extends Command
{
    protected function configure(): void
    {
        $this->setName('plugin:required-check')
            ->addOption('manifest-only', null, Option::VALUE_NONE, '仅校验清单与 Installer，不访问数据库')
            ->setDescription('检查所有 required=true 插件是否可安装，并验证安装启用状态');
    }

    protected function execute(Input $input, Output $output): int
    {
        /** @var ModuleInstaller $manager */
        $manager = app()->make(ModuleInstaller::class);

        try {
            $required = $manager->getRequiredPlugins();
        } catch (\Throwable $e) {
            $output->writeln('<error>必需插件清单检查失败：' . $e->getMessage() . '</error>');
            return 1;
        }

        if ($required === []) {
            $output->writeln('<error>未声明任何 required=true 的基础插件</error>');
            return 1;
        }

        $output->writeln('必需插件清单：');
        foreach ($required as $key => $meta) {
            $output->writeln(sprintf('  ✓ %s (%s)', $key, $meta['path'] ?? 'unknown'));
        }

        if ((bool) $input->getOption('manifest-only')) {
            $output->writeln('<info>清单与 Installer 检查通过</info>');
            return 0;
        }

        $statuses = [];
        foreach ($manager->getStatusList() as $item) {
            $statuses[$item['id']] = $item;
        }

        $errors = [];
        foreach ($required as $key => $meta) {
            $status = $statuses[$key] ?? null;
            if (!$status || !$status['installed']) {
                $errors[] = "{$key} 未安装";
                continue;
            }
            if (!$status['enabled']) {
                $errors[] = "{$key} 未启用";
            }
        }

        if ($errors !== []) {
            foreach ($errors as $error) {
                $output->writeln('<error>  ✗ ' . $error . '</error>');
            }
            return 1;
        }

        $output->writeln('<info>全部必需插件均已安装并启用</info>');
        return 0;
    }
}
