<?php
namespace crmeb\services\storage;

use crmeb\services\SystemConfigService;
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use Qiniu\Config;
use think\facade\Cache;


/**
 * TODO 七牛云上传
 * Class Qiniu
 */
class Qiniu
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
     * @return null|Auth
     * @throws \Exception
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
        if(self::$auth == null) self::$auth = new Auth(self::$accessKey,self::$secretKey);
        return self::$auth;
    }

    /**
     * TODO 获取上传图片视频token和domain
     * @return array
     */
    public static function getToKenAndDomainI(){
        $token = self::autoInfo()->uploadToken(self::$storageName);
        $domain = self::$uploadUrl;
        $fileName = md5(rand(1000,9999). date('YmdHis') . rand(0, 9999));
        return compact('token','domain','fileName');
    }

    /**
     * TODO 图片上传 名称
     * @param string $filename
     * @return array
     * @throws \Exception
     */
    public static function uploadImage($filename = 'image'){
        $request = app('request');
        $file = $request->file($filename);
        $filePath = $file->getRealPath();
        $ext = $file->getOriginalExtension();
        $key = substr(md5($file->getRealPath()) , 0, 5). date('YmdHis') . rand(0, 9999) . '.' . $ext;
        $token = self::autoInfo()->uploadToken(self::$storageName);
        try{
            $uploadMgr = new UploadManager();
            return $uploadMgr->putFile($token, $key, $filePath);
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * TODO 图片上传 内容
     * @param $key
     * @param $content
     * @return array|string
     * @throws \Exception
     */
    public static function uploadImageStream($key, $content){
        $token = self::autoInfo()->uploadToken(self::$storageName, $key);
        try{
            $uploadMgr = new UploadManager();
            return $uploadMgr->put($token, $key, $content);
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * TODO 图片下载链接
     * @param $key  uploadImage()  返回的key
     * @param string $type 是否设置图片大小
     * @param string $imageUrl 图片访问链接
     * @param int $time  图片链接最后的访问时间
     * @return array
     */
    public static function imageUrl($key, $type = '', $imageUrl = '', $time = 0){
        return ['code'=>200,'name'=>$key,'dir'=>self::$uploadUrl.$key,'thumb_path'=>self::$uploadUrl.$key,'time'=>time()];
//        $imageValue = !strlen(trim($type)) ? self::$uploadUrl.$key : self::$uploadUrl.$key.self::getType($type);
//        if($time > time() && !strlen(trim($imageUrl))) return ['code'=>100,'dir'=>$imageUrl,'thumb_path'=>$imageUrl,'time'=>$time];
//        $imageUrl = self::autoInfo()->privateDownloadUrl($imageValue);
//        return ['code'=>200,'name'=>$key,'dir'=>$imageUrl,'thumb_path'=>$imageUrl,'time'=>bcadd(time(),3600,0)];
    }

    /**
     * TODO 获取图片时转换图片大小
     * @param $imageType
     * @return string
     */
    public static function getType($imageType){
        $type = '';
        switch ($imageType){
            case "8x6":
                $type='?imageView2/1/w/800/h/600';
                break;
        }
        return $type;
    }

    /**
     * TODO 删除资源
     * @param $key
     * @param $bucket
     * @return mixed
     */
    public static function delete($key){
        $bucketManager = new BucketManager(self::autoInfo(),new Config());
        return $bucketManager->delete(self::$storageName, $key);
    }
}