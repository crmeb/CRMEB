<?php


namespace crmeb\services\workerman\chat;


use Channel\Client;
use Channel\Server;
use crmeb\services\workerman\ChannelService;
use crmeb\services\workerman\Response;
use Workerman\Connection\TcpConnection;
use Workerman\Lib\Timer;
use Workerman\Worker;

class ChatService
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
     * @var ChatHandle
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
        $this->handle = new ChatHandle($this);
        $this->response = new Response();
    }

    public function setUser(TcpConnection $connection)
    {
        $this->user[$connection->user->uid] = $connection;
    }

    public function user()
    {
        return $this->user;
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
        if (!$res || !isset($res['type']) || !$res['type'] || $res['type'] == 'ping') return;
        if (!method_exists($this->handle, $res['type'])) return;

        $this->handle->{$res['type']}($connection, $res + ['data' => []], $this->response->connection($connection));
    }


    public function onWorkerStart(Worker $worker)
    {
        ChannelService::connet();

        Client::on('crmeb_chat', function ($eventData) use ($worker) {
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
                if ($time_now - $connection->lastMessageTime > 12) {
                    $this->response->connection($connection)->close('timeout');
                }
            }
        });
    }


    public function onClose(TcpConnection $connection)
    {
        var_dump('close');
        unset($this->connections[$connection->id]);
        if (isset($connection->user->uid)) {
            unset($this->user[$connection->user->uid]);
        }
    }
}