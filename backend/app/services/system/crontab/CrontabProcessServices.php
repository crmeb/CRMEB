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
namespace app\services\system\crontab;

use think\facade\Cache;
use think\facade\Db;
use think\facade\Log;

/**
 * 定时任务子进程派发服务
 *
 * 背景：`php think timer` 为单进程 Workerman（count=1），全部定时任务共享同一个事件循环，
 * 原实现中任务闭包在事件循环内同步执行，任一耗时任务都会阻塞其余任务的准时触发与心跳刷新。
 * 本服务通过 pcntl_fork 将任务执行体派发到独立子进程：父进程（事件循环）派发后立即返回，
 * 实现「调度归主进程、执行归子进程」的隔离；无 pcntl 扩展环境自动降级为同步执行。
 *
 * 隔离机制：
 *  - 防重入：同一任务上一次执行未结束时跳过本次触发（flock 文件锁，父进程持有、回收时释放）；
 *  - 并发上限：运行中子进程达到上限后本次触发丢弃并记录日志，等待下轮调度；
 *  - 超时看护：子进程内 pcntl_alarm 强制终止超时任务（如自定义 eval 代码死循环）；
 *  - 连接重置：fork 复制的 MySQL 连接在子进程内强制重建，避免父子进程共用同一连接导致协议错乱。
 *
 * @author CRMEB Team <admin@crmeb.com>
 * @date 2026/09/07
 */
class CrontabProcessServices
{
    /**
     * 最大并发子进程数，防止任务堆积导致资源耗尽
     * @var int
     */
    protected const MAX_CONCURRENT = 5;

    /**
     * 单任务最大执行时长（秒），超时由 SIGALRM 强制终止子进程
     * @var int
     */
    protected const TIMEOUT_SECONDS = 600;

    /**
     * 防重入锁文件目录
     * @var string
     */
    protected $lockDir;

    /**
     * 当前运行中的子进程数
     * @var int
     */
    protected $running = 0;

    /**
     * 运行中子进程 pid => 任务标识 映射，用于回收时释放对应锁
     * @var array
     */
    protected $pidMarks = [];

    /**
     * 任务标识 => flock 文件句柄映射（锁由父进程持有，子进程结束在 SIGCHLD 回收中释放）
     * @var array
     */
    protected $lockHandles = [];

    public function __construct()
    {
        $this->lockDir = runtime_path() . 'timer_lock';
        if (!is_dir($this->lockDir) && !mkdir($this->lockDir, 0755, true) && !is_dir($this->lockDir)) {
            throw new \RuntimeException('定时任务锁目录创建失败:' . $this->lockDir);
        }
        $this->registerSigChldHandler();
    }

    /**
     * 当前环境是否支持子进程派发
     * @return bool
     */
    public function isForkSupported(): bool
    {
        return function_exists('pcntl_fork');
    }

    /**
     * 注册 SIGCHLD 处理器：非阻塞回收子进程、递减并发计数、释放防重入锁
     * @return void
     */
    protected function registerSigChldHandler(): void
    {
        if (!function_exists('pcntl_signal')) {
            return;
        }
        pcntl_async_signals(true);
        pcntl_signal(SIGCHLD, function () {
            while (($pid = pcntl_waitpid(-1, $status, WNOHANG)) > 0) {
                $this->running = max(0, $this->running - 1);
                $mark = $this->pidMarks[$pid] ?? null;
                if ($mark !== null && isset($this->lockHandles[$mark])) {
                    flock($this->lockHandles[$mark], LOCK_UN);
                    fclose($this->lockHandles[$mark]);
                    unset($this->lockHandles[$mark], $this->pidMarks[$pid]);
                }
                // 业务异常已在执行闭包内记录，此处仅捕获子进程兜底异常/超时/信号终止
                if (pcntl_wifsignaled($status)) {
                    Log::warning('定时任务子进程被信号终止,信号:' . pcntl_wtermsig($status) . ',任务标识:' . ($mark ?? (string)$pid));
                } elseif (pcntl_wexitstatus($status) !== 0) {
                    Log::warning('定时任务子进程异常退出,退出码:' . pcntl_wexitstatus($status) . ',任务标识:' . ($mark ?? (string)$pid));
                }
            }
        });
    }

    /**
     * 派发任务到独立子进程执行，立即返回不阻塞事件循环
     *
     * @param string $mark 任务标识（防重入锁 key，自定义任务场景为 customTimer_{id}）
     * @param \Closure $execute 任务执行体（在子进程中运行，内部已自带业务异常记录）
     * @return bool true=已派发/已同步执行；false=并发满载、防重入跳过或 fork 失败（本次触发丢弃，等待下轮调度）
     */
    public function dispatch(string $mark, \Closure $execute): bool
    {
        if (!$this->isForkSupported()) {
            // 无 pcntl 扩展（如 Windows/受限容器）降级为同步执行，保持功能可用
            $execute();
            return true;
        }
        if ($this->running >= self::MAX_CONCURRENT) {
            $this->crontabLog('定时任务并发已满(' . self::MAX_CONCURRENT . ')，跳过本次触发,当前并发:' . $this->running . ',任务标识:' . $mark);
            return false;
        }
        // 防重入：同一任务上一次尚未结束则跳过本次触发，避免「每隔 N 秒」型长任务堆叠执行
        $handle = fopen($this->lockDir . DIRECTORY_SEPARATOR . $this->lockKey($mark) . '.lock', 'c');
        if (!$handle || !flock($handle, LOCK_EX | LOCK_NB)) {
            if (is_resource($handle)) {
                fclose($handle);
            }
            $this->crontabLog('定时任务上一次执行未结束，跳过本次触发,任务标识:' . $mark);
            return false;
        }
        $pid = pcntl_fork();
        if ($pid === -1) {
            flock($handle, LOCK_UN);
            fclose($handle);
            Log::error('定时任务子进程创建失败,任务标识:' . $mark);
            return false;
        }
        if ($pid === 0) {
            // 子进程：执行完毕必须 exit 结束进程，绝不进入 Workerman 事件循环
            $this->childRun($mark, $execute);
            exit(0);
        }
        // 父进程：登记 pid 与锁句柄后立即返回，事件循环继续调度其余任务
        $this->running++;
        $this->pidMarks[$pid] = $mark;
        $this->lockHandles[$mark] = $handle;
        return true;
    }

    /**
     * 子进程执行体：超时看护 -> 重置继承连接 -> 执行任务 -> 结束进程
     *
     * fork 复制了父进程已建立的 MySQL 连接句柄，父子进程共用同一连接会导致协议错乱，
     * 必须先强制重建数据库连接后再执行业务；父进程持有的原连接不受影响。
     *
     * @param string $mark 任务标识
     * @param \Closure $execute 任务执行体
     * @return void
     */
    protected function childRun(string $mark, \Closure $execute): void
    {
        try {
            // 超时看护：SIGALRM 触发后记录日志并以 124 退出码终止，防止死循环类任务常驻
            pcntl_async_signals(true);
            pcntl_signal(SIGALRM, function () use ($mark) {
                Log::error('定时任务执行超时(' . self::TIMEOUT_SECONDS . '秒)被强制终止,任务标识:' . $mark);
                exit(124);
            });
            pcntl_alarm(self::TIMEOUT_SECONDS);

            // 强制重建默认数据库连接，断开 fork 继承的连接句柄（database.break_reconnect 已开启，双保险）
            Db::connect(null, true);
            // 缓存若为 redis 则关闭继承的连接（phpredis 后续命令自动重连；file 等非连接型驱动忽略异常）
            try {
                $store = Cache::store();
                $handler = method_exists($store, 'handler') ? $store->handler() : null;
                if (is_object($handler) && method_exists($handler, 'close')) {
                    $handler->close();
                }
            } catch (\Throwable $e) {
                // 非连接型缓存驱动（file 等），无需处理
            }

            $execute();
            exit(0);
        } catch (\Throwable $e) {
            Log::error('定时任务执行失败,任务标识:' . $mark . ',失败原因:' . $e->getMessage());
            exit(1);
        }
    }

    /**
     * 锁文件名安全化（customTimer_{id} 等场景仅保留字母数字下划线连字符）
     * @param string $mark
     * @return string
     */
    protected function lockKey(string $mark): string
    {
        return preg_replace('/[^A-Za-z0-9_\-]/', '_', $mark) ?: 'unknown';
    }

    /**
     * 定时任务过程日志（受 log.timer_log 开关控制，与 CrontabRunServices 口径一致）
     * @param string $msg
     * @return void
     */
    protected function crontabLog(string $msg): void
    {
        if (config('log.timer_log', false)) {
            Log::write(date('Y-m-d H:i:s') . $msg, 'crontab');
        }
    }
}
