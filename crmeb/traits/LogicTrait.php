<?php
/**
 * Created by CRMEB.
 * User: 136327134@qq.com
 * Date: 2019/4/9 16:50
 */

namespace crmeb\traits;

trait LogicTrait
{

    protected $items = [];

    /*
     * 魔术方法 对不可访问或不存在的属性调用
     *
     * */
    public function __isset($name)
    {

    }

    /*
     * 魔术方法 对不可访问或不存在的属性进行unset时被调用
     * */
    public function __unset($name)
    {

    }

    /*
     * 静态方法调用
     * @param  string $method 调用方法
     * @param  mixed  $args   参数
     * */
    public static function __callStatic($method,$args)
    {

    }

    /*
     * 执行本类的方法
     * @param string $carryoutname 方法名
     * @return boolean
     * */
    public static function CarryOut($carryoutname)
    {
        $methords = get_class_methods(self::class);
        if(!in_array($carryoutname,$methords)) return false;
        try{
            return (new self)->$carryoutname();
        }catch (\Exception $e){
            return false;
        }
    }

    /*
     * 配置参数
     *
     * */
    protected function setConfig(array $config=[])
    {
        foreach ($config as $key => $value) {
            $this->set($this->items,$key, $value);
        }
    }

    /*
     * 设置参数
     * @param array $array
     * @param string $key
     * @param string $value
     * */
    protected function set(&$array, $key, $value)
    {
        if (is_null($key)) return $array = $value;
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $array[array_shift($keys)] = $value;
        return $array;
    }

    /*
     * 实例化类
     *
     * */
    protected function registerProviders()
    {
        foreach ($this->providers as $key=>$provider)
        {
            $this->register(new $provider(),$key);
        }
    }

    /*
     * 获取类内配置信息
     * @param object $pimple
     * @return this
     * */
    protected function register($pimple,$key)
    {
        $response=$pimple->register($this->items);
        if(is_array($response)) {
            list($key,$provider)=$response;
            $this->$key= $provider;
        }else if(is_string($key)){
            $this->$key= $pimple;
        }
        return $this;
    }

    /*
     * 实例化本类
     * @param array $config
     * @return this
     * */
    public static function instance($config=[])
    {
        $that=new self();
        $that->setConfig($config);
        $that->registerProviders();
        return $that;
    }
}