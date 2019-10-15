<?php

namespace League\Flysystem\Cached\Storage;

use League\Flysystem\Cached\CacheInterface;
use League\Flysystem\Util;

abstract class AbstractCache implements CacheInterface
{
    /**
     * @var bool
     */
    protected $autosave = true;

    /**
     * @var array
     */
    protected $cache = [];

    /**
     * @var array
     */
    protected $complete = [];

    /**
     * Destructor.
     */
    public function __destruct()
    {
        if (! $this->autosave) {
            $this->save();
        }
    }

    /**
     * Get the autosave setting.
     *
     * @return bool autosave
     */
    public function getAutosave()
    {
        return $this->autosave;
    }

    /**
     * Get the autosave setting.
     *
     * @param bool $autosave
     */
    public function setAutosave($autosave)
    {
        $this->autosave = $autosave;
    }

    /**
     * Store the contents listing.
     *
     * @param string $directory
     * @param array  $contents
     * @param bool   $recursive
     *
     * @return array contents listing
     */
    public function storeContents($directory, array $contents, $recursive = false)
    {
        $directories = [$directory];

        foreach ($contents as $object) {
            $this->updateObject($object['path'], $object);
            $object = $this->cache[$object['path']];

            if ($recursive && $this->pathIsInDirectory($directory, $object['path'])) {
                $directories[] = $object['dirname'];
            }
        }

        foreach (array_unique($directories) as $directory) {
            $this->setComplete($directory, $recursive);
        }

        $this->autosave();
    }

    /**
     * Update the metadata for an object.
     *
     * @param string $path     object path
     * @param array  $object   object metadata
     * @param bool   $autosave whether to trigger the autosave routine
     */
    public function updateObject($path, array $object, $autosave = false)
    {
        if (! $this->has($path)) {
            $this->cache[$path] = Util::pathinfo($path);
        }

        $this->cache[$path] = array_merge($this->cache[$path], $object);

        if ($autosave) {
            $this->autosave();
        }

        $this->ensureParentDirectories($path);
    }

    /**
     * Store object hit miss.
     *
     * @param string $path
     */
    public function storeMiss($path)
    {
        $this->cache[$path] = false;
        $this->autosave();
    }

    /**
     * Get the contents listing.
     *
     * @param string $dirname
     * @param bool   $recursive
     *
     * @return array contents listing
     */
    public function listContents($dirname = '', $recursive = false)
    {
        $result = [];

        foreach ($this->cache as $object) {
            if ($object === false) {
                continue;
            }
            if ($object['dirname'] === $dirname) {
                $result[] = $object;
            } elseif ($recursive && $this->pathIsInDirectory($dirname, $object['path'])) {
                $result[] = $object;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function has($path)
    {
        if ($path !== false && array_key_exists($path, $this->cache)) {
            return $this->cache[$path] !== false;
        }

        if ($this->isComplete(Util::dirname($path), false)) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
    {
        if (isset($this->cache[$path]['contents']) && $this->cache[$path]['contents'] !== false) {
            return $this->cache[$path];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function rename($path, $newpath)
    {
        if ($this->has($path)) {
            $object = $this->cache[$path];
            unset($this->cache[$path]);
            $object['path'] = $newpath;
            $object = array_merge($object, Util::pathinfo($newpath));
            $this->cache[$newpath] = $object;
            $this->autosave();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function copy($path, $newpath)
    {
        if ($this->has($path)) {
            $object = $this->cache[$path];
            $object = array_merge($object, Util::pathinfo($newpath));
            $this->updateObject($newpath, $object, true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path)
    {
        $this->storeMiss($path);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDir($dirname)
    {
        foreach ($this->cache as $path => $object) {
            if ($this->pathIsInDirectory($dirname, $path) || $path === $dirname) {
                unset($this->cache[$path]);
            }
        }

        unset($this->complete[$dirname]);

        $this->autosave();
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype($path)
    {
        if (isset($this->cache[$path]['mimetype'])) {
            return $this->cache[$path];
        }

        if (! $result = $this->read($path)) {
            return false;
        }

        $mimetype = Util::guessMimeType($path, $result['contents']);
        $this->cache[$path]['mimetype'] = $mimetype;

        return $this->cache[$path];
    }

    /**
     * {@inheritdoc}
     */
    public function getSize($path)
    {
        if (isset($this->cache[$path]['size'])) {
            return $this->cache[$path];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
        if (isset($this->cache[$path]['timestamp'])) {
            return $this->cache[$path];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibility($path)
    {
        if (isset($this->cache[$path]['visibility'])) {
            return $this->cache[$path];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($path)
    {
        if (isset($this->cache[$path]['type'])) {
            return $this->cache[$path];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isComplete($dirname, $recursive)
    {
        if (! array_key_exists($dirname, $this->complete)) {
            return false;
        }

        if ($recursive && $this->complete[$dirname] !== 'recursive') {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function setComplete($dirname, $recursive)
    {
        $this->complete[$dirname] = $recursive ? 'recursive' : true;
    }

    /**
     * Filter the contents from a listing.
     *
     * @param array $contents object listing
     *
     * @return array filtered contents
     */
    public function cleanContents(array $contents)
    {
        $cachedProperties = array_flip([
            'path', 'dirname', 'basename', 'extension', 'filename',
            'size', 'mimetype', 'visibility', 'timestamp', 'type',
        ]);

        foreach ($contents as $path => $object) {
            if (is_array($object)) {
                $contents[$path] = array_intersect_key($object, $cachedProperties);
            }
        }

        return $contents;
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $this->cache = [];
        $this->complete = [];
        $this->autosave();
    }

    /**
     * {@inheritdoc}
     */
    public function autosave()
    {
        if ($this->autosave) {
            $this->save();
        }
    }

    /**
     * Retrieve serialized cache data.
     *
     * @return string serialized data
     */
    public function getForStorage()
    {
        $cleaned = $this->cleanContents($this->cache);

        return json_encode([$cleaned, $this->complete]);
    }

    /**
     * Load from serialized cache data.
     *
     * @param string $json
     */
    public function setFromStorage($json)
    {
        list($cache, $complete) = json_decode($json, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($cache) && is_array($complete)) {
            $this->cache = $cache;
            $this->complete = $complete;
        }
    }

    /**
     * Ensure parent directories of an object.
     *
     * @param string $path object path
     */
    public function ensureParentDirectories($path)
    {
        $object = $this->cache[$path];

        while ($object['dirname'] !== '' && ! isset($this->cache[$object['dirname']])) {
            $object = Util::pathinfo($object['dirname']);
            $object['type'] = 'dir';
            $this->cache[$object['path']] = $object;
        }
    }

    /**
     * Determines if the path is inside the directory.
     *
     * @param string $directory
     * @param string $path
     *
     * @return bool
     */
    protected function pathIsInDirectory($directory, $path)
    {
        return $directory === '' || strpos($path, $directory . '/') === 0;
    }
}
