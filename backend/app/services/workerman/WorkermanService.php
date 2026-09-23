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

namespace app\services\workerman;


use Channel\Client;
use crmeb\services\workerman\ChannelService;
use crmeb\services\workerman\Response;
use crmeb\services\workerman\WsServiceHandlerInterface;
use Workerman\Connection\TcpConnection;
use Workerman\Lib\Timer;
use Workerman\Worker;

/**
 * 管理后台长连接服务（统一网关 admin 服务处理器）
 * 负责管理 admin 端 workerman 连接的生命周期与消息分发，
 * 作为 WsServiceHandlerInterface 实现挂载到统一长连接网关
 */
class WorkermanService implements WsServiceHandlerInterface
{
    /**
     * @var Worker
     */
    protected $worker;

    /**
     * @var TcpConnection[]
     */
    protected $connections = [];

    /**
     * @var TcpConnection[]
     */
    protected $user = [];

    /**
     * @var WorkermanHandle
     */
    protected $handle;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var int
     */
    protected $timer;

    public function __construct(Worker $worker)
    {
        $this->worker = $worker;
        $this->handle = new WorkermanHandle($this);
        $this->response = new Response();
    }

    /**
     * 服务唯一标识（统一网关 ?service= 参数值）
     * @return string
     */
    public function getServiceKey(): string
    {
        return 'admin';
    }

    public function setUser(TcpConnection $connection)
    {
        $this->user[$connection->adminInfo['id']] = $connection;
    }

    public function onConnect(TcpConnection $connection)
    {
        $this->connections[$connection->id] = $connection;
        $connection->lastMessageTime = time();
    }

    public function onMessage(TcpConnection $connection, $res)
    {
        $connection->lastMessageTime = time();
        $res = json_decode($res, true);
        if (!$res || !isset($res['type']) || !$res['type'] || $res['type'] == 'ping') {
            return $this->response->connection($connection)->success('ping', ['now' => time()]);
        }
        if (!method_exists($this->handle, $res['type'])) return;

        $this->handle->{$res['type']}($connection, $res + ['data' => []], $this->response->connection($connection));
    }


    public function onWorkerStart(Worker $worker)
    {
        ChannelService::connet();

        Client::on('crmeb', function ($eventData) use ($worker) {
            if (!isset($eventData['type']) || !$eventData['type']) return;
            $ids = isset($eventData['ids']) && count($eventData['ids']) ? $eventData['ids'] : array_keys($this->user);
            foreach ($ids as $id) {
                if (isset($this->user[$id]))
                    $this->response->connection($this->user[$id])->success($eventData['type'], $eventData['data'] ?? null);
            }
        });

        $this->timer = Timer::add(15, function () use (&$worker) {
            $time_now = time();
            foreach ($worker->connections as $connection) {
                if ($time_now - $connection->lastMessageTime > 120) {
                    $this->response->connection($connection)->close('timeout');
                }
            }
        });
    }


    public function onClose(TcpConnection $connection)
    {
        unset($this->connections[$connection->id]);
    }
}
