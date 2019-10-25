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

namespace think;

/**
 * Session管理类
 * @package think
 */
class Session
{
    /**
     * 配置参数
     * @var array
     */
    protected $config = [];

    /**
     * Session数据
     * @var array
     */
    protected $data = [];

    /**
     * 是否初始化
     * @var bool
     */
    protected $init = null;

    /**
     * 记录Session name
     * @var string
     */
    protected $sessionName = 'PHPSESSID';

    /**
     * 记录Session Id
     * @var string
     */
    protected $sessionId;

    /**
     * Session有效期
     * @var int
     */
    protected $expire = 0;

    /**
     * Request实例
     * @var Request
     */
    protected $request;

    /**
     * Session写入对象
     * @var object
     */
    protected $handler;

    public function __construct(Request $request, array $config = [])
    {
        $this->config  = $config;
        $this->request = $request;
    }

    public static function __make(Request $request, Config $config)
    {
        return new static($request, $config->get('session'));
    }

    /**
     * 配置
     * @access public
     * @param  array $config
     * @return void
     */
    public function setConfig(array $config = []): void
    {
        $this->config = array_merge($this->config, array_change_key_case($config));
    }

    /**
     * 设置数据
     * @access public
     * @param  array $data
     * @return void
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * session初始化
     * @access public
     * @return void
     * @throws \think\Exception
     */
    public function init(): void
    {
        if (!empty($this->config['name'])) {
            $this->sessionName = $this->config['name'];
        }

        if (!empty($this->config['expire'])) {
            $this->expire = $this->config['expire'];
        }

        // 初始化session写入驱动
        $type = !empty($this->config['type']) ? $this->config['type'] : 'File';

        $this->handler = App::factory($type, '\\think\\session\\driver\\', $this->config);

        $this->start();
    }

    /**
     * 设置SessionName
     * @access public
     * @param  string $name session_name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->sessionName = $name;
    }

    /**
     * 获取sessionName
     * @access public
     * @return string
     */
    public function getName(): string
    {
        return $this->sessionName;
    }

    /**
     * session_id设置
     * @access public
     * @param  string $id session_id
     * @return void
     */
    public function setId(string $id): void
    {
        $this->sessionId = $id;
    }

    /**
     * 获取session_id
     * @access public
     * @param  bool $regenerate 不存在是否自动生成
     * @return string
     */
    public function getId(bool $regenerate = true): string
    {
        if ($this->sessionId) {
            return $this->sessionId;
        }

        return $regenerate ? $this->regenerate() : '';
    }

    /**
     * session设置
     * @access public
     * @param  string $name session名称
     * @param  mixed  $value session值
     * @return void
     */
    public function set(string $name, $value): void
    {
        empty($this->init) && $this->init();

        if (strpos($name, '.')) {
            // 二维数组赋值
            list($name1, $name2) = explode('.', $name);

            $this->data[$name1][$name2] = $value;
        } else {
            $this->data[$name] = $value;
        }
    }

    /**
     * session获取
     * @access public
     * @param  string $name session名称
     * @param  mixed  $default 默认值
     * @return mixed
     */
    public function get(string $name = '', $default = null)
    {
        empty($this->init) && $this->init();

        $sessionId = $this->getId();

        return $this->readSession($name, $default);
    }

    /**
     * session获取
     * @access protected
     * @param  string $name session名称
     * @param  mixed  $default 默认值
     * @return mixed
     */
    protected function readSession(string $name = '', $default = null)
    {
        $value = $this->data;

        if ('' != $name) {
            $name = explode('.', $name);

            foreach ($name as $val) {
                if (isset($value[$val])) {
                    $value = $value[$val];
                } else {
                    $value = $default;
                    break;
                }
            }
        }

        return $value;
    }

    /**
     * 删除session数据
     * @access public
     * @param  string $name session名称
     * @return void
     */
    public function delete(string $name): bool
    {
        empty($this->init) && $this->init();

        $sessionId = $this->getId(false);

        if (!$sessionId) {
            return false;
        }

        if (strpos($name, '.')) {
            list($name1, $name2) = explode('.', $name);
            unset($this->data[$name1][$name2]);
        } else {
            unset($this->data[$name]);
        }

        return true;
    }

    /**
     * 保存session数据
     * @access public
     * @return void
     */
    public function save()
    {
        if ($this->handler) {
            $sessionId = $this->getId(false);

            if (!empty($this->data)) {
                $data = $this->serialize($this->data);

                $this->handler->write($sessionId, $data, $this->expire);
            } else {
                $this->handler->delete($sessionId);
            }
        }
    }

    /**
     * 清空session数据
     * @access public
     * @return void
     */
    public function clear(): void
    {
        empty($this->init) && $this->init();

        $sessionId = $this->getId(false);

        if ($sessionId) {
            $this->data = [];
        }
    }

    /**
     * 判断session数据
     * @access public
     * @param  string $name session名称
     * @return bool
     */
    public function has(string $name): bool
    {
        empty($this->init) && $this->init();

        $sessionId = $this->getId(false);

        if ($sessionId) {
            return $this->hasSession($name);
        }

        return false;
    }

    /**
     * 判断session数据
     * @access protected
     * @param  string $name session名称
     * @return bool
     */
    protected function hasSession(string $name): bool
    {
        $value = $this->data ?: [];

        $name = explode('.', $name);

        foreach ($name as $val) {
            if (!isset($value[$val])) {
                return false;
            } else {
                $value = $value[$val];
            }
        }

        return true;
    }

    /**
     * 启动session
     * @access public
     * @return void
     */
    public function start(): void
    {
        $sessionId = $this->getId();

        // 读取缓存数据
        if (empty($this->data)) {
            $data = $this->handler->read($sessionId);

            if (!empty($data)) {
                $this->data = $this->unserialize($data);
            }
        }

        $this->init = true;
    }

    /**
     * 销毁session
     * @access public
     * @return void
     */
    public function destroy(): void
    {
        $sessionId = $this->getId(false);

        if ($sessionId && !empty($this->data)) {
            $this->data = [];
            $this->save();
        }
    }

    /**
     * 重新生成session_id
     * @access protected
     * @param  bool $delete 是否删除关联会话文件
     * @return string
     */
    protected function regenerate(bool $delete = false): string
    {
        if ($delete) {
            $data = $this->data;
            $this->destroy();
            $this->data = $data;
        }

        $sessionId = md5(microtime(true) . uniqid());

        $this->setId($sessionId);
        return $sessionId;
    }

    /**
     * session获取并删除
     * @access public
     * @param  string $name session名称
     * @return mixed
     */
    public function pull(string $name)
    {
        $result = $this->get($name);

        if ($result) {
            $this->delete($name);
            return $result;
        }
    }

    /**
     * session设置 下一次请求有效
     * @access public
     * @param  string $name session名称
     * @param  mixed  $value session值
     * @return void
     */
    public function flash(string $name, $value): void
    {
        $this->set($name, $value);

        if (!$this->has('__flash__.__time__')) {
            $this->set('__flash__.__time__', $this->request->time(true));
        }

        $this->push('__flash__', $name);
    }

    /**
     * 清空当前请求的session数据
     * @access public
     * @return void
     */
    public function flush()
    {
        if (!$this->init) {
            return;
        }

        $items = $this->get('__flash__');

        if (!empty($items)) {
            $time = $items['__time__'];

            if ($this->request->time(true) > $time) {
                unset($items['__time__']);

                foreach ($items as $item) {
                    $this->delete($item);
                }

                $this->set('__flash__', []);
            }
        }
    }

    /**
     * 添加数据到一个session数组
     * @access public
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function push(string $key, $value): void
    {
        $array = $this->get($key);

        if (is_null($array)) {
            $array = [];
        }

        $array[] = $value;

        $this->set($key, $array);
    }

    /**
     * 序列化数据
     * @access protected
     * @param  mixed $data
     * @return string
     */
    protected function serialize($data): string
    {
        $serialize = $this->config['serialize'][0] ?? 'serialize';

        return $serialize($data);
    }

    /**
     * 反序列化数据
     * @access protected
     * @param  string $data
     * @return array
     */
    protected function unserialize(string $data): array
    {
        $unserialize = $this->config['serialize'][1] ?? 'unserialize';

        return (array) $unserialize($data);
    }
}
