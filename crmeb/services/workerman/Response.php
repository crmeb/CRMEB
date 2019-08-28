<?php


namespace crmeb\services\workerman;


use Workerman\Connection\TcpConnection;

class Response
{
    /**
     * @var TcpConnection
     */
    protected $connection;

    /**
     * 设置用户
     *
     * @param TcpConnection $connection
     * @return $this
     */
    public function connection(TcpConnection $connection)
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * 发送请求
     *
     * @param string $type
     * @param array|null $data
     * @param bool $close
     * @param array $other
     * @return bool|null
     */
    public function send(string $type, ?array $data = null, bool $close = false, array $other = [])
    {
        $this->connection->lastMessageTime = time();
        $res = compact('type');

        if (!is_null($data)) $res['data'] = $data;
        $data = array_merge($res, $other);

        if ($close)
            $data['close'] = true;

        $json = json_encode($data);

        return $close
            ? ($this->connection->close($json) && true)
            : $this->connection->send($json);
    }

    /**
     * 成功
     *
     * @param string $message
     * @param array|null $data
     * @return bool|null
     */
    public function success($type = 'success', ?array $data = null)
    {
        if (is_array($type)) {
            $data = $type;
            $type = 'success';
        }
        return $this->send($type, $data);
    }

    /**
     * 失败
     *
     * @param string $message
     * @param array|null $data
     * @return bool|null
     */
    public function fail($type = 'error', ?array $data = null)
    {
        if (is_array($type)) {
            $data = $type;
            $type = 'error';
        }
        return $this->send($type, $data);
    }

    /**
     * 关闭连接
     *
     * @param string $type
     * @param array|null $data
     * @return bool|null
     */
    public function close($type = 'error', ?array $data = null)
    {
        if (is_array($type)) {
            $data = $type;
            $type = 'error';
        }
        return $this->send($type, $data, true);
    }
}