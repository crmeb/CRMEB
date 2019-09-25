<?php
namespace crmeb\services\storage;

use crmeb\services\SystemConfigService;
use OSS\Core\OssException;
use OSS\OssClient;
use think\facade\Cache;

/**
 * 阿里云OSS上传
 * Class OSS
 */
class OSS
{
    protected static $accessKey;

    protected static $secretKey;

    protected static $auth = null;

    //TODO 空间域名 Domain
    protected static $uploadUrl;

    //TODO 存储空间名称  公开空间
    protected static $storageName;


    /**
     * TODO 初始化
     * @return null|OssClient
     * @throws \OSS\Core\OssException
     */
    protected static function autoInfo(){
        if(($storageName = Cache::get('storageName')) && ($uploadUrl = Cache::get('uploadUrl')) && ($accessKey = Cache::get('accessKey')) && ($secretKey = Cache::get('secretKey'))){
            self::$accessKey = $accessKey;
            self::$secretKey = $secretKey;
            self::$uploadUrl = $uploadUrl;
            self::$storageName = $storageName;
        }else{
            self::$accessKey = trim(SystemConfigService::get('accessKey'));
            self::$secretKey = trim(SystemConfigService::get('secretKey'));
            self::$uploadUrl = trim(SystemConfigService::get('uploadUrl')).'/';
            self::$storageName = trim(SystemConfigService::get('storage_name'));
            Cache::set('accessKey',self::$accessKey);
            Cache::set('secretKey',self::$secretKey);
            Cache::set('uploadUrl',self::$uploadUrl);
            Cache::set('storageName',self::$storageName);
        }
        if(!self::$accessKey || !self::$secretKey || !self::$uploadUrl || !self::$storageName){
            exception('请设置 secretKey 和 accessKey 和 空间域名 和 存储空间名称');
        }
        if(self::$auth == null) {
            self::$auth = new OssClient(self::$accessKey,self::$secretKey,self::$uploadUrl);
            if(!self::$auth->doesBucketExist(self::$storageName)) self::$auth->createBucket(self::$storageName,self::$auth::OSS_ACL_TYPE_PUBLIC_READ_WRITE);
        }
        return self::$auth;
    }

    /**
     * TODO 文件上传 名称
     * @param string $filename
     * @return string
     */
    public static function uploadImage($filename = 'image'){
        $request = app('request');
        $file = $request->file($filename);
        $filePath = $file->getRealPath();
        $ext = $file->getOriginalExtension();
        $key = substr(md5($file->getRealPath()) , 0, 5). date('YmdHis') . rand(0, 9999) . '.' . $ext;
        try{
            self::autoInfo();
            return self::$auth->uploadFile(self::$storageName,$key,$filePath);
        }catch (OssException $e){
            return $e->getMessage();
        }
    }

    /**
     * TODO 文件上传 内容
     * @param $key
     * @param $content
     * @return string
     */
    public static function uploadImageStream($key, $content){
        try{
            self::autoInfo();
            return self::$auth->putObject(self::$storageName,$key,$content);
        }catch (OssException $e){
            return $e->getMessage();
        }
    }

    /**
     * TODO 删除资源
     * @param $key
     * @return mixed
     */
    public static function delete($key){
        try {
            self::autoInfo();
            return self::$auth->deleteObject(self::$storageName,$key);
        } catch (OssException $e) {
            return $e->getMessage();
        }
    }

}