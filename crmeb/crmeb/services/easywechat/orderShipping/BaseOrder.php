<?php

namespace crmeb\services\easywechat\orderShipping;

use crmeb\exceptions\AdminException;
use EasyWeChat\Core\AbstractAPI;
use EasyWeChat\Core\AccessToken;
use EasyWeChat\Support\Collection;


class BaseOrder extends AbstractAPI
{

    public $config;
    public $accessToken;

    const BASE_API = 'https://api.weixin.qq.com/';

    const ORDER = 'wxa/sec/order/';
    const EXPRESS = 'cgi-bin/express/delivery/open_msg/';

    const PATH = '/pages/goods/order_details/index';


    public function __construct(AccessToken $accessToken, $config)
    {
        parent::__construct($accessToken);
        $this->config = $config;
        $this->accessToken = $accessToken;
    }

    private function resultHandle(Collection $result)
    {
        if (empty($result)) {
            throw new AdminException('微信接口返回异常');
        }
        $res = $result->toArray();
        if ($res['errcode'] == 0) {
            return $res;
        } else {
            throw  new AdminException("微信接口异常：code = {$res['errcode']} msg = {$res['errmsg']}");
        }
    }


    /**
     * request.
     *
     * @param string $endpoint
     * @param string $method
     * @param array $options
     * @param bool $returnResponse
     */
    public function request(string $method, string $endpoint, array $options = [])
    {

        if (isset($options['json'])) {
            $body = json_encode($options['json']);
            $options['body'] = $body;
            unset($options['json']);
        }

        $headers = [
            'Content-Type' => 'text/plain',
            'User-Agent' => 'curl',
            'Accept' => 'text/plain',
        ];

        $options['headers'] = array_merge($headers, ($options['headers'] ?? []));


        return $this->_doRequestCurl($method, self::BASE_API . $endpoint, $options);
    }

    /**
     * @param $method
     * @param $location
     * @param array $options
     * @return mixed
     */
    private function _doRequestCurl($method, $location, $options = [])
    {
        //拼接$accessToken
        $location .= $this->accessToken->getToken();
        var_dump($location);
        $curl = curl_init();
        // POST数据设置
        if (strtolower($method) === 'post') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $options['data'] ?? $options['body'] ?? '');
        }
        // CURL头信息设置
        if (!empty($options['headers'])) {
            $headers = [];
            foreach ($options['headers'] as $k => $v) {
                $headers[] = "$k: $v";
            }
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($curl, CURLOPT_URL, $location);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $content = curl_exec($curl);
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        curl_close($curl);
        return json_decode(substr($content, $headerSize), true);
    }

    /**
     * 发货
     * @param $params
     * @return array
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     *
     * @date 2023/05/09
     * @author yyw
     */
    public function shipping($params)
    {
        return $this->resultHandle($this->parseJSON('POST', [self::BASE_API . self::ORDER . 'upload_shipping_info', json_encode($params, JSON_UNESCAPED_UNICODE)]));
    }

    /**
     * 合单
     * @param $params
     * @return array
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     *
     * @date 2023/05/09
     * @author yyw
     */
    public function combinedShipping($params)
    {
        return $this->resultHandle($this->parseJSON('POST', [self::BASE_API . self::ORDER . 'upload_combined_shipping_info', json_encode($params)]));
    }


    /**
     * 签收消息提醒
     * @param $params
     * @return array
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     *
     * @date 2023/05/09
     * @author yyw
     */
    public function notifyConfirm($params)
    {
        return $this->resultHandle($this->parseJSON('POST', [self::BASE_API . self::ORDER . 'notify_confirm_receive', json_encode($params)]));
    }


    /**
     * 查询小程序是否已开通发货信息管理服务
     * @return array
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     *
     * @date 2023/05/09
     * @author yyw
     */
    public function isManaged()
    {
        $params = [
            'appid' => $this->config['config']['mini_program']['app_id']
        ];
        return $this->resultHandle($this->parseJSON('POST', [self::BASE_API . self::ORDER . 'is_trade_managed', json_encode($params)]));
    }

    /**
     * 设置跳转连接
     * @param $path
     * @return array
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     *
     * @date 2023/05/10
     * @author yyw
     */
    public function setMesJumpPath($path)
    {
        $params = [
            'path' => $path
        ];
        return $this->resultHandle($this->parseJSON('POST', [self::BASE_API . self::ORDER . 'set_msg_jump_path', json_encode($params)]));
    }

    /**
     * 获取运力id列表get_delivery_list
     * @return array
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     *
     * @date 2023/05/09
     * @author yyw
     */
    public function getDeliveryList()
    {
        return $this->resultHandle($this->parseJSON('POST', [self::BASE_API . self::EXPRESS . 'get_delivery_list', "{}"]));
    }
}
