<?php
declare(strict_types=1);

namespace Fastknife\Utils;


class CacheUtils
{

    /**
     * 配置参数
     * @var array
     */
    protected $options = [
        'expire'        => 300,
        'cache_subdir'  => true,
        'prefix'        => '',
        'path'          => '',
        'hash_type'     => 'md5',
        'data_compress' => false,
        'tag_prefix'    => 'tag:',
        'serialize'     => [],
    ];

    /**
     * 缓存读取次数
     * @var integer
     */
    protected $readTimes = 0;

    /**
     * 缓存写入次数
     * @var integer
     */
    protected $writeTimes = 0;


    /**
     * 架构函数
     * @param array $options 参数
     */
    public function __construct(array $options = [])
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }

        if (empty($this->options['path'])) {
            $root = isset($_SERVER['DOCUMENT_ROOT']) && !empty($_SERVER['DOCUMENT_ROOT']) ?$_SERVER['DOCUMENT_ROOT'] : getcwd();
            $this->options['path'] = $root. '/runtime/cache';
        }

        if (substr($this->options['path'], -1) != DIRECTORY_SEPARATOR) {
            $this->options['path'] .= DIRECTORY_SEPARATOR;
        }
    }

    /**
     * 取得变量的存储文件名
     * @access public
     * @param string $name 缓存变量名
     * @return string
     */
    public function getCacheKey(string $name): string
    {
        $name = hash($this->options['hash_type'], $name);

        if ($this->options['cache_subdir']) {
            // 使用子目录
            $name = substr($name, 0, 2) . DIRECTORY_SEPARATOR . substr($name, 2);
        }

        if ($this->options['prefix']) {
            $name = $this->options['prefix'] . DIRECTORY_SEPARATOR . $name;
        }

        return $this->options['path'] . $name . '.php';
    }

    /**
     * 获取缓存数据
     * @param string $name 缓存标识名
     * @return array|null
     */
    protected function getRaw(string $name)
    {
        $filename = $this->getCacheKey($name);

        if (!is_file($filename)) {
            return;
        }

        $content = @file_get_contents($filename);

        if (false !== $content) {
            $expire = (int) substr($content, 8, 12);
            if (0 != $expire && time() - $expire > filemtime($filename)) {
                //缓存过期删除缓存文件
                $this->unlink($filename);
                return;
            }

            $content = substr($content, 32);

            return is_string($content) ? ['content' => $content, 'expire' => $expire] : null;
        }
    }

    /**
     * 判断缓存是否存在
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function has($name): bool
    {
        return $this->getRaw($name) !== null;
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name    缓存变量名
     * @param mixed  $default 默认值
     * @return mixed
     */
    public function get($name, $default = null)
    {
        $this->readTimes++;

        $raw = $this->getRaw($name);

        return is_null($raw) ? $default : $this->unserialize($raw['content']);
    }

    /**
     * 写入缓存
     * @access public
     * @param string        $name   缓存变量名
     * @param mixed         $value  存储数据
     * @param int $expire 有效时间 0为永久
     * @return bool
     */
    public function set($name, $value, $expire = null): bool
    {
        $this->writeTimes++;

        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }

        $filename = $this->getCacheKey($name);

        $dir = dirname($filename);

        if (!is_dir($dir)) {
            try {
                mkdir($dir, 0755, true);
            } catch (\Exception $e) {
                // 创建失败
            }
        }

        $data = $this->serialize($value);

        if ($this->options['data_compress'] && function_exists('gzcompress')) {
            //数据压缩
            $data = gzcompress($data, 3);
        }

        $data   = "<?php\n//" . sprintf('%012d', $expire) . "\n exit();?>\n" . $data;
        $result = file_put_contents($filename, $data);

        if ($result) {
            clearstatcache();
            return true;
        }

        return false;
    }

    /**
     * 自增缓存（针对数值缓存）
     * @access public
     * @param string $name 缓存变量名
     * @param int    $step 步长
     * @return false|int
     */
    public function inc(string $name, int $step = 1)
    {
        if ($raw = $this->getRaw($name)) {
            $value  = $this->unserialize($raw['content']) + $step;
            $expire = $raw['expire'];
        } else {
            $value  = $step;
            $expire = 0;
        }

        return $this->set($name, $value, $expire) ? $value : false;
    }

    /**
     * 自减缓存（针对数值缓存）
     * @access public
     * @param string $name 缓存变量名
     * @param int    $step 步长
     * @return false|int
     */
    public function dec(string $name, int $step = 1)
    {
        return $this->inc($name, -$step);
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function delete($name): bool
    {
        $this->writeTimes++;

        return $this->unlink($this->getCacheKey($name));
    }

    /**
     * 清除缓存
     * @access public
     * @return bool
     */
    public function clear(): bool
    {
        $this->writeTimes++;

        $dirname = $this->options['path'] . $this->options['prefix'];

        $this->rmdir($dirname);

        return true;
    }

    /**
     * 删除缓存标签
     * @access public
     * @param array $keys 缓存标识列表
     * @return void
     */
    public function clearTag(array $keys): void
    {
        foreach ($keys as $key) {
            $this->unlink($key);
        }
    }

    /**
     * 判断文件是否存在后，删除
     * @access private
     * @param string $path
     * @return bool
     */
    private function unlink(string $path): bool
    {
        try {
            return is_file($path) && unlink($path);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 删除文件夹
     * @param $dirname
     * @return bool
     */
    private function rmdir($dirname)
    {
        if (!is_dir($dirname)) {
            return false;
        }

        $items = new \FilesystemIterator($dirname);

        foreach ($items as $item) {
            if ($item->isDir() && !$item->isLink()) {
                $this->rmdir($item->getPathname());
            } else {
                $this->unlink($item->getPathname());
            }
        }

        @rmdir($dirname);

        return true;
    }
    /**
     * 序列化数据
     * @access protected
     * @param mixed $data 缓存数据
     * @return string
     */
    protected function serialize($data): string
    {
        if (is_numeric($data)) {
            return (string) $data;
        }

        $serialize = $this->options['serialize'][0] ?? "serialize";

        return $serialize($data);
    }

    /**
     * 反序列化数据
     * @access protected
     * @param string $data 缓存数据
     * @return mixed
     */
    protected function unserialize(string $data)
    {
        if (is_numeric($data)) {
            return $data;
        }

        $unserialize = $this->options['serialize'][1] ?? "unserialize";

        return $unserialize($data);
    }


}
