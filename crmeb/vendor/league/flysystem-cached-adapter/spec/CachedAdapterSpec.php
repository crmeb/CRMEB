<?php

namespace spec\League\Flysystem\Cached;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Cached\CacheInterface;
use League\Flysystem\Config;
use PhpSpec\ObjectBehavior;

class CachedAdapterSpec extends ObjectBehavior
{
    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var CacheInterface
     */
    private $cache;

    public function let(AdapterInterface $adapter, CacheInterface $cache)
    {
        $this->adapter = $adapter;
        $this->cache = $cache;
        $this->cache->load()->shouldBeCalled();
        $this->beConstructedWith($adapter, $cache);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('League\Flysystem\Cached\CachedAdapter');
        $this->shouldHaveType('League\Flysystem\AdapterInterface');
    }

    public function it_should_forward_read_streams()
    {
        $path = 'path.txt';
        $response = ['path' => $path];
        $this->adapter->readStream($path)->willReturn($response);
        $this->readStream($path)->shouldbe($response);
    }

    public function it_should_cache_writes()
    {
        $type = 'file';
        $path = 'path.txt';
        $contents = 'contents';
        $config = new Config();
        $response = compact('path', 'contents', 'type');
        $this->adapter->write($path, $contents, $config)->willReturn($response);
        $this->cache->updateObject($path, $response, true)->shouldBeCalled();
        $this->write($path, $contents, $config)->shouldBe($response);
    }

    public function it_should_cache_streamed_writes()
    {
        $type = 'file';
        $path = 'path.txt';
        $stream = tmpfile();
        $config = new Config();
        $response = compact('path', 'stream', 'type');
        $this->adapter->writeStream($path, $stream, $config)->willReturn($response);
        $this->cache->updateObject($path, ['contents' => false] + $response, true)->shouldBeCalled();
        $this->writeStream($path, $stream, $config)->shouldBe($response);
        fclose($stream);
    }

    public function it_should_cache_streamed_updates()
    {
        $type = 'file';
        $path = 'path.txt';
        $stream = tmpfile();
        $config = new Config();
        $response = compact('path', 'stream', 'type');
        $this->adapter->updateStream($path, $stream, $config)->willReturn($response);
        $this->cache->updateObject($path, ['contents' => false] + $response, true)->shouldBeCalled();
        $this->updateStream($path, $stream, $config)->shouldBe($response);
        fclose($stream);
    }

    public function it_should_ignore_failed_writes()
    {
        $path = 'path.txt';
        $contents = 'contents';
        $config = new Config();
        $this->adapter->write($path, $contents, $config)->willReturn(false);
        $this->write($path, $contents, $config)->shouldBe(false);
    }

    public function it_should_ignore_failed_streamed_writes()
    {
        $path = 'path.txt';
        $contents = tmpfile();
        $config = new Config();
        $this->adapter->writeStream($path, $contents, $config)->willReturn(false);
        $this->writeStream($path, $contents, $config)->shouldBe(false);
        fclose($contents);
    }

    public function it_should_cache_updated()
    {
        $type = 'file';
        $path = 'path.txt';
        $contents = 'contents';
        $config = new Config();
        $response = compact('path', 'contents', 'type');
        $this->adapter->update($path, $contents, $config)->willReturn($response);
        $this->cache->updateObject($path, $response, true)->shouldBeCalled();
        $this->update($path, $contents, $config)->shouldBe($response);
    }

    public function it_should_ignore_failed_updates()
    {
        $path = 'path.txt';
        $contents = 'contents';
        $config = new Config();
        $this->adapter->update($path, $contents, $config)->willReturn(false);
        $this->update($path, $contents, $config)->shouldBe(false);
    }

    public function it_should_ignore_failed_streamed_updates()
    {
        $path = 'path.txt';
        $contents = tmpfile();
        $config = new Config();
        $this->adapter->updateStream($path, $contents, $config)->willReturn(false);
        $this->updateStream($path, $contents, $config)->shouldBe(false);
        fclose($contents);
    }

    public function it_should_cache_renames()
    {
        $old = 'old.txt';
        $new = 'new.txt';
        $this->adapter->rename($old, $new)->willReturn(true);
        $this->cache->rename($old, $new)->shouldBeCalled();
        $this->rename($old, $new)->shouldBe(true);
    }

    public function it_should_ignore_rename_fails()
    {
        $old = 'old.txt';
        $new = 'new.txt';
        $this->adapter->rename($old, $new)->willReturn(false);
        $this->rename($old, $new)->shouldBe(false);
    }

    public function it_should_cache_copies()
    {
        $old = 'old.txt';
        $new = 'new.txt';
        $this->adapter->copy($old, $new)->willReturn(true);
        $this->cache->copy($old, $new)->shouldBeCalled();
        $this->copy($old, $new)->shouldBe(true);
    }

    public function it_should_ignore_copy_fails()
    {
        $old = 'old.txt';
        $new = 'new.txt';
        $this->adapter->copy($old, $new)->willReturn(false);
        $this->copy($old, $new)->shouldBe(false);
    }

    public function it_should_cache_deletes()
    {
        $delete = 'delete.txt';
        $this->adapter->delete($delete)->willReturn(true);
        $this->cache->delete($delete)->shouldBeCalled();
        $this->delete($delete)->shouldBe(true);
    }

    public function it_should_ignore_delete_fails()
    {
        $delete = 'delete.txt';
        $this->adapter->delete($delete)->willReturn(false);
        $this->delete($delete)->shouldBe(false);
    }

    public function it_should_cache_dir_deletes()
    {
        $delete = 'delete';
        $this->adapter->deleteDir($delete)->willReturn(true);
        $this->cache->deleteDir($delete)->shouldBeCalled();
        $this->deleteDir($delete)->shouldBe(true);
    }

    public function it_should_ignore_delete_dir_fails()
    {
        $delete = 'delete';
        $this->adapter->deleteDir($delete)->willReturn(false);
        $this->deleteDir($delete)->shouldBe(false);
    }

    public function it_should_cache_dir_creates()
    {
        $dirname = 'dirname';
        $config = new Config();
        $response = ['path' => $dirname, 'type' => 'dir'];
        $this->adapter->createDir($dirname, $config)->willReturn($response);
        $this->cache->updateObject($dirname, $response, true)->shouldBeCalled();
        $this->createDir($dirname, $config)->shouldBe($response);
    }

    public function it_should_ignore_create_dir_fails()
    {
        $dirname = 'dirname';
        $config = new Config();
        $this->adapter->createDir($dirname, $config)->willReturn(false);
        $this->createDir($dirname, $config)->shouldBe(false);
    }

    public function it_should_cache_set_visibility()
    {
        $path = 'path.txt';
        $visibility = AdapterInterface::VISIBILITY_PUBLIC;
        $this->adapter->setVisibility($path, $visibility)->willReturn(true);
        $this->cache->updateObject($path, ['path' => $path, 'visibility' => $visibility], true)->shouldBeCalled();
        $this->setVisibility($path, $visibility)->shouldBe(true);
    }

    public function it_should_ignore_set_visibility_fails()
    {
        $dirname = 'delete';
        $visibility = AdapterInterface::VISIBILITY_PUBLIC;
        $this->adapter->setVisibility($dirname, $visibility)->willReturn(false);
        $this->setVisibility($dirname, $visibility)->shouldBe(false);
    }

    public function it_should_indicate_missing_files()
    {
        $this->cache->has($path = 'path.txt')->willReturn(false);
        $this->has($path)->shouldBe(false);
    }

    public function it_should_indicate_file_existance()
    {
        $this->cache->has($path = 'path.txt')->willReturn(true);
        $this->has($path)->shouldBe(true);
    }

    public function it_should_cache_missing_files()
    {
        $this->cache->has($path = 'path.txt')->willReturn(null);
        $this->adapter->has($path)->willReturn(false);
        $this->cache->storeMiss($path)->shouldBeCalled();
        $this->has($path)->shouldBe(false);
    }

    public function it_should_delete_when_metadata_is_missing()
    {
        $path = 'path.txt';
        $this->cache->has($path)->willReturn(true);
        $this->cache->getSize($path)->willReturn(['path' => $path]);
        $this->adapter->getSize($path)->willReturn($response = ['path' => $path, 'size' => 1024]);
        $this->cache->updateObject($path, $response, true)->shouldBeCalled();
        $this->getSize($path)->shouldBe($response);
    }

    public function it_should_cache_has()
    {
        $this->cache->has($path = 'path.txt')->willReturn(null);
        $this->adapter->has($path)->willReturn(true);
        $this->cache->updateObject($path, compact('path'), true)->shouldBeCalled();
        $this->has($path)->shouldBe(true);
    }

    public function it_should_list_cached_contents()
    {
        $this->cache->isComplete($dirname = 'dirname', $recursive = true)->willReturn(true);
        $response = [['path' => 'path.txt']];
        $this->cache->listContents($dirname, $recursive)->willReturn($response);
        $this->listContents($dirname, $recursive)->shouldBe($response);
    }

    public function it_should_ignore_failed_list_contents()
    {
        $this->cache->isComplete($dirname = 'dirname', $recursive = true)->willReturn(false);
        $this->adapter->listContents($dirname, $recursive)->willReturn(false);
        $this->listContents($dirname, $recursive)->shouldBe(false);
    }

    public function it_should_cache_contents_listings()
    {
        $this->cache->isComplete($dirname = 'dirname', $recursive = true)->willReturn(false);
        $response = [['path' => 'path.txt']];
        $this->adapter->listContents($dirname, $recursive)->willReturn($response);
        $this->cache->storeContents($dirname, $response, $recursive)->shouldBeCalled();
        $this->listContents($dirname, $recursive)->shouldBe($response);
    }

    public function it_should_use_cached_visibility()
    {
        $this->make_it_use_getter_cache('getVisibility', 'path.txt', [
            'path' => 'path.txt',
            'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
        ]);
    }

    public function it_should_cache_get_visibility()
    {
        $path = 'path.txt';
        $response = ['visibility' => AdapterInterface::VISIBILITY_PUBLIC, 'path' => $path];
        $this->make_it_cache_getter('getVisibility', $path, $response);
    }

    public function it_should_ignore_failed_get_visibility()
    {
        $path = 'path.txt';
        $this->make_it_ignore_failed_getter('getVisibility', $path);
    }

    public function it_should_use_cached_timestamp()
    {
        $this->make_it_use_getter_cache('getTimestamp', 'path.txt', [
            'path' => 'path.txt',
            'timestamp' => 1234,
        ]);
    }

    public function it_should_cache_timestamps()
    {
        $this->make_it_cache_getter('getTimestamp', 'path.txt', [
            'path' => 'path.txt',
            'timestamp' => 1234,
        ]);
    }

    public function it_should_ignore_failed_get_timestamps()
    {
        $this->make_it_ignore_failed_getter('getTimestamp', 'path.txt');
    }

    public function it_should_cache_get_metadata()
    {
        $path = 'path.txt';
        $response = ['visibility' => AdapterInterface::VISIBILITY_PUBLIC, 'path' => $path];
        $this->make_it_cache_getter('getMetadata', $path, $response);
    }

    public function it_should_use_cached_metadata()
    {
        $this->make_it_use_getter_cache('getMetadata', 'path.txt', [
            'path' => 'path.txt',
            'timestamp' => 1234,
        ]);
    }

    public function it_should_ignore_failed_get_metadata()
    {
        $this->make_it_ignore_failed_getter('getMetadata', 'path.txt');
    }

    public function it_should_cache_get_size()
    {
        $path = 'path.txt';
        $response = ['size' => 1234, 'path' => $path];
        $this->make_it_cache_getter('getSize', $path, $response);
    }

    public function it_should_use_cached_size()
    {
        $this->make_it_use_getter_cache('getSize', 'path.txt', [
            'path' => 'path.txt',
            'size' => 1234,
        ]);
    }

    public function it_should_ignore_failed_get_size()
    {
        $this->make_it_ignore_failed_getter('getSize', 'path.txt');
    }

    public function it_should_cache_get_mimetype()
    {
        $path = 'path.txt';
        $response = ['mimetype' => 'text/plain', 'path' => $path];
        $this->make_it_cache_getter('getMimetype', $path, $response);
    }

    public function it_should_use_cached_mimetype()
    {
        $this->make_it_use_getter_cache('getMimetype', 'path.txt', [
            'path' => 'path.txt',
            'mimetype' => 'text/plain',
        ]);
    }

    public function it_should_ignore_failed_get_mimetype()
    {
        $this->make_it_ignore_failed_getter('getMimetype', 'path.txt');
    }

    public function it_should_cache_reads()
    {
        $path = 'path.txt';
        $response = ['path' => $path, 'contents' => 'contents'];
        $this->make_it_cache_getter('read', $path, $response);
    }

    public function it_should_use_cached_file_contents()
    {
        $this->make_it_use_getter_cache('read', 'path.txt', [
            'path' => 'path.txt',
            'contents' => 'contents'
        ]);
    }

    public function it_should_ignore_failed_reads()
    {
        $this->make_it_ignore_failed_getter('read', 'path.txt');
    }

    protected function make_it_use_getter_cache($method, $path, $response)
    {
        $this->cache->{$method}($path)->willReturn($response);
        $this->{$method}($path)->shouldBe($response);
    }

    protected function make_it_cache_getter($method, $path, $response)
    {
        $this->cache->{$method}($path)->willReturn(false);
        $this->adapter->{$method}($path)->willReturn($response);
        $this->cache->updateObject($path, $response, true)->shouldBeCalled();
        $this->{$method}($path)->shouldBe($response);
    }

    protected function make_it_ignore_failed_getter($method, $path)
    {
        $this->cache->{$method}($path)->willReturn(false);
        $this->adapter->{$method}($path)->willReturn(false);
        $this->{$method}($path)->shouldBe(false);
    }
}
