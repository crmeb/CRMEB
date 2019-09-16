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
 * Session Memcache驱动
 */
class Memcache implements SessionHandlerInterface
{
    protected $handler = null;
    protected $config  = [
        'host'       => '127.0.0.1', // memcache主机
        'port'       => 11211, // memcache端口
        'expire'     => 3600, // session有效期
        'timeout'    => 0, // 连接超时时间（单位：毫秒）
        'persistent' => true, // 长连接
        'prefix'     => '', // session name （memcache key前缀）
    ];

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);

        $this->init();
    }

    /**
     * 打开Session
     * @access protected
     */
    protected function init(): bool
    {
        // 检测php环境
        if (!extension_loaded('memcache')) {
            throw new Exception('not support:memcache');
        }

        $this->handler = new \Memcache;

        // 支持集群
        $hosts = (array) $this->config['host'];
        $ports = (array) $this->config['port'];

        if (empty($ports[0])) {
            $ports[0] = 11211;
        }

        // 建立连接
        foreach ($hosts as $i => $host) {
            $port = $ports[$i] ?? $ports[0];
            $this->config['timeout'] > 0 ?
            $this->handler->addServer($host, (int) $port, $this->config['persistent'], 1, $this->config['timeout']) :
            $this->handler->addServer($host, (int) $port, $this->config['persistent'], 1);
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
     * @param  string $data
     * @return array
     */
    public function write(string $sessID, string $data): bool
    {
        return $this->handler->set($this->config['prefix'] . $sessID, $data, 0, $this->config['expire']);
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
