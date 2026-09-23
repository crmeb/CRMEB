<?php

namespace Qiniu\Storage;

use Qiniu\Auth;
use Qiniu\Config;
use Qiniu\Zone;
use Qiniu\Http\Client;
use Qiniu\Http\Error;
use Qiniu\Http\Proxy;

/**
 * 主要涉及了内容审核接口的实现，具体的接口规格可以参考
 *
 * @link https://developer.qiniu.com/censor/api/5620/video-censor
 */
final class ArgusManager
{
    private $auth;
    private $config;
    private $proxy;

    public function __construct(
        Auth $auth,
        Config $config = null,
        $proxy = null,
        $proxy_auth = null,
        $proxy_user_password = null
    ) {
        $this->auth = $auth;
        if ($config == null) {
            $this->config = new Config();
        } else {
            $this->config = $config;
        }
        $this->proxy = new Proxy($proxy, $proxy_auth, $proxy_user_password);
    }

    /**
     * 视频审核
     *
     * @param string $body body信息
     *
     * @return array 成功返回NULL，失败返回对象Qiniu\Http\Error
     * @link  https://developer.qiniu.com/censor/api/5620/video-censor
     */
    public function censorVideo($body)
    {
        $path = '/v3/video/censor';

        return $this->arPost($path, $body);
    }


    /**
     * 图片审核
     *
     * @param string $body
     *
     * @return array 成功返回NULL，失败返回对象Qiniu\Http\Error
     * @link  https://developer.qiniu.com/censor/api/5588/image-censor
     */
    public function censorImage($body)
    {
        $path = '/v3/image/censor';

        return $this->arPost($path, $body);
    }

    /**
     * 查询视频审核结果
     *
     * @param string $jobid 任务ID
     * @return array
     * @link  https://developer.qiniu.com/censor/api/5620/video-censor
     */
    public function censorStatus($jobid)
    {
        $scheme = "http://";

        if ($this->config->useHTTPS === true) {
            $scheme = "https://";
        }
        $url = $scheme . Config::ARGUS_HOST . "/v3/jobs/video/$jobid";
        $response = $this->get($url);
        if (!$response->ok()) {
            return array(null, new Error($url, $response));
        }
        return array($response->json(), null);
    }

    private function getArHost()
    {
        $scheme = "http://";
        if ($this->config->useHTTPS === true) {
            $scheme = "https://";
        }
        return $scheme . Config::ARGUS_HOST;
    }

    private function arPost($path, $body = null)
    {
        $url = $this->getArHost() . $path;
        return $this->post($url, $body);
    }

    private function get($url)
    {
        $headers = $this->auth->authorizationV2($url, 'GET');

        return Client::get($url, $headers, $this->proxy->makeReqOpt());
    }

    private function post($url, $body)
    {
        $headers = $this->auth->authorizationV2($url, 'POST', $body, 'application/json');
        $headers['Content-Type'] = 'application/json';
        $ret = Client::post($url, $body, $headers, $this->proxy->makeReqOpt());
        if (!$ret->ok()) {
            return array(null, new Error($url, $ret));
        }
        $r = ($ret->body === null) ? array() : $ret->json();
        if (strstr($url, "video")) {
            $jobid = $r['job'];
            return array($jobid, null);
        }
        return array($r, null);
    }
}
