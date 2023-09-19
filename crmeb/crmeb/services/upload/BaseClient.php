<?php
/**
 *  +----------------------------------------------------------------------
 *  | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
 *  +----------------------------------------------------------------------
 *  | Author: CRMEB Team <admin@crmeb.com>
 *  +----------------------------------------------------------------------
 */

namespace crmeb\services\upload;

/**
 * 基础请求
 * Class BaseClient
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2023/5/18
 * @package crmeb\services\upload
 */
abstract class BaseClient
{

    /**
     * 是否解析为xml
     * @var bool
     */
    protected $isXml = true;

    /**
     *
     * @var []callable
     */
    protected $curlFn = [];

    /**
     * @param callable $curlFn
     * @return $this
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/18
     */
    public function middleware(callable $curlFn)
    {
        $this->curlFn[] = $curlFn;
        return $this;
    }

    /**
     * 发起请求
     * @param string $url
     * @param string $method
     * @param array $data
     * @param array $clientHeader
     * @param int $timeout
     * @return array|extend\cos\SimpleXMLElement
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/18
     */
    protected function requestClient(string $url, string $method, array $data = [], array $clientHeader = [], int $timeout = 10)
    {
        $headers = [];
        foreach ($clientHeader as $key => $item) {
            $headers[] = $key . ':' . $item;
        }
        $curl = curl_init($url);
        //请求方式
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        //post请求
        if (!empty($data['body'])) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data['body']);
        } else if (!empty($data['json'])) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data['json']));
        } else {
            $curlFn = $this->curlFn;
            foreach ($curlFn as $item) {
                if ($item instanceof \Closure) {
                    $curlFn($curl);
                }
            }
        }
        //超时时间
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        //设置header头
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        //返回抓取数据
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //输出header头信息
        curl_setopt($curl, CURLOPT_HEADER, true);
        //TRUE 时追踪句柄的请求字符串，从 PHP 5.1.3 开始可用。这个很关键，就是允许你查看请求header
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        //https请求
        if (1 == strpos("$" . $url, "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        [$content, $status] = [curl_exec($curl), curl_getinfo($curl)];
        $content = trim(substr($content, $status['header_size']));
        if ($this->isXml) {
            return XML::parse($content);
        } else {
            return json_decode($content, true);
        }
    }
}
