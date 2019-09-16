<?php

namespace think\filesystem;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Cached\Storage\Memory as MemoryStore;
use League\Flysystem\Filesystem;
use think\App;
use think\File;

/**
 * Class Driver
 * @package think\filesystem
 * @mixin Filesystem
 */
abstract class Driver
{

    /** @var App */
    protected $app;

    /** @var Filesystem */
    protected $filesystem;

    /**
     * 配置参数
     * @var array
     */
    protected $config = [];

    public function __construct(App $app, array $config)
    {
        $this->app    = $app;
        $this->config = array_merge($this->config, $config);

        $adapter          = $this->createAdapter();
        $this->filesystem = $this->createFilesystem($adapter);
    }

    protected function createCacheStore($config)
    {
        if (true === $config) {
            return new MemoryStore;
        }

        return new CacheStore(
            $this->app->cache->store($config['store']),
            $config['prefix'] ?? 'flysystem',
            $config['expire'] ?? null
        );
    }

    abstract protected function createAdapter(): AdapterInterface;

    protected function createFilesystem(AdapterInterface $adapter): Filesystem
    {
        if (!empty($this->config['cache'])) {
            $adapter = new CachedAdapter($adapter, $this->createCacheStore($this->config['cache']));
        }

        $config = array_intersect_key($this->config, array_flip(['visibility', 'disable_asserts', 'url']));

        return new Filesystem($adapter, count($config) > 0 ? $config : null);
    }

    /**
     * 获取文件完整路径
     * @param string $path
     * @return string
     */
    public function path(string $path): string
    {
        $adapter = $this->filesystem->getAdapter();

        if ($adapter instanceof AbstractAdapter) {
            return $adapter->applyPathPrefix($path);
        }

        return $path;
    }

    /**
     * 保存文件
     * @param string               $path
     * @param File                 $file
     * @param null|string|\Closure $rule
     * @param array                $options
     * @return bool|string
     */
    public function putFile(string $path, File $file, $rule = null, array $options = [])
    {
        return $this->putFileAs($path, $file, $file->hashName($rule), $options);
    }

    /**
     * 指定文件名保存文件
     * @param string $path
     * @param File   $file
     * @param string $name
     * @param array  $options
     * @return bool|string
     */
    public function putFileAs(string $path, File $file, string $name, array $options = [])
    {
        $stream = fopen($file->getRealPath(), 'r');

        $result = $this->putStream(
            $path = trim($path . '/' . $name, '/'), $stream, $options
        );

        if (is_resource($stream)) {
            fclose($stream);
        }

        return $result ? $path : false;
    }

    public function __call($method, $parameters)
    {
        return $this->filesystem->$method(...$parameters);
    }
}
