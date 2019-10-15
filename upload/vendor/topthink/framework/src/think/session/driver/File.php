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

use think\App;
use think\contract\SessionHandlerInterface;

/**
 * Session 文件驱动
 */
class File implements SessionHandlerInterface
{
    protected $config = [
        'path'           => '',
        'expire'         => 0,
        'prefix'         => '',
        'data_compress'  => false,
        'gc_divisor'     => 1000,
        'gc_maxlifetime' => 1440,
    ];

    public function __construct(App $app, array $config = [])
    {
        $this->config = array_merge($this->config, $config);

        if (empty($this->config['path'])) {
            $this->config['path'] = $app->getRootPath() . 'runtime' . DIRECTORY_SEPARATOR . 'session' . DIRECTORY_SEPARATOR;
        } elseif (substr($this->config['path'], -1) != DIRECTORY_SEPARATOR) {
            $this->config['path'] .= DIRECTORY_SEPARATOR;
        }

        $this->init();
    }

    /**
     * 打开Session
     * @access protected
     * @return bool
     * @throws Exception
     */
    protected function init(): bool
    {
        try {
            !is_dir($this->config['path']) && mkdir($this->config['path'], 0755, true);
        } catch (\Exception $e) {
            // 写入失败
            return false;
        }

        if (1 == mt_rand(1, $this->config['gc_divisor'])) {
            $this->gc();
        }

        return true;
    }

    /**
     * Session 垃圾回收
     * @access public
     * @return true
     */
    public function gc()
    {
        $maxlifetime = $this->config['gc_maxlifetime'];
        $list        = glob($this->config['path'] . '*');

        foreach ($list as $path) {
            if (is_dir($path)) {
                $files = glob($path . DIRECTORY_SEPARATOR . '*.php');
                foreach ($files as $file) {
                    if (time() > filemtime($file) + $maxlifetime) {
                        unlink($file);
                    }
                }
            } elseif (time() > filemtime($path) + $maxlifetime) {
                unlink($path);
            }
        }
    }

    /**
     * 取得变量的存储文件名
     * @access protected
     * @param  string $name 缓存变量名
     * @param  bool   $auto 是否自动创建目录
     * @return string
     */
    protected function getFileName(string $name, bool $auto = false): string
    {
        if ($this->config['prefix']) {
            // 使用子目录
            $name = $this->config['prefix'] . DIRECTORY_SEPARATOR . 'sess_' . $name;
        } else {
            $name = 'sess_' . $name;
        }

        $filename = $this->config['path'] . $name . '.php';
        $dir      = dirname($filename);

        if ($auto && !is_dir($dir)) {
            try {
                mkdir($dir, 0755, true);
            } catch (\Exception $e) {
                // 创建失败
            }
        }

        return $filename;
    }

    /**
     * 读取Session
     * @access public
     * @param  string $sessID
     * @return string
     */
    public function read(string $sessID): string
    {
        $filename = $this->getFileName($sessID);

        $content = is_file($filename) ? file_get_contents($filename) : false;

        if (false === $content) {
            return '';
        }

        $expire = (int) substr($content, 8, 12);

        if (0 != $expire && time() > filemtime($filename) + $expire) {
            //缓存过期删除缓存文件
            $this->unlink($filename);
            return '';
        }

        $content = substr($content, 32);

        if ($this->config['data_compress'] && function_exists('gzcompress')) {
            //启用数据压缩
            $content = gzuncompress($content);
        }

        return $content;
    }

    /**
     * 写入Session
     * @access public
     * @param  string $sessID
     * @param  string $sessData
     * @return bool
     */
    public function write(string $sessID, string $sessData): bool
    {
        $expire = $this->config['expire'];

        $expire = $this->getExpireTime($expire);

        $filename = $this->getFileName($sessID, true);

        $data = $sessData;

        if ($this->config['data_compress'] && function_exists('gzcompress')) {
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
     * 删除Session
     * @access public
     * @param  string $sessID
     * @return array
     */
    public function delete(string $sessID): bool
    {
        try {
            return $this->unlink($this->getFileName($sessID));
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取有效期
     * @access protected
     * @param  integer|\DateTimeInterface $expire 有效期
     * @return int
     */
    protected function getExpireTime($expire): int
    {
        if ($expire instanceof \DateTimeInterface) {
            $expire = $expire->getTimestamp() - time();
        }

        return (int) $expire;
    }

    /**
     * 判断文件是否存在后，删除
     * @access private
     * @param  string $path
     * @return bool
     */
    private function unlink(string $file): bool
    {
        return is_file($file) && unlink($file);
    }

}
