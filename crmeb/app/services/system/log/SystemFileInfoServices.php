<?php

namespace app\services\system\log;

use app\dao\system\log\SystemFileInfoDao;
use app\services\BaseServices;

/**
 * @author 吴汐
 * @email 442384644@qq.com
 * @date 2023/04/07
 */
class SystemFileInfoServices extends BaseServices
{
    /**
     * 构造方法
     * SystemLogServices constructor.
     * @param SystemFileInfoDao $dao
     */
    public function __construct(SystemFileInfoDao $dao)
    {
        $this->dao = $dao;
    }

    public function syncfile()
    {
        $list = $this->flattenArray($this->scanDirectory());
        $this->dao->saveAll($list);
    }

    public function scanDirectory($dir = '')
    {
        if ($dir == '') $dir = root_path();
        $result = array();
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $path = $dir . '/' . $file;
                $fileInfo = array(
                    'name' => $file,
                    'update_time' => date('Y-m-d H:i:s', filemtime($path)),
                    'create_time' => date('Y-m-d H:i:s', filectime($path)),
                    'path' => str_replace(root_path(), '', $dir),
                    'full_path' => str_replace(root_path(), '', $path),
                );
                if (is_dir($path)) {
                    $fileInfo['type'] = 'dir';
                    $fileInfo['contents'] = $this->scanDirectory($path);
                } else {
                    $fileInfo['type'] = 'file';
                }
                $result[] = $fileInfo;
            }
        }
        return $result;
    }

    public function flattenArray($arr)
    {
        $result = array();
        foreach ($arr as $item) {
            $result[] = array(
                'name' => $item['name'],
                'type' => $item['type'],
                'update_time' => $item['update_time'],
                'create_time' => $item['create_time'],
                'path' => $item['path'],
                'full_path' => $item['full_path'],
            );
            if (isset($item['contents'])) {
                $result = array_merge($result, $this->flattenArray($item['contents']));
            }
        }
        return $result;
    }
}