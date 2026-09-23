<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2025 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace think\db;

use DateInterval;
use DateTime;
use DateTimeInterface;
use think\db\exception\InvalidArgumentException;

/**
 * CacheItem实现类.
 */
class CacheItem
{
    /**
     * 缓存Key.
     *
     * @var string
     */
    protected $key;

    /**
     * 缓存内容.
     *
     * @var mixed
     */
    protected $value;

    /**
     * 过期时间.
     *
     * @var int|DateTimeInterface
     */
    protected $expire;

    /**
     * 缓存tag.
     *
     * @var string
     */
    protected $tag;

    /**
     * 缓存是否命中.
     *
     * @var bool
     */
    protected $isHit = false;

    public function __construct(?string $key = null)
    {
        $this->key = $key;
    }

    /**
     * 为此缓存项设置「键」.
     *
     * @param string $key
     *
     * @return $this
     */
    public function setKey(string $key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * 返回当前缓存项的「键」.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * 返回当前缓存项的有效期
     *
     * @return DateTimeInterface|int|null
     */
    public function getExpire()
    {
        if ($this->expire instanceof DateTimeInterface) {
            return $this->expire;
        }

        return $this->expire ? $this->expire - time() : null;
    }

    /**
     * 获取缓存Tag.
     *
     * @return string|array
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * 凭借此缓存项的「键」从缓存系统里面取出缓存项.
     *
     * @return mixed
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * 确认缓存项的检查是否命中.
     *
     * @return bool
     */
    public function isHit(): bool
    {
        return $this->isHit;
    }

    /**
     * 为此缓存项设置「值」.
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function set($value)
    {
        $this->value = $value;
        $this->isHit = true;

        return $this;
    }

    /**
     * 为此缓存项设置所属标签.
     *
     * @param string|array $tag
     *
     * @return $this
     */
    public function tag($tag = null)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * 设置缓存项的有效期
     *
     * @param mixed $expire
     *
     * @return $this
     */
    public function expire($expire)
    {
        if (is_null($expire)) {
            $this->expire = null;
        } elseif (is_numeric($expire) || $expire instanceof DateInterval) {
            $this->expiresAfter($expire);
        } elseif ($expire instanceof DateTimeInterface) {
            $this->expire = $expire;
        } else {
            throw new InvalidArgumentException('not support datetime');
        }

        return $this;
    }

    /**
     * 设置缓存项的准确过期时间点.
     *
     * @param DateTimeInterface $expiration
     *
     * @return $this
     */
    public function expiresAt(DateTimeInterface $expiration)
    {
        $this->expire = $expiration;

        return $this;
    }

    /**
     * 设置缓存项的过期时间.
     *
     * @param int|DateInterval $timeInterval
     *
     * @throws InvalidArgumentException
     *
     * @return $this
     */
    public function expiresAfter($timeInterval)
    {
        if ($timeInterval instanceof DateInterval) {
            $this->expire = (int) DateTime::createFromFormat('U', (string) time())->add($timeInterval)->format('U');
        } elseif (is_numeric($timeInterval)) {
            $this->expire = $timeInterval + time();
        } else {
            throw new InvalidArgumentException('not support datetime');
        }

        return $this;
    }
}
