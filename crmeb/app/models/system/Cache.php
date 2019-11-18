<?php
namespace app\models\system;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * 数据缓存
 * Class Express
 * @package app\models\system
 */
class Cache extends BaseModel
{
    use ModelTrait;

    const EXPIRE = 0;
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'cache';

    /**
     * 获取数据缓存
     * @param string $key
     * @return result
     */
    public static function getDbCache(string $key)
    {
        self::delectDeOverdueDbCache();
         $result = self::where('key',$key)->value('result');
         return json_decode($result,true);
    }

    /**
     * 设置数据缓存存在则更新，没有则写入
     * @param string $key
     * @param string | array $result
     * @param int $expire
     * @return void
     */
    public static function setDbCache(string $key,$result,$expire = self::EXPIRE)
    {
        self::delectDeOverdueDbCache();
        $addTime = $expire ? time() + $expire : 0;
        if(self::be(['key'=>$key])){
            return self::where(['key'=>$key])->update(['result'=>json_encode($result),'add_time'=>$addTime]);
        }else{
            return self::create(['key'=>$key,'result'=>json_encode($result),'add_time'=>$addTime]);
        }
    }

    /**
     * 删除失效缓存
     */
    public static function delectDeOverdueDbCache()
    {
    }

    /**
     * 删除某个缓存
     * @param string $key
     */
    public static function delectDbCache(string $key = '')
    {
        if($key)
            return self::where('key',$key)->delete();
        else
            return self::delete();
    }

}