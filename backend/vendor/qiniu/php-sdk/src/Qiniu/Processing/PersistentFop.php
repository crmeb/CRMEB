<?php

namespace Qiniu\Processing;

use Qiniu\Config;
use Qiniu\Http\Error;
use Qiniu\Http\Client;
use Qiniu\Http\Proxy;
use Qiniu\Zone;

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

    /**
     * @var 代理信息
     */
    private $proxy;


    public function __construct($auth, $config = null, $proxy = null, $proxy_auth = null, $proxy_user_password = null)
    {
        $this->auth = $auth;
        if ($config == null) {
            $this->config = new Config();
        } else {
            $this->config = $config;
        }
        $this->proxy = new Proxy($proxy, $proxy_auth, $proxy_user_password);
    }

    /**
     * 对资源文件进行异步持久化处理
     * @param string $bucket 资源所在空间
     * @param string $key 待处理的源文件
     * @param string|array $fops 待处理的pfop操作，多个pfop操作以array的形式传入。
     *                    eg. avthumb/mp3/ab/192k, vframe/jpg/offset/7/w/480/h/360
     * @param string $pipeline 资源处理队列
     * @param string $notify_url 处理结果通知地址
     * @param bool $force 是否强制执行一次新的指令
     * @param int $type 为 `1` 时开启闲时任务
     *
     *
     * @return array 返回持久化处理的 persistentId 与可能出现的错误。
     *
     * @link http://developer.qiniu.com/docs/v6/api/reference/fop/
     */
    public function execute(
        $bucket,
        $key,
        $fops = null,
        $pipeline = null,
        $notify_url = null,
        $force = false,
        $type = null,
        $workflow_template_id = null
    ) {
        if (is_array($fops)) {
            $fops = implode(';', $fops);
        }

        if (!$fops && !$workflow_template_id) {
            throw new \InvalidArgumentException('Must provide one of fops or template_id');
        }

        $params = array('bucket' => $bucket, 'key' => $key);
        \Qiniu\setWithoutEmpty($params, 'fops', $fops);
        \Qiniu\setWithoutEmpty($params, 'pipeline', $pipeline);
        \Qiniu\setWithoutEmpty($params, 'notifyURL', $notify_url);
        \Qiniu\setWithoutEmpty($params, 'type', $type);
        \Qiniu\setWithoutEmpty($params, 'workflowTemplateID', $workflow_template_id);
        if ($force) {
            $params['force'] = 1;
        }
        $data = http_build_query($params);
        $scheme = "http://";
        if ($this->config->useHTTPS === true) {
            $scheme = "https://";
        }
        $apiHost = $this->getApiHost();
        $url = $scheme . $apiHost . '/pfop/';
        $headers = $this->auth->authorization($url, $data, 'application/x-www-form-urlencoded');
        $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        $response = Client::post($url, $data, $headers, $this->proxy->makeReqOpt());
        if (!$response->ok()) {
            return array(null, new Error($url, $response));
        }
        $r = $response->json();
        $id = $r['persistentId'];
        return array($id, null);
    }

    /**
     * @param string $id
     * @return array 返回任务状态与可能出现的错误
     */
    public function status($id)
    {
        $scheme = "http://";

        if ($this->config->useHTTPS === true) {
            $scheme = "https://";
        }
        $apiHost = $this->getApiHost();
        $url = $scheme . $apiHost . "/status/get/prefop?id=$id";
        $response = Client::get($url, array(), $this->proxy->makeReqOpt());
        if (!$response->ok()) {
            return array(null, new Error($url, $response));
        }
        return array($response->json(), null);
    }

    private function getApiHost()
    {
        if (!empty($this->config->zone) && !empty($this->config->zone->apiHost)) {
            $apiHost = $this->config->zone->apiHost;
        } else {
            $apiHost = Config::API_HOST;
        }
        return $apiHost;
    }
}
