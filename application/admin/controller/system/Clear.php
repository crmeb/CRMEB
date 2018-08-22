<?php

namespace app\admin\controller\system;

use app\admin\controller\AuthController;
use service\CacheService;
use service\JsonService as Json;

/**
 * 首页控制器
 * Class Clear
 * @package app\admin\controller
 *
 */
class Clear extends AuthController
{
    public function index()
    {
        return $this->fetch();
    }
    public function refresh_cache(){
        if(function_exists('shell_exec')){
            `php think optimize:schema`;
            `php think optimize:autoload`;
            `php think optimize:route`;
            `php think optimize:config`;
        }else if(function_exists('exec')){
            exec('php think optimize:schema');
            exec('php think optimize:autoload');
            exec('php think optimize:route');
            exec('php think optimize:config');
        }
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


