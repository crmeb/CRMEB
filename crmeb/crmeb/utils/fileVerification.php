<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace crmeb\utils;

use Exception;

/**
 * 签名计算
 * Class fileVerification
 * @package crmeb\utils
 */
class fileVerification
{
    public $path = "";
    public $fileValue = "";

    /**
     * 项目路径
     * @param string $path
     * @return string
     * @throws Exception
     */
    public function getSignature(string $path): string
    {
        if (!is_dir($path) && !is_file($path)) {
            throw new Exception($path . " 不是有效的文件或目录!");
        }

        $appPath = $path . DS . 'app';
        if (!is_dir($appPath)) {
            throw new Exception($appPath . " 不是有效的目录!");
        }

        $crmebPath = $path . DS . 'crmeb';
        if (!is_dir($crmebPath)) {
            throw new Exception($crmebPath . " 不是有效的目录!");
        }

        $this->path = $appPath;
        $this->getFileSignature($appPath);
        $this->path = $crmebPath;
        $this->getFileSignature($crmebPath);
        return md5($this->fileValue);
    }

    /**
     * 计算签名
     * @param string $path
     * @return void
     * @throws Exception
     */
    public function getFileSignature(string $path)
    {
        if (!is_dir($path)) {
            $this->fileValue .= @md5_file($path);
        } else {
            if (!$dh = opendir($path)) throw new Exception($path . " File open failed!");
            while (($file = readdir($dh)) != false) {
                if ($file == "." || $file == "..") {
                    continue;
                } else {
                    $this->getFileSignature($path . DS . $file);
                }
            }
            closedir($dh);
        }
    }
}