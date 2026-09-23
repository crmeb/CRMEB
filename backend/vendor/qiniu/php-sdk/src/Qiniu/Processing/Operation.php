<?php

namespace Qiniu\Processing;

use Qiniu\Http\Client;
use Qiniu\Http\Error;
use Qiniu\Http\Proxy;

final class Operation
{

    private $auth;
    private $token_expire;
    private $domain;
    private $proxy;

    public function __construct(
        $domain,
        $auth = null,
        $token_expire = 3600,
        $proxy = null,
        $proxy_auth = null,
        $proxy_user_password = null
    ) {
        $this->auth = $auth;
        $this->domain = $domain;
        $this->token_expire = $token_expire;
        $this->proxy = new Proxy($proxy, $proxy_auth, $proxy_user_password);
    }


    /**
     * 对资源文件进行处理
     *
     * @param string $key 待处理的资源文件名
     * @param string $fops string|array  fop操作，多次fop操作以array的形式传入。
     *                eg. imageView2/1/w/200/h/200, imageMogr2/thumbnail/!75px
     *
     * @return array 文件处理后的结果及错误。
     *
     * @link http://developer.qiniu.com/docs/v6/api/reference/fop/
     */
    public function execute($key, $fops)
    {
        $url = $this->buildUrl($key, $fops);
        $resp = Client::get($url, array(), $this->proxy->makeReqOpt());
        if (!$resp->ok()) {
            return array(null, new Error($url, $resp));
        }
        if ($resp->json() !== null) {
            return array($resp->json(), null);
        }
        return array($resp->body, null);
    }

    public function buildUrl($key, $fops, $protocol = 'http')
    {
        if (is_array($fops)) {
            $fops = implode('|', $fops);
        }

        $url = $protocol . "://$this->domain/$key?$fops";
        if ($this->auth !== null) {
            $url = $this->auth->privateDownloadUrl($url, $this->token_expire);
        }

        return $url;
    }
}
