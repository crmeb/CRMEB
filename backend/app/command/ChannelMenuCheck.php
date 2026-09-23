<?php
namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Db;

/**
 * 渠道模块菜单挂载校验命令
 *
 * 模拟渠道模块（app / wechat_mp / wechat_mini）的"全新安装"菜单写入流程，
 * 校验各渠道一级菜单是否正确挂载到系统"应用"菜单下（pid = 应用菜单 id）。
 *
 * 实现要点：
 * - 全程在事务内执行，结束自动回滚，不污染现有数据（安全可重复运行）
 * - 先删除各渠道现有菜单树，模拟"全新安装"的干净环境
 * - 对每个渠道实例化 Installer，通过反射调用 protected 的 insertMenus()/seed()
 *   （install() 含 DDL 建表且内部自带事务，无法在外部事务中整体执行，
 *   而菜单写入只涉及 DML，事务回滚完全安全）
 *
 * 用法：php think channel:menu-check
 * 校验失败退出码 1，可接入 CI
 */
class ChannelMenuCheck extends Command
{
    /**
     * 渠道模块与一级菜单名映射：key（模块目录名） => 一级菜单名
     * @var array<string,string>
     */
    private const CHANNELS = [
        'app'         => 'APP',
        'wechat_mp'   => '公众号',
        'wechat_mini' => '小程序',
    ];

    protected function configure()
    {
        $this->setName('channel:menu-check')
            ->setDescription('模拟渠道模块全新安装，校验一级菜单是否挂载到「应用」菜单下（事务内执行，自动回滚）');
    }

    /**
     * 执行校验
     * @param Input $input 输入对象
     * @param Output $output 输出对象
     * @return int 0=通过，1=存在失败项或异常
     */
    protected function execute(Input $input, Output $output)
    {
        $prefix = (string) config('database.connections.mysql.prefix', 'eb_');

        $output->writeln('模拟渠道模块全新安装 → 校验菜单挂载「应用」菜单');
        $output->writeln(str_repeat('-', 72));

        Db::startTrans();
        try {
            // 1. 模拟"全新安装"：删除各渠道现有菜单树（事务内删除，回滚可恢复）
            $deleted = $this->deleteChannelMenusTree($prefix);
            $output->writeln("清理现有渠道菜单 {$deleted} 条（模拟全新安装环境）");

            // 2. 按渠道逐个执行安装（insertMenus + seed，均为 DML）
            foreach (self::CHANNELS as $key => $menuName) {
                $class = "\\modules\\channel\\{$key}\\Installer";
                if (!class_exists($class)) {
                    throw new \RuntimeException("渠道模块 Installer 不存在：{$class}");
                }
                $installer    = new $class();
                $menuCount    = $this->callProtected($installer, 'insertMenus');
                $seedCount    = $this->callProtected($installer, 'seed');
                $output->writeln("  ✓ {$key}：insertMenus={$menuCount} / seed={$seedCount}");
            }

            // 3. 校验各渠道一级菜单是否挂载到「应用」菜单下
            $appMenuId = (int) Db::table($prefix . 'system_menus')
                ->where('menu_name', '应用')
                ->where('is_del', 0)
                ->value('id');
            if (!$appMenuId) {
                throw new \RuntimeException('未找到系统「应用」菜单');
            }
            $output->writeln('「应用」菜单 id = ' . $appMenuId);
            $output->writeln(str_repeat('-', 72));

            $failures = [];
            foreach (self::CHANNELS as $key => $menuName) {
                $menu = Db::table($prefix . 'system_menus')
                    ->where('menu_name', $menuName)
                    ->where('is_del', 0)
                    ->find();

                // 校验点 1：一级菜单必须已创建
                if (!$menu) {
                    $failures[] = "[{$key}] 一级菜单「{$menuName}」未创建";
                    continue;
                }
                // 校验点 2：pid 必须等于「应用」菜单 id
                if ((int) $menu['pid'] !== $appMenuId) {
                    $failures[] = "[{$key}] 「{$menuName}」(id={$menu['id']}) pid={$menu['pid']}，"
                        . "应为 {$appMenuId}（应用）";
                }
                // 校验点 3：一级菜单下必须有直接子菜单（完整菜单树）
                $children = (int) Db::table($prefix . 'system_menus')
                    ->where('pid', $menu['id'])
                    ->where('is_del', 0)
                    ->count();
                if ($children === 0) {
                    $failures[] = "[{$key}] 「{$menuName}」(id={$menu['id']}) 下无子菜单";
                }

                $output->writeln(sprintf('  %-10s %-8s pid=%-6d 子菜单=%d',
                    $key, $menuName, (int) $menu['pid'], $children));
            }

            // 校验点 4：无孤儿菜单（pid 指向不存在的菜单）——以「应用」子树为范围
            $orphans = Db::query(
                "SELECT COUNT(*) AS c FROM {$prefix}system_menus m "
                . "LEFT JOIN {$prefix}system_menus p ON m.pid = p.id "
                . "WHERE m.pid <> 0 AND p.id IS NULL AND m.is_del = 0"
            );
            $orphanCount = (int) ($orphans[0]['c'] ?? 0);
            if ($orphanCount > 0) {
                $failures[] = "存在 {$orphanCount} 条孤儿菜单（pid 指向不存在的菜单）";
            }
            $output->writeln('孤儿菜单：' . $orphanCount . ' 条');

            Db::rollback(); // 恢复现场
        } catch (\Throwable $e) {
            Db::rollback();
            $output->error('校验异常（已回滚）：' . $e->getMessage());
            return 1;
        }

        // 4. 汇总结果
        if (!empty($failures)) {
            foreach ($failures as $msg) {
                $output->writeln('  ❌ ' . $msg);
            }
            $output->error('校验未通过：渠道菜单未正确挂载到「应用」菜单下');
            $output->writeln('（测试数据已回滚，不影响现有数据）');
            return 1;
        }

        $output->writeln('✅ 各渠道菜单均正确挂载到「应用」菜单下');
        $output->writeln('（测试数据已回滚，不影响现有数据）');
        return 0;
    }

    /**
     * 递归删除各渠道的一级菜单及其全部子孙菜单
     * @param string $prefix 表前缀
     * @return int 删除条数
     */
    private function deleteChannelMenusTree(string $prefix): int
    {
        // 收集各渠道一级菜单 id
        $rootIds = Db::table($prefix . 'system_menus')
            ->whereIn('menu_name', array_values(self::CHANNELS))
            ->where('is_del', 0)
            ->column('id');

        // 递归收集全部子孙 id
        $ids     = $rootIds;
        $pending = $rootIds;
        while (!empty($pending)) {
            $children = Db::table($prefix . 'system_menus')
                ->whereIn('pid', $pending)
                ->column('id');
            $pending = $children;
            $ids     = array_merge($ids, $children);
        }

        if (empty($ids)) {
            return 0;
        }
        return Db::table($prefix . 'system_menus')->whereIn('id', $ids)->delete();
    }

    /**
     * 反射调用 Installer 的 protected 方法（insertMenus / seed）
     * @param object $obj 安装器实例
     * @param string $method 方法名
     * @return int 方法返回的计数
     */
    private function callProtected(object $obj, string $method): int
    {
        $ref = new \ReflectionMethod($obj, $method);
        $ref->setAccessible(true);
        return (int) $ref->invoke($obj);
    }
}
