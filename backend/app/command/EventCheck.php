<?php
namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * 事件注册一致性校验命令
 *
 * 静态扫描全库事件触发面（event() / Event::trigger() / Event::until() 等静态字符串 key），
 * 与事件注册面（app/event.php + 各模块 event.php 的 listen 键）双向比对：
 * - 孤儿事件（触发但未注册）→ error 级，退出码 1，可接入 CI / 发布流程拦截
 * - 悬空注册（注册但无静态触发）→ 白名单内的为已知情况（框架内部触发 / 待接线断链），
 *   白名单外的输出警告，提示补接线或清理死注册
 *
 * 用法：php think event:check
 */
class EventCheck extends Command
{
    /** 触发面扫描目录（相对项目根目录，vendor / runtime / public 不扫） */
    protected array $scanDirs = ['app', 'crmeb', 'modules', 'driver', 'extend', 'config', 'route'];

    /**
     * 合法悬空白名单：注册但无静态触发的 key => 原因
     * 两种情况：① 框架 vendor 内部触发（静态扫不到，如 HttpEnd）；② 断链待接线（监听器就位、触发方未落地，保留注册单独立项接线）
     * @var array<string, string>
     */
    protected array $danglingWhitelist = [
        // think 框架内部触发（Http 请求结束），app/event.php 订阅 HttpEndListener
        'HttpEnd' => '框架内部触发（vendor/topthink/framework/src/think/Http.php）',

        // 断链待接线：监听器功能完整但触发方未落地（P5 校验发现，单独立项补接线）
        'OrderComplete'                   => '待接线：订单完成（中台状态推进 + 生命周期日志 + 扩展表推进）',
        'OrderPriceChange'                => '待接线：订单改价（扩展表实付金额同步 + 改价通知）',
        'OrderRefundCreateAfter'          => '待接线：退款单创建后（扩展表退款中标记 + 申请通知）',
        'OrderRefundSuccess'              => '待接线：退款成功（中台/扩展表退款状态 + 退款成功通知）',
        'OrderRefundRefuse'               => '待接线：退款拒绝（扩展表恢复原状态 + 拒绝通知）',
        'OrderRefundCancelAfter'          => '待接线：退款取消（扩展表恢复原状态）',
        'OrderCenterBusinessStatusResolve'=> '待接线：拼团订单中台业务状态解析',
        'OrderSpreadBrokerage'            => '待接线：代理等级分销比例调整',
        'OrderDivisionPercent'            => '待接线：分销比例事件',
    ];

    protected function configure()
    {
        $this->setName('event:check')
            ->setDescription('事件注册一致性校验（孤儿事件 / 悬空注册比对）');
    }

    /**
     * 执行校验
     * @param Input $input 输入对象
     * @param Output $output 输出对象
     * @return int 0=通过（悬空仅警告），1=存在孤儿事件
     */
    protected function execute(Input $input, Output $output)
    {
        $root = $this->app->getRootPath();

        // 收集触发面与注册面
        $triggers = $this->collectTriggers($root);
        $registered = $this->collectRegistered($root);

        // 双向比对：触发未注册 = 孤儿（error）；注册未触发 = 悬空（warning / 白名单）
        $orphans = array_diff(array_keys($triggers), array_keys($registered));
        $dangling = array_diff(array_keys($registered), array_keys($triggers));

        $output->writeln('事件注册一致性校验');
        $output->writeln('触发面：' . count($triggers) . ' 个 key / 注册面：' . count($registered) . ' 个 key');
        $output->writeln(str_repeat('-', 72));

        // 孤儿事件：触发但未注册（监听器缺失，事件静默丢失）
        if ($orphans) {
            $output->error('孤儿事件（触发但未注册）' . count($orphans) . ' 个：');
            foreach ($orphans as $key) {
                $output->writeln('  ❌ ' . $key . '（' . count($triggers[$key]) . ' 处触发）');
                foreach (array_slice($triggers[$key], 0, 5) as $loc) {
                    $output->writeln('       ' . $loc);
                }
            }
        } else {
            $output->writeln('✅ 孤儿事件（触发但未注册）：0');
        }

        // 悬空注册：注册但无静态触发
        $whitelisted = array_values(array_intersect($dangling, array_keys($this->danglingWhitelist)));
        $unknownDangling = array_values(array_diff($dangling, array_keys($this->danglingWhitelist)));

        if ($whitelisted) {
            $output->writeln('ℹ️ 白名单悬空（外部触发 / 待接线）' . count($whitelisted) . ' 个：');
            foreach ($whitelisted as $key) {
                $output->writeln('  - ' . $key . '：' . $this->danglingWhitelist[$key]);
            }
        }

        if ($unknownDangling) {
            $output->warning('非白名单悬空注册（待接线或死注册）' . count($unknownDangling) . ' 个：');
            foreach ($unknownDangling as $key) {
                $output->writeln('  ⚠️ ' . $key . ' ← 注册于 ' . implode(', ', $registered[$key]));
            }
        } else {
            $output->writeln('✅ 非白名单悬空注册：0');
        }

        if ($orphans) {
            $output->error('校验未通过：存在孤儿事件，请补注册或修正事件名');
            return 1;
        }

        $output->writeln('✅ 校验通过' . ($unknownDangling ? '（存在悬空警告，请评估接线或清理）' : ''));
        return 0;
    }

    /**
     * 收集触发面：静态扫描源码中的事件触发调用
     * @param string $root 项目根目录
     * @return array<string, array<int, string>> key => 触发位置（相对路径:行号）列表
     */
    protected function collectTriggers(string $root): array
    {
        $triggers = [];
        // 两种触发形式：助手函数 event('Key')；Event 门面/实例的 trigger/until('Key')
        $patterns = [
            '/\bevent\(\s*[\'"]([A-Z][A-Za-z0-9_]*)[\'"]/',
            '/(?:->|::)(?:trigger|until)\(\s*[\'"]([A-Z][A-Za-z0-9_]*)[\'"]/',
        ];

        foreach ($this->scanDirs as $dir) {
            $path = $root . $dir;
            if (!is_dir($path)) {
                continue;
            }
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }
                // 跳过校验器自身：本文件正则/注释含事件触发字面量，会造成自匹配误报
                // （用路径后缀比较，规避 __FILE__ 与迭代器路径形态差异导致比较失效）
                $selfRel = 'app/command/EventCheck.php';
                if (substr(str_replace('\\', '/', $file->getPathname()), -strlen($selfRel)) === $selfRel) {
                    continue;
                }
                $content = file_get_contents($file->getPathname());
                if ($content === false || strpos($content, 'event(') === false && strpos($content, 'trigger(') === false && strpos($content, 'until(') === false) {
                    continue;
                }
                $relPath = str_replace($root, '', $file->getPathname());
                foreach ($patterns as $pattern) {
                    preg_match_all($pattern, $content, $m, PREG_OFFSET_CAPTURE);
                    foreach ($m[1] as $hit) {
                        // 偏移量转行号
                        $line = substr_count($content, "\n", 0, $hit[1]) + 1;
                        $triggers[$hit[0]][] = $relPath . ':' . $line;
                    }
                }
            }
        }
        return $triggers;
    }

    /**
     * 收集注册面：app/event.php + 多应用 event.php + 各模块 event.php 的 listen 键
     * @param string $root 项目根目录
     * @return array<string, array<int, string>> key => 注册文件（相对路径）列表
     */
    protected function collectRegistered(string $root): array
    {
        $registered = [];
        // glob 兜底三级深度：一级（agent 等）、二级（product/normal 等）、三级（预留）
        $globs = [
            'app/event.php',
            'app/*/event.php',
            'modules/*/event.php',
            'modules/*/*/event.php',
            'modules/*/*/*/event.php',
        ];
        foreach ($globs as $glob) {
            foreach (glob($root . $glob) ?: [] as $file) {
                // event.php 均为纯数组 return，include 无副作用（::class 字面量不触发自动加载）
                $arr = include $file;
                if (!is_array($arr) || empty($arr['listen']) || !is_array($arr['listen'])) {
                    continue;
                }
                $relPath = str_replace($root, '', $file);
                foreach (array_keys($arr['listen']) as $key) {
                    if (is_string($key)) {
                        $registered[$key][] = $relPath;
                    }
                }
            }
        }
        return $registered;
    }
}
