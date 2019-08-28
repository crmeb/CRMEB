<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\session\driver;

use think\contract\SessionHandlerInterface;
use think\Exception;

/**
 * Session Memcached驱动
 */
class Memcached implements SessionHandlerInterface
{
    protected $handler = null;
    protected $config  = [
        'host'     => '127.0.0.1', // memcache主机
        'port'     => 11211, // memcache端口
        'expire'   => 3600, // session有效期
        'timeout'  => 0, // 连接超时时间（单位：毫秒）
        'prefix'   => '', // session name （memcache key前缀）
        'username' => '', //账号
        'password' => '', //密码
    ];

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);

        $this->init();
    }

    /**
     * Session初始化
     * @access protected
     * @return bool
     */
    protected function init(): bool
    {
        // 检测php环境
        if (!extension_loaded('memcached')) {
            throw new Exception('not support:memcached');
        }

        $this->handler = new \Memcached;

        // 设置连接超时时间（单位：毫秒）
        if ($this->config['timeout'] > 0) {
            $this->handler->setOption(\Memcached::OPT_CONNECT_TIMEOUT, $this->config['timeout']);
        }

        // 支持集群
        $hosts = (array) $this->config['host'];
        $ports = (array) $this->config['port'];

        if (empty($ports[0])) {
            $ports[0] = 11211;
        }

        // 建立连接
        $servers = [];
        foreach ($hosts as $i => $host) {
            $servers[] = [$host, $ports[$i] ?? $ports[0], 1];
        }

        $this->handler->addServers($servers);

        if ('' != $this->config['username']) {
            $this->handler->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
            $this->handler->setSaslAuthData($this->config['username'], $this->config['password']);
        }

        return true;
    }

    /**
     * 读取Session
     * @access public
     * @param  string $sessID
     * @return string
     */
    public function read(string $sessID): string
    {
        return (string) $this->handler->get($this->config['prefix'] . $sessID);
    }

    /**
     * 写入Session
     * @access public
     * @param  string $sessID
     * @param  array  $data
     * @return bool
     */
    public function write(string $sessID, string $data): bool
    {
        return $this->handler->set($this->config['prefix'] . $sessID, $data, $this->config['expire']);
    }

    /**
     * 删除Session
     * @access public
     * @param  string $sessID
     * @return bool
     */
    public function delete(string $sessID): bool
    {
        return $this->handler->delete($this->config['prefix'] . $sessID);
    }

}
