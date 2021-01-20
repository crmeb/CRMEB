<?php

namespace League\Flysystem\Cached;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;

class CachedAdapter implements AdapterInterface
{
    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * Constructor.
     *
     * @param AdapterInterface $adapter
     * @param CacheInterface   $cache
     */
    public function __construct(AdapterInterface $adapter, CacheInterface $cache)
    {
        $this->adapter = $adapter;
        $this->cache = $cache;
        $this->cache->load();
    }

    /**
     * Get the underlying Adapter implementation.
     *
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Get the used Cache implementation.
     *
     * @return CacheInterface
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * {@inheritdoc}
     */
    public function write($path, $contents, Config $config)
    {
        $result = $this->adapter->write($path, $contents, $config);

        if ($result !== false) {
            $result['type'] = 'file';
            $this->cache->updateObject($path, $result + compact('path', 'contents'), true);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($path, $resource, Config $config)
    {
        $result = $this->adapter->writeStream($path, $resource, $config);

        if ($result !== false) {
            $result['type'] = 'file';
            $contents = false;
            $this->cache->updateObject($path, $result + compact('path', 'contents'), true);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function update($path, $contents, Config $config)
    {
        $result = $this->adapter->update($path, $contents, $config);

        if ($result !== false) {
            $result['type'] = 'file';
            $this->cache->updateObject($path, $result + compact('path', 'contents'), true);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream($path, $resource, Config $config)
    {
        $result = $this->adapter->updateStream($path, $resource, $config);

        if ($result !== false) {
            $result['type'] = 'file';
            $contents = false;
            $this->cache->updateObject($path, $result + compact('path', 'contents'), true);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function rename($path, $newPath)
    {
        $result = $this->adapter->rename($path, $newPath);

        if ($result !== false) {
            $this->cache->rename($path, $newPath);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function copy($path, $newpath)
    {
        $result = $this->adapter->copy($path, $newpath);

        if ($result !== false) {
            $this->cache->copy($path, $newpath);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path)
    {
        $result = $this->adapter->delete($path);

        if ($result !== false) {
            $this->cache->delete($path);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDir($dirname)
    {
        $result = $this->adapter->deleteDir($dirname);

        if ($result !== false) {
            $this->cache->deleteDir($dirname);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function createDir($dirname, Config $config)
    {
        $result = $this->adapter->createDir($dirname, $config);

        if ($result !== false) {
            $type = 'dir';
            $path = $dirname;
            $this->cache->updateObject($dirname, compact('path', 'type'), true);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibility($path, $visibility)
    {
        $result = $this->adapter->setVisibility($path, $visibility);

        if ($result !== false) {
            $this->cache->updateObject($path, compact('path', 'visibility'), true);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function has($path)
    {
        $cacheHas = $this->cache->has($path);

        if ($cacheHas !== null) {
            return $cacheHas;
        }

        $adapterResponse = $this->adapter->has($path);

        if (! $adapterResponse) {
            $this->cache->storeMiss($path);
        } else {
            $cacheEntry = is_array($adapterResponse) ? $adapterResponse : compact('path');
            $this->cache->updateObject($path, $cacheEntry, true);
        }

        return $adapterResponse;
    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
    {
        return $this->callWithFallback('contents', $path, 'read');
    }

    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        return $this->adapter->readStream($path);
    }

    /**
     * {@inheritdoc}
     */
    public function listContents($directory = '', $recursive = false)
    {
        if ($this->cache->isComplete($directory, $recursive)) {
            return $this->cache->listContents($directory, $recursive);
        }

        $result = $this->adapter->listContents($directory, $recursive);

        if ($result !== false) {
            $this->cache->storeContents($directory, $result, $recursive);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($path)
    {
        return $this->callWithFallback(null, $path, 'getMetadata');
    }

    /**
     * {@inheritdoc}
     */
    public function getSize($path)
    {
        return $this->callWithFallback('size', $path, 'getSize');
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype($path)
    {
        return $this->callWithFallback('mimetype', $path, 'getMimetype');
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
        return $this->callWithFallback('timestamp', $path, 'getTimestamp');
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibility($path)
    {
        return $this->callWithFallback('visibility', $path, 'getVisibility');
    }

    /**
     * Call a method and cache the response.
     *
     * @param string $property
     * @param string $path
     * @param string $method
     *
     * @return mixed
     */
    protected function callWithFallback($property, $path, $method)
    {
        $result = $this->cache->{$method}($path);

        if ($result !== false && ($property === null || array_key_exists($property, $result))) {
            return $result;
        }

        $result = $this->adapter->{$method}($path);

        if ($result) {
            $object = $result + compact('path');
            $this->cache->updateObject($path, $object, true);
        }

        return $result;
    }
}
