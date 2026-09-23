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
namespace app\command;

use Channel\Server;
use crmeb\services\workerman\WsGateway;
use crmeb\services\workerman\WsServiceHandlerInterface;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use Workerman\Worker;

/**
 * Workerman 服务命令
 * 启动/停止 统一长连接网关、channel 内部通讯服务
 *
 * 网关模式：系统所有长链接业务共用唯一对外 WebSocket 端口，
 * 客户端以 ?service=xxx 标识业务，网关分发至对应服务处理器；
 * 业务处理器通过 WebSocketServiceRegister 事件动态注册（系统内置与各插件均走该事件）。
 * WSS/TLS 由前置反代（Nginx location /ws）统一终结，本服务始终以 ws 对内提供。
 */
class Workerman extends Command
{
    /**
     * workerman 配置
     * @var array
     */
    protected $config = [];

    /**
     * 统一长连接网关服务实例
     * @var Worker
     */
    protected $gatewayWorkerServer;

    /**
     * channel 内部通讯服务实例
     * @var Server
     */
    protected $channelServer;

    /**
     * @var Input
     */
    public $input;

    /**
     * @var Output
     */
    public $output;

    protected function configure()
    {
        // 指令配置
        $this->setName('workerman')
            ->addArgument('status', Argument::REQUIRED, 'start/stop/reload/status/connections')
            ->addArgument('server', Argument::OPTIONAL, 'gateway/channel（兼容旧值 admin/chat，等同 gateway）')
            ->addOption('d', null, Option::VALUE_NONE, 'daemon（守护进程）方式启动')
            ->setDescription('start/stop/restart workerman');
    }

    /**
     * 初始化命令参数与配置
     * @param Input $input 输入
     * @param Output $output 输出
     * @return string 服务标识：gateway 或 channel
     */
    protected function init(Input $input, Output $output)
    {
        global $argv;
        $argv[1] = $input->getArgument('status') ?: 'start';
        $server = $input->getArgument('server');
        if ($input->hasOption('d')) {
            $argv[2] = '-d';
        } else {
            unset($argv[2]);
        }

        $this->config = config('workerman');

        // 兼容历史调用：admin/chat 已合并进统一网关
        if ($server == 'admin' || $server == 'chat') {
            $server = 'gateway';
        }

        return $server;
    }

    /**
     * 执行命令入口
     * @param Input $input 输入
     * @param Output $output 输出
     */
    protected function execute(Input $input, Output $output)
    {
        $server = $this->init($input, $output);
        Worker::$pidFile = app()->getRootPath() . 'runtime/workerman.pid';
        Worker::$logFile = app()->getRootPath() . 'runtime/workerman.log';

        if (!$server || $server == 'gateway') {
            //创建统一长连接网关服务（所有长链接业务共用此端口）
            $this->gatewayWorkerServer = new Worker(
                $this->config['gateway']['protocol'] . '://' . $this->config['gateway']['ip'] . ':' . $this->config['gateway']['port']
            );
            $this->gatewayWorkerServer->count = $this->config['gateway']['serverCount'];
        }

        if (!$server || $server == 'channel') {
            //创建内部通讯服务
            $this->channelServer = new Server($this->config['channel']['listen_ip'], $this->config['channel']['port']);
        }
        $this->bindHandle();
        try {
            Worker::runAll();
        } catch (\Exception $e) {
            $output->warning($e->getMessage());
        }
    }

    /**
     * 绑定 Workerman 各事件回调
     *
     * 1. 网关服务：
     *    - 触发 WebSocketServiceRegister 事件收集全部长链接服务处理器类名
     *      （app 层内置 admin 服务与各插件均以监听器形式注册，插件卸载/禁用后自动摘除）；
     *    - 实例化处理器并挂载到 WsGateway 分发器，由其按 ?service= 参数路由消息；
     *
     * 2. channel 内部通讯：跨进程推送中转，各服务处理器在自身 onWorkerStart 中订阅对应频道。
     *
     * 通过这种方式，新增长链接业务无需新增端口、无需修改本命令与 Nginx 反代配置。
     */
    protected function bindHandle()
    {
        if (!is_null($this->gatewayWorkerServer)) {
            // 收集所有监听器返回的服务处理器类名（支持单个类名字符串或类名数组）
            $classLists = event('WebSocketServiceRegister', []) ?: [];
            $handlerClasses = [];
            foreach ($classLists as $item) {
                foreach ((array)$item as $class) {
                    if (is_string($class) && class_exists($class)) {
                        $handlerClasses[] = $class;
                    }
                }
            }

            $gateway = new WsGateway();
            foreach ($handlerClasses as $class) {
                /** @var WsServiceHandlerInterface $handler */
                $handler = new $class($this->gatewayWorkerServer);
                $gateway->addHandler($handler);
            }

            // 将网关的四个核心事件绑定到分发器
            // 当 TCP 连接建立时触发（此时 WS 握手未完成）
            $this->gatewayWorkerServer->onConnect = [$gateway, 'onConnect'];
            // 当 WS 握手完成时触发（解析 ?service= 并绑定路由）
            $this->gatewayWorkerServer->onWebSocketConnect = [$gateway, 'onWebSocketConnect'];
            // 当收到客户端发来的消息时触发
            $this->gatewayWorkerServer->onMessage = [$gateway, 'onMessage'];
            // 当 worker 进程启动时触发（每个进程生命周期内仅一次）
            $this->gatewayWorkerServer->onWorkerStart = function (Worker $worker) use ($gateway) {
                $writeHeartbeat = static function () {
                    file_put_contents(app()->getRootPath() . 'runtime/.workerman', time());
                };
                $writeHeartbeat();
                \Workerman\Lib\Timer::add(30, $writeHeartbeat);
                $gateway->onWorkerStart($worker);
            };
            // 当客户端断开连接时触发
            $this->gatewayWorkerServer->onClose = [$gateway, 'onClose'];
        }
    }
}
