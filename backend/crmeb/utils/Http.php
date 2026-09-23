<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\utils;

use think\facade\Log;

/**
 * Class Http
 * @package crmeb\utils
 */
class Http
{
    /**
     * 错误信息
     * @var string
     */
    private static $curlError;

    /**
     * header头信息
     * @var string
     */
    private static $headerStr;

    /**
     * 请求状态
     * @var int
     */
    private static $status;

    /**
     * @return string
     */
    public static function getCurlError()
    {
        return self::$curlError;
    }

    /**
     * @return mixed
     */
    public static function getStatus()
    {
        return self::$status;
    }

    /**
     * 模拟GET发起请求
     * @param $url
     * @param array $data
     * @param bool $header
     * @param int $timeout
     * @return bool|string
     */
    public static function getRequest($url, $data = array(), $header = false, $timeout = 10)
    {
        if (!empty($data)) {
            $url .= (stripos($url, '?') === false ? '?' : '&');
            $url .= (is_array($data) ? http_build_query($data) : $data);
        }
        return self::request($url, 'get', array(), $header, $timeout);
    }

    /**
     * curl 请求
     * @param $url
     * @param string $method
     * @param array $data
     * @param bool $header
     * @param int $timeout
     * @return bool|string
     */
    public static function request($url, $method = 'get', $data = array(), $header = false, $timeout = 15)
    {
        self::$status = null;
        self::$curlError = null;
        self::$headerStr = null;

        $curl = curl_init($url);
        $method = strtoupper($method);
        //请求方式
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        //携带参数
        if ($method == 'POST') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, is_array($data) ? http_build_query($data) : $data);
        } elseif ($method == 'GET' && count($data)) {
            $url .= '?' . http_build_query($data);
            curl_setopt($curl, CURLOPT_URL, $url);
        }
        //超时时间
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        //设置header头
        if ($header !== false) curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        //固定 HTTP/1.1：部分第三方网关（如一号通）在 HTTP/2 的 TLS 协商下会异常断开连接，
        //导致握手报错 error:0A000126 unexpected eof while reading，固定 1.1 可规避（与流式接口处理一致）
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        //返回抓取数据
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //输出header头信息
        curl_setopt($curl, CURLOPT_HEADER, true);
        //TRUE 时追踪句柄的请求字符串，从 PHP 5.1.3 开始可用。这个很关键，就是允许你查看请求header
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        //https请求：校验证书与主机名，防止中间人窃取篡改（部分环境缺少 CA 证书时
        //需安装系统 ca-certificates 或在 php.ini 配置 openssl.capath）
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        self::$curlError = curl_error($curl);

        list($content, $status) = [curl_exec($curl), curl_getinfo($curl), curl_close($curl)];
        self::$status = $status;
        self::$headerStr = trim(substr($content, 0, $status['header_size']));
        $content = trim(substr($content, $status['header_size']));
        return (intval($status["http_code"]) === 200) ? $content : false;
    }

    /**
     * 模拟POST发起请求
     * @param $url
     * @param $data
     * @param bool $header
     * @param int $timeout
     * @return bool|string
     */
    public static function postRequest($url, $data = array(), $header = false, $timeout = 10)
    {
        return self::request($url, 'post', $data, $header, $timeout);
    }

    /**
     * 获取header头字符串类型
     * @return mixed
     */
    public static function getHeaderStr()
    {
        return self::$headerStr;
    }

    /**
     * 获取header头数组类型
     * @return array
     */
    public static function getHeader()
    {
        $headArr = explode("\r\n", self::$headerStr);
        return $headArr;
    }

}
