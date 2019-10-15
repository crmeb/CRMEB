<?php
namespace Qiniu\Processing;

use Qiniu\Config;
use Qiniu\Http\Client;
use Qiniu\Http\Error;
use Qiniu\Processing\Operation;

/**
 * 持久化处理类,该类用于主动触发异步持久化操作.
 *
 * @link http://developer.qiniu.com/docs/v6/api/reference/fop/pfop/pfop.html
 */
final class PersistentFop
{
    /**
     * @var 账号管理密钥对，Auth对象
     */
    private $auth;

    /*
     * @var 配置对象，Config 对象
     * */
    private $config;


    public function __construct($auth, $config = null)
    {
        $this->auth = $auth;
        if ($config == null) {
            $this->config = new Config();
        } else {
            $this->config = $config;
        }
    }

    /**
     * 对资源文件进行异步持久化处理
     * @param $bucket     资源所在空间
     * @param $key        待处理的源文件
     * @param $fops       string|array  待处理的pfop操作，多个pfop操作以array的形式传入。
     *                    eg. avthumb/mp3/ab/192k, vframe/jpg/offset/7/w/480/h/360
     * @param $pipeline   资源处理队列
     * @param $notify_url 处理结果通知地址
     * @param $force      是否强制执行一次新的指令
     *
     *
     * @return array 返回持久化处理的persistentId, 和返回的错误。
     *
     * @link http://developer.qiniu.com/docs/v6/api/reference/fop/
     */
    public function execute($bucket, $key, $fops, $pipeline = null, $notify_url = null, $force = false)
    {
        if (is_array($fops)) {
            $fops = implode(';', $fops);
        }
        $params = array('bucket' => $bucket, 'key' => $key, 'fops' => $fops);
        \Qiniu\setWithoutEmpty($params, 'pipeline', $pipeline);
        \Qiniu\setWithoutEmpty($params, 'notifyURL', $notify_url);
        if ($force) {
            $params['force'] = 1;
        }
        $data = http_build_query($params);
        $scheme = "http://";
        if ($this->config->useHTTPS === true) {
            $scheme = "https://";
        }
        $url = $scheme . Config::API_HOST . '/pfop/';
        $headers = $this->auth->authorization($url, $data, 'application/x-www-form-urlencoded');
        $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        $response = Client::post($url, $data, $headers);
        if (!$response->ok()) {
            return array(null, new Error($url, $response));
        }
        $r = $response->json();
        $id = $r['persistentId'];
        return array($id, null);
    }

    public function status($id)
    {
        $scheme = "http://";

        if ($this->config->useHTTPS === true) {
            $scheme = "https://";
        }
        $url = $scheme . Config::API_HOST . "/status/get/prefop?id=$id";
        $response = Client::get($url);
        if (!$response->ok()) {
            return array(null, new Error($url, $response));
        }
        return array($response->json(), null);
    }
}
