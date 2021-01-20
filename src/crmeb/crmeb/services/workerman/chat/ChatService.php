<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\services\workerman\chat;


use app\services\message\service\StoreServiceRecordServices;
use app\services\message\service\StoreServiceServices;
use Channel\Client;
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
     * 在线客服
     * @var TcpConnection[]
     */
    protected $kefuUser = [];

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

    /**
     * 获得当前在线客服
     * @return TcpConnection[]
     */
    public function kefuUser()
    {
        return $this->kefuUser;
    }

    /**
     * 设置当前在线客服
     * @param TcpConnection $connection
     */
    public function setKefuUser(TcpConnection $connection, bool $isUser = true)
    {
        $this->kefuUser[$connection->kefuUser->uid] = $connection;
        if ($isUser) {
            $this->user[$connection->user->uid] = $connection;
        }
    }

    public function user($key = null)
    {
        return $key ? ($this->user[$key] ?? false) : $this->user;
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
        try {
            $this->handle->{$res['type']}($connection, $res + ['data' => []], $this->response->connection($connection));
        } catch (\Throwable $e) {
        }
    }


    public function onWorkerStart(Worker $worker)
    {
        ChannelService::connet();

        Client::on('crmeb_chat', function ($eventData) use ($worker) {
            if (!isset($eventData['type']) || !$eventData['type']) return;
            $ids = isset($eventData['ids']) && count($eventData['ids']) ? $eventData['ids'] : array_keys($this->user);
            $fun = $eventData['fun'] ?? false;
            foreach ($ids as $id) {
                if (isset($this->user[$id])) {
                    if ($fun) {
                        $this->handle->{$eventData['type']}($this->user[$id], $eventData + ['data' => []], $this->response->connection($this->user[$id]));
                    } else {
                        $this->response->connection($this->user[$id])->success($eventData['type'], $eventData['data'] ?? null);
                    }
                }
            }
        });

        $this->timer = Timer::add(15, function () use (&$worker) {
            $time_now = time();
            foreach ($worker->connections as $connection) {
                if ($time_now - $connection->lastMessageTime > 12) {
                    //定时器判断当前用户是否下线
                    if (isset($connection->user->uid) && !isset($connection->user->isTourist)) {
                        /** @var StoreServiceRecordServices $service */
                        $service = app()->make(StoreServiceRecordServices::class);
                        $service->updateRecord(['to_uid' => $connection->user->uid], ['online' => 0]);
                    }
                    $this->response->connection($connection)->close('timeout');
                    //广播给客服谁下线了
                    foreach ($this->kefuUser as $uid => &$conn) {
                        if (isset($connection->user->uid) && $connection->user->uid != $uid) {
                            if (isset($conn->onlineUids) && ($key = array_search($connection->user->uid, $conn->onlineUids)) !== false) {
                                unset($conn->onlineUids[$key]);
                            }
                            $this->response->connection($conn)->send('user_online', ['to_uid' => $connection->user->uid, 'online' => 0]);
                        }
                    }
                }
            }
        });

        Timer::add(2, function () use (&$worker) {
            $uids = [];
            foreach ($this->user() as $uid => $connection) {
                if (!isset($connection->isTourist)) {
                    $uids[] = $uid;
                }
            }
            if ($uids) {
                //除了当前在线的其他全部都下线
                /** @var StoreServiceRecordServices $service */
                $service = app()->make(StoreServiceRecordServices::class);
                $service->updateOnline(['notUid' => $uids], ['online' => 0]);
            }
            $kefuUid = array_keys($this->kefuUser());
            if ($kefuUid) {
                /** @var StoreServiceServices $kefuService */
                $kefuService = app()->make(StoreServiceServices::class);
                $kefuService->updateOnline(['notUid' => $kefuUid], ['online' => 0]);
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
        if (isset($connection->kefuUser->uid)) {
            unset($this->kefuUser[$connection->kefuUser->uid]);
        }
    }
}
