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

namespace crmeb\services\workerman;

use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Worker;

/**
 * 统一长连接网关分发器
 *
 * 单一对外 WebSocket 端口承载所有长链接业务：
 * - 客户端在握手 URL 携带 ?service=xxx 标识目标业务；
 * - 网关在 onWebSocketConnect 阶段解析标识、绑定到连接对象并路由至对应服务处理器；
 * - 后续消息与断开事件按连接已绑定的标识透传给对应处理器。
 *
 * 服务处理器通过 WebSocketServiceRegister 事件收集（系统内置服务与各插件均以监听器形式注册），
 * 本类位于 crmeb 核心层，不依赖 app 与 modules 具体实现。
 */
class WsGateway
{
    /**
     * 已注册的服务处理器映射
     * @var array<string, WsServiceHandlerInterface> key 为 getServiceKey() 返回值
     */
    protected array $handlers = [];

    /**
     * 注册服务处理器
     * @param WsServiceHandlerInterface $handler 服务处理器实例
     * @return $this
     */
    public function addHandler(WsServiceHandlerInterface $handler)
    {
        $this->handlers[$handler->getServiceKey()] = $handler;
        return $this;
    }

    /**
     * 获取全部已注册服务处理器
     * @return array<string, WsServiceHandlerInterface>
     */
    public function handlers(): array
    {
        return $this->handlers;
    }

    /**
     * TCP 连接建立回调（此时 WebSocket 握手尚未完成）
     * 仅初始化心跳时间占位，避免半握手的空连接在各服务的超时定时器中触发未定义属性访问
     * @param TcpConnection $connection 连接对象
     */
    public function onConnect(TcpConnection $connection)
    {
        $connection->lastMessageTime = time();
    }

    /**
     * WebSocket 握手完成回调：解析 ?service= 并绑定路由
     *
     * service 缺失或未注册时直接关闭连接（如插件被卸载/禁用后其 service 不存在），
     * 关闭前下发 error 帧便于前端提示。
     *
     * 注意：Workerman v4 中该回调第二参数为 HTTP 原始报文字符串（v5 起才为 Request 对象），
     * 此处自行构造 Request 完成参数解析。
     *
     * @param TcpConnection $connection 连接对象
     * @param string $httpBuffer HTTP 握手原始报文
     */
    public function onWebSocketConnect(TcpConnection $connection, string $httpBuffer)
    {
        $request = new Request($httpBuffer);
        $service = (string)($request->get('service') ?? '');
        if ($service === '' || !isset($this->handlers[$service])) {
            $connection->close(json_encode(['type' => 'error', 'data' => ['msg' => '未知的长连接服务或服务未启用']]));
            return;
        }
        $connection->wsService = $service;
        // 握手完成即视为该服务的有效连接，交给服务处理器接管生命周期
        $this->handlers[$service]->onConnect($connection);
    }

    /**
     * 收到消息：按连接绑定的 service 分发
     * 未完成服务绑定（半握手连接）的消息直接忽略
     * @param TcpConnection $connection 连接对象
     * @param string $res 原始消息内容
     */
    public function onMessage(TcpConnection $connection, string $res)
    {
        if (!isset($connection->wsService) || !isset($this->handlers[$connection->wsService])) {
            return;
        }
        $this->handlers[$connection->wsService]->onMessage($connection, $res);
    }

    /**
     * Worker 进程启动：依次通知所有服务处理器完成各自的 channel 订阅与定时器初始化
     * @param Worker $worker 当前 Worker 实例
     */
    public function onWorkerStart(Worker $worker)
    {
        foreach ($this->handlers as $handler) {
            $handler->onWorkerStart($worker);
        }
    }

    /**
     * 连接断开：按绑定的 service 分发清理逻辑
     * @param TcpConnection $connection 连接对象
     */
    public function onClose(TcpConnection $connection)
    {
        if (!isset($connection->wsService) || !isset($this->handlers[$connection->wsService])) {
            return;
        }
        $this->handlers[$connection->wsService]->onClose($connection);
    }
}
