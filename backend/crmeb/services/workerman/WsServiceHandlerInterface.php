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
use Workerman\Worker;

/**
 * 统一长连接网关服务处理器契约
 *
 * 系统所有长链接业务共用同一个对外 WebSocket 端口（统一网关），
 * 客户端通过 URL 参数 ?service=xxx 标识目标业务，网关按标识分发到本契约实现类。
 *
 * 各业务插件通过监听 WebSocketServiceRegister 事件返回实现类的完整类名，
 * 实现类由长连接命令实例化并挂载到网关；插件卸载/禁用后监听器消失，对应 service 自动摘除。
 */
interface WsServiceHandlerInterface
{
    /**
     * 服务唯一标识（即客户端 ?service= 参数值，小写下划线风格）
     * @return string 如 admin、kefu_chat
     */
    public function getServiceKey(): string;

    /**
     * WebSocket 握手完成回调（此时已确定连接归属本服务）
     * @param TcpConnection $connection 连接对象
     */
    public function onConnect(TcpConnection $connection);

    /**
     * 收到客户端消息回调（原始 JSON 字符串，由实现类自行解码与分发）
     * @param TcpConnection $connection 连接对象
     * @param string $res 原始消息内容
     */
    public function onMessage(TcpConnection $connection, string $res);

    /**
     * Worker 进程启动回调（每进程生命周期一次，用于订阅 channel 频道、启动心跳定时器等）
     * @param Worker $worker 当前 Worker 实例
     */
    public function onWorkerStart(Worker $worker);

    /**
     * 连接断开回调
     * @param TcpConnection $connection 连接对象
     */
    public function onClose(TcpConnection $connection);
}
