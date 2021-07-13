<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\system\log;


use app\dao\system\log\SystemFileDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use crmeb\services\FileService as FileClass;

/**
 * 文件校验
 * Class SystemFileServices
 * @package app\services\system\log
 */
class SystemFileServices extends BaseServices
{
    /**
     * 构造方法
     * SystemFileServices constructor.
     * @param SystemFileDao $dao
     */
    public function __construct(SystemFileDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取文件校验列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getFileList()
    {
        $rootPath = app()->getRootPath();
        $key = 'system_file_app_crmeb_public';
        $arr = CacheService::get(md5($key));
        if (!$arr) {
            $app = $this->getDir($rootPath . 'app');
            $extend = $this->getDir($rootPath . 'crmeb');
            $arr = array_merge($app, $extend);
            CacheService::set(md5($key), $arr, 3600 * 24);
        }
        $fileAll = [];//本地文件
        $cha = [];//不同的文件
        $len = strlen($rootPath);
        $file = $this->dao->getAll();//数据库中的文件
        if (empty($file)) {
            foreach ($arr as $k => $v) {
                $update_time = stat($v);
                $fileAll[$k]['cthash'] = md5_file($v);
                $fileAll[$k]['filename'] = substr($v, $len);
                $fileAll[$k]['atime'] = $update_time['atime'];
                $fileAll[$k]['mtime'] = $update_time['mtime'];
                $fileAll[$k]['ctime'] = $update_time['ctime'];
            }
            $data_num = array_chunk($fileAll, 100);
            $res = true;
            $res = $this->transaction(function () use ($data_num, $res) {
                foreach ($data_num as $k => $v) {
                    $res = $res && $this->dao->saveAll($v);
                }
                return $res;
            });
            if ($res) {
                $cha = [];//不同的文件
            } else {
                $cha = $fileAll;
            }
        } else {
            $file = array_combine(array_column($file, 'filename'), $file);
            foreach ($arr as $ko => $vo) {
                $update_time = stat($vo);
                $cthash = md5_file($vo);
                $cha[] = [
                    'filename' => $vo,
                    'cthash' => $cthash,
                    'atime' => date('Y-m-d H:i:s', $update_time['atime']),
                    'mtime' => date('Y-m-d H:i:s', $update_time['mtime']),
                    'ctime' => date('Y-m-d H:i:s', $update_time['ctime']),
                    'type' => '新增的',
                ];
                if (isset($file[$vo]) && $file[$vo] != $cthash) {
                    $cha[] = [
                        'type' => '已修改',
                    ];
                    unset($file[$vo]);
                }
            }
            foreach ($file as $k => $v) {
                $cha[] = [
                    'filename' => $v['filename'],
                    'cthash' => $v['cthash'],
                    'atime' => date('Y-m-d H:i:s', $v['atime']),
                    'mtime' => date('Y-m-d H:i:s', $v['mtime']),
                    'ctime' => date('Y-m-d H:i:s', $v['ctime']),
                    'type' => '已删除',

                ];
            }
        }
        $ctime = array_column($cha, 'ctime');
        array_multisort($ctime, SORT_DESC, $cha);
        return $cha;
    }

    /**
     * 获取文件夹中的文件 包括子文件
     * @param $dir
     * @return array
     */
    public function getDir($dir)
    {
        $data = [];
        $this->searchDir($dir, $data);
        return $data;
    }

    /**
     * 获取文件夹中的文件 包括子文件 不能直接用  直接使用  $this->getDir()方法 P156
     * @param $path
     * @param $data
     */
    public function searchDir($path, &$data)
    {
        if (is_dir($path) && !strpos($path, 'uploads')) {
            $files = scandir($path);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $this->searchDir($path . '/' . $file, $data);
                }
            }
        }
        if (is_file($path)) {
            $data[] = $path;
        }
    }

    //打开目录
    public function opendir()
    {
        $fileAll = array('dir' => [], 'file' => []);
        //根目录
        $rootdir = app()->getRootPath();
//        return $rootdir;
        //当前目录
        $request_dir = app('request')->param('dir');
        //防止查看站点以外的目录
        if (strpos($request_dir, $rootdir) === false) {
            $request_dir = $rootdir;
        }
        //判断是否是返回上级
        if (app('request')->param('superior') && !empty($request_dir)) {
            if (strpos(dirname($request_dir), $rootdir) !== false) {
                $dir = dirname($request_dir);
            } else {
                $dir = $rootdir;
            }

        } else {
            $dir = !empty($request_dir) ? $request_dir : $rootdir;
            $dir = rtrim($dir, DS) . DS . app('request')->param('filedir');
        }
        $list = scandir($dir);
        foreach ($list as $key => $v) {
            if ($v != '.' && $v != '..') {
                if (is_dir($dir . DS . $v)) {
                    $fileAll['dir'][] = FileClass::listInfo($dir . DS . $v);
                }
                if (is_file($dir . DS . $v)) {
                    $fileAll['file'][] = FileClass::listInfo($dir . DS . $v);
                }
            }
        }
        //兼容windows
        $uname = php_uname('s');
        if (strstr($uname, 'Windows') !== false) {
            $dir = ltrim($dir, '\\');
            $rootdir = str_replace('\\', '\\\\', $rootdir);
        }
        $list = array_merge($fileAll['dir'], $fileAll['file']);
        foreach ($list as $key => $value) {
            $list[$key]['real_path'] = str_replace($rootdir, '', $value['pathname']);
            $list[$key]['mtime'] = date('Y-m-d H:i:s', $value['mtime']);
        }
        return compact('dir', 'list');
    }

    //读取文件
    public function openfile($filepath)
    {
        $content = FileClass::readFile($filepath);//防止页面内嵌textarea标签
        $ext = FileClass::getExt($filepath);
        $extarray = [
            'js' => 'text/javascript'
            , 'php' => 'text/x-php'
            , 'html' => 'text/html'
            , 'sql' => 'text/x-mysql'
            , 'css' => 'text/x-scss'];
        $mode = empty($extarray[$ext]) ? '' : $extarray[$ext];
        return compact('content', 'mode', 'filepath');
    }

    //保存文件
    public function savefile($filepath,$comment)
    {
        //兼容windows
        $uname = php_uname('s');
        if (strstr($uname, 'Windows') !== false)
            $filepath = ltrim(str_replace('/', DS, $filepath), '.');
        if (!FileClass::isWritable($filepath)) {
            throw new AdminException('没有权限');
        }
        return FileClass::writeFile($filepath, $comment);
    }
}
