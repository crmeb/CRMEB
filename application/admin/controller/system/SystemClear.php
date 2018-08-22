<?php

namespace app\admin\controller\system;

use app\admin\controller\AuthController;
use service\CacheService;
use service\JsonService as Json;

/**
 * 清除缓存
 * Class Clear
 * @package app\admin\controller
 *
 */
class systemClear extends AuthController
{
    public function index()
    {
        return $this->fetch();
    }
    public function refresh_cache(){
        `php think optimize:schema`;
        `php think optimize:autoload`;
        `php think optimize:route`;
        `php think optimize:config`;
        return Json::successful('数据缓存刷新成功!');
    }
    public function delete_cache(){
        $this->delDirAndFile("./runtime/temp");
        $this->delDirAndFile("./runtime/cache");
        return Json::successful('清除缓存成功!');
    }
    public function delete_log(){
        $this->delDirAndFile("./runtime/log");
        return Json::successful('清除日志成功!');
    }
    function delDirAndFile($dirName,$subdir=true){
        if ($handle = opendir("$dirName")){
            while(false !== ($item = readdir($handle))){
                if($item != "." && $item != ".."){
                    if(is_dir("$dirName/$item"))
                        $this->delDirAndFile("$dirName/$item",false);
                    else
                        @unlink("$dirName/$item");
                }
            }
            closedir($handle);
            if(!$subdir) @rmdir($dirName);
        }
    }
}


