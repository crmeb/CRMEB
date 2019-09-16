<?php
namespace crmeb\services\storage;

use crmeb\services\SystemConfigService;
use Guzzle\Http\EntityBody;
use Qcloud\Cos\Client;
use think\facade\Cache;

/**
 * TODO 腾讯云COS文件上传
 * Class COS
 */
class COS
{
    protected static $accessKey;

    protected static $secretKey;

    protected static $auth = null;

    //TODO 空间域名 Domain
    protected static $uploadUrl;

    //TODO 存储空间名称  公开空间
    protected static $storageName;

    //TODO COS使用  所属地域
    protected static $storageRegion;


    /**
     * TODO 初始化
     * @return null|Client
     * @throws \Exception
     */
    protected static function autoInfo(){
        if(($storageRegion = Cache::get('storageRegion')) && ($storageName = Cache::get('storageName')) && ($uploadUrl = Cache::get('uploadUrl')) && ($accessKey = Cache::get('accessKey')) && ($secretKey = Cache::get('secretKey'))){
            self::$accessKey = $accessKey;
            self::$secretKey = $secretKey;
            self::$uploadUrl = $uploadUrl;
            self::$storageName = $storageName;
            self::$storageRegion = $storageRegion;
        }else{
            self::$accessKey = trim(SystemConfigService::get('accessKey'));
            self::$secretKey = trim(SystemConfigService::get('secretKey'));
            self::$uploadUrl = trim(SystemConfigService::get('uploadUrl')).'/';
            self::$storageName = trim(SystemConfigService::get('storage_name'));
            self::$storageRegion = trim(SystemConfigService::get('storage_region'));
            Cache::set('accessKey',self::$accessKey);
            Cache::set('secretKey',self::$secretKey);
            Cache::set('uploadUrl',self::$uploadUrl);
            Cache::set('storageName',self::$storageName);
            Cache::set('storageRegion',self::$storageRegion);
        }
        if(!self::$accessKey || !self::$secretKey || !self::$uploadUrl || !self::$storageName){
            exception('请设置 secretKey 和 accessKey 和 空间域名 和 存储空间名称');
        }
        if(self::$auth == null) {
            self::$auth = new Client([
                'region'=>self::$storageRegion,
                'credentials'=>[
                    'secretId'=>self::$accessKey,
                    'secretKey'=>self::$secretKey,
                ]
            ]);
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
        try {
            self::autoInfo();
            return [self::$uploadUrl.$key,self::$auth->putObject([
                'Bucket' => self::$storageName,
                'Key' => $key,
                'Body' => fopen($filePath, 'rb')
            ])];
        } catch (\Exception $e) {
            return [false,$e->getMessage()];
        }
    }

    /**
     * TODO 文件上传 内容
     * @param $key
     * @param $content
     * @return string
     */
    public static function uploadImageStream($key, $content){
        try {
            self::autoInfo();
            return [self::$uploadUrl.$key,self::$auth->putObject([
                'Bucket' => self::$storageName,
                'Key' => $key,
                'Body' => $content
            ])];
        } catch (\Exception $e) {
            return [false,$e->getMessage()];
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
            return self::$auth->deleteObject([
                'Bucket' => self::$storageName,
                'Key' => $key
            ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * TODO 转为文件流
     * @param $resource
     * @return EntityBody
     */
    public static function resourceStream($resource)
    {
        return EntityBody::factory($resource)->__toString();
    }

}