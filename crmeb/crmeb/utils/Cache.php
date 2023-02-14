<?php
/**
 *  +----------------------------------------------------------------------
 *  | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
 *  +----------------------------------------------------------------------
 *  | Author: CRMEB Team <admin@crmeb.com>
 *  +----------------------------------------------------------------------
 */

namespace crmeb\utils;


use think\cache\TagSet;
use think\facade\Cache as ThinkCache;

/**
 * Class Cache
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2023/2/8
 * @package crmeb\utils
 */
class Cache
{

    protected $key;

    /**
     * @var string
     */
    protected $origin = 'crmebbz';

    /**
     * Cache constructor.
     * @param string $key
     */
    public function __construct(string $key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/2/8
     */
    public function getTagName()
    {
        return $this->origin . ':t:' . $this->key;
    }

    /**
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/2/8
     */
    public function getValueName()
    {
        return $this->origin . ':v:' . $this->key;
    }

    /**
     * @return TagSet
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/2/8
     */
    protected function tag()
    {
        return ThinkCache::tag($this->getTagName());
    }

    /**
     * @param string $name
     * @param $value
     * @param null $expire
     * @return mixed
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/2/8
     */
    public function remember(string $name, $value, $expire = null)
    {
        return $this->tag()->remember($this->getValueName() . ':' . $name, $value, $expire);
    }

    /**
     * @param $name
     * @return bool
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/2/8
     */
    public function delete($name)
    {
        return ThinkCache::delete($this->getValueName() . ':' . $name);
    }

    /**
     * @param $name
     * @return bool
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/2/8
     */
    public function has($name)
    {
        return ThinkCache::has($this->getValueName() . ':' . $name);
    }

    /**
     * @param $name
     * @param null $default
     * @return mixed
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/2/8
     */
    public function get($name, $default = null)
    {
        return ThinkCache::get($this->getValueName() . ':' . $name, $default);
    }

    /**
     * @param $name
     * @param $value
     * @param null $expire
     * @return bool
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/2/8
     */
    public function set($name, $value, $expire = null)
    {
        return $this->tag()->set($this->getValueName() . ':' . $name, $value, $expire);
    }

    /**
     * @return bool
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/2/8
     */
    public function clear()
    {
        return $this->tag()->clear();
    }

}
