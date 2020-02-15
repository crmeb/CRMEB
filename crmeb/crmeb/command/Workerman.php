<?php

namespace crmeb\command;

use Channel\Client;
use Channel\Server;
use crmeb\services\workerman\chat\ChatService;
use crmeb\services\workerman\WorkermanService;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use Workerman\Worker;

class Workerman extends Command
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var Worker
     */
    protected $workerServer;

    /**
     * @var Worker
     */
    protected $chatWorkerServer;

    /**
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
            ->addArgument('server', Argument::OPTIONAL, 'admin/chat/channel')
            ->addOption('d', null, Option::VALUE_NONE, 'daemon（守护进程）方式启动')
            ->setDescription('start/stop/restart workerman');
    }

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

        return $server;
    }

    protected function execute(Input $input, Output $output)
    {
        $server = $this->init($input, $output);
        Worker::$pidFile = app()->getRootPath().'workerman.pid';
        if(!$server || $server == 'admin'){
            var_dump('admin');
            //创建 admin 长连接服务
            $this->workerServer = new Worker($this->config['admin']['protocol'] . '://' . $this->config['admin']['ip'] . ':' . $this->config['admin']['port']);
            $this->workerServer->count = $this->config['admin']['serverCount'];
        }

        if(!$server || $server == 'chat') {
            var_dump('chat');
            //创建 h5 chat 长连接服务
            $this->chatWorkerServer = new Worker($this->config['chat']['protocol'] . '://' . $this->config['chat']['ip'] . ':' . $this->config['chat']['port']);
            $this->chatWorkerServer->count = $this->config['chat']['serverCount'];
        }

        if(!$server || $server == 'channel') {
            var_dump('channel');
            //创建内部通讯服务
            $this->channelServer = new Server($this->config['channel']['ip'], $this->config['channel']['port']);
        }
        $this->bindHandle();
        try {
            Worker::runAll();
        } catch (\Exception $e) {
            $output->warning($e->getMessage());
        }
    }

    protected function bindHandle()
    {
        if(!is_null($this->workerServer)){
            $server = new WorkermanService($this->workerServer, $this->channelServer);
            // 连接时回调
            $this->workerServer->onConnect = [$server, 'onConnect'];
            // 收到客户端信息时回调
            $this->workerServer->onMessage = [$server, 'onMessage'];
            // 进程启动后的回调
            $this->workerServer->onWorkerStart = [$server, 'onWorkerStart'];
            // 断开时触发的回调
            $this->workerServer->onClose = [$server, 'onClose'];
        }

        if(!is_null($this->chatWorkerServer)) {
            $chatServer = new ChatService($this->chatWorkerServer, $this->channelServer);
            $this->chatWorkerServer->onConnect = [$chatServer, 'onConnect'];
            $this->chatWorkerServer->onMessage = [$chatServer, 'onMessage'];
            $this->chatWorkerServer->onWorkerStart = [$chatServer, 'onWorkerStart'];
            $this->chatWorkerServer->onClose = [$chatServer, 'onClose'];
        }
    }
}
