<?php

namespace crmeb\services\sms\storage;

use think\facade\Config;
use crmeb\basic\BaseSms;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

/**
 * 阿里云短信服务
 * Class Aliyun
 * @package crmeb\services
 */
class AliyunSms extends BaseSms
{

    protected $SignName = '';
    protected function initialize(array $config = [])
    {
        parent::initialize($config);
        $conf = Config::get('sms.stores.aliyun', []);

        $this->SignName = $config['sign_name'] ?? $conf['sign_name'] ?? '';
        $AccessKeyId = $config['access_key_id'] ?? $conf['access_key_id'] ?? '';
        $AccessKeySecret = $config['access_key_secret'] ?? $conf['access_key_secret'] ?? '';
        $RegionId = $config['region_id'] ?? $conf['region_id'] ?? '';
        if(!$AccessKeyId || !$AccessKeySecret || !$RegionId || !$this->SignName){
            return $this->setError('请先配置短信秘钥');
        }
        AlibabaCloud::accessKeyClient($AccessKeyId, $AccessKeySecret)
            ->regionId($RegionId)
            ->asDefaultClient();
    }

    public function send(string $phone, string $templateId, array $data = [])
    {
        if (empty($phone)) {
            return $this->setError('Mobile number cannot be empty');
        }
        try {
            $TemplateCode = $this->getTemplateCode($templateId);
            $TemplateParam = json_encode($data);
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId' => $this->SignName,
                        'TemplateCode' => $TemplateCode,
                        'TemplateParam' => $TemplateParam
                    ],
                ])
                ->request();
            $result = $result->toArray();
        } catch (ClientException $e) {
            return $this->setError($e->getErrorMessage());
        } catch (ServerException $e) {
            return $this->setError($e->getErrorMessage());
        }
        return [
            'data' => [
                'id' => $result['RequestId'],
                'content' => $TemplateParam,
                'template' => $TemplateCode,
            ]
        ];
    }
}
