<?php
namespace driver\copyproduct\Yihaotong;

use crmeb\services\copyproduct\CopyProduct;
use crmeb\services\yihaotong\AccessTokenServeService;

class Driver extends CopyProduct
{
    /**
     * @var AccessTokenServeService
     */
    protected $accessToken;

    /**
     * 获取一号通API服务
     * @return AccessTokenServeService
     */
    protected function getAccessToken(): AccessTokenServeService
    {
        if (!$this->accessToken) {
            $this->accessToken = new AccessTokenServeService(
                $this->config['yihaotong_appid'] ?? '',
                $this->config['yihaotong_appsecret'] ?? ''
            );
        }
        return $this->accessToken;
    }

    /**
     * 采集商品
     * @param string $url 商品链接
     * @param array $options 额外参数
     * @return array ['status' => bool, 'msg' => string, 'data' => array]
     */
    public function copy($url, array $options = [])
    {
        try {
            $service = $this->getAccessToken();
            $data = array_merge(['url' => $url], $options);
            $resultData = $service->httpRequest('v2/copy/goods', $data);
            // 一号通返回的图片字段为 JSON 字符串，统一归一化为数组
            if (isset($resultData['description_image']) && is_string($resultData['description_image'])) {
                $resultData['description_image'] = json_decode($resultData['description_image'], true);
            }
            if (isset($resultData['slider_image']) && is_string($resultData['slider_image'])) {
                $resultData['slider_image'] = json_decode($resultData['slider_image'], true);
            }
            return ['status' => true, 'data' => $resultData];
        } catch (\Throwable $e) {
            return ['status' => false, 'msg' => $e->getMessage()];
        }
    }

    /**
     * 开通采集服务
     * @return array|mixed
     */
    public function open()
    {
        try {
            return $this->getAccessToken()->httpRequest('v2/copy/open');
        } catch (\Throwable $e) {
            return ['status' => false, 'msg' => $e->getMessage()];
        }
    }

    public function getPlatforms()
    {
        // 一号通采集接口（v2/copy/goods）通过商品链接自动识别平台，此处声明其支持采集的电商平台
        return [
            ['name' => '淘宝', 'key' => 'taobao', 'domain' => 'taobao.com', 'icon' => 'icon-taobao'],
            ['name' => '天猫', 'key' => 'tmall', 'domain' => 'tmall.com', 'icon' => 'icon-tmall'],
            ['name' => '京东', 'key' => 'jd', 'domain' => 'jd.com', 'icon' => 'icon-jd'],
            ['name' => '苏宁', 'key' => 'suning', 'domain' => 'suning.com', 'icon' => 'icon-suning'],
            ['name' => '1688', 'key' => '1688', 'domain' => '1688.com', 'icon' => 'icon-1688'],
        ];
    }

    public function getCapabilities()
    {
        return [
            'open' => true,
            'multi_platform' => false,
            'cache' => true,
        ];
    }
}