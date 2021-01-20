<?php

namespace League\Flysystem\Cached\Storage;

use Psr\Cache\CacheItemPoolInterface;

class Psr6Cache extends AbstractCache
{
    /**
     * @var CacheItemPoolInterface
     */
    private $pool;

    /**
     * @var string storage key
     */
    protected $key;

    /**
     * @var int|null seconds until cache expiration
     */
    protected $expire;

    /**
     * Constructor.
     *
     * @param CacheItemPoolInterface $pool
     * @param string                 $key    storage key
     * @param int|null               $expire seconds until cache expiration
     */
    public function __construct(CacheItemPoolInterface $pool, $key = 'flysystem', $expire = null)
    {
        $this->pool = $pool;
        $this->key = $key;
        $this->expire = $expire;
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $item = $this->pool->getItem($this->key);
        $item->set($this->getForStorage());
        $item->expiresAfter($this->expire);
        $this->pool->save($item);
    }

    /**
     * {@inheritdoc}
     */
    public function load()
    {
        $item = $this->pool->getItem($this->key);
        if ($item->isHit()) {
            $this->setFromStorage($item->get());
        }
    }
}