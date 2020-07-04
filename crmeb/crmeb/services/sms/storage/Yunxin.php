<?php

namespace crmeb\services\sms\storage;

use crmeb\basic\BaseSms;
use crmeb\services\HttpService;


/**
 * 云信短信服务
 * Class SMSService
 * @package crmeb\services
 */
class Yunxin extends BaseSms
{

    /**
     * 短信账号
     * @var string
     */
    protected $smsAccount;

    /**
     * 短信token
     * @var string
     */
    protected $smsToken;

    /**
     * 是否登陆
     * @var bool
     */
    protected $status;

    /**
     * 短信请求地址
     * @var string
     */
    protected $smsUrl = 'https://sms.crmeb.net/api/';

    /**
     * 短信支付回调地址
     * @var string
     */
    protected $payNotify;

    /**
     * 初始化
     * @param array $config
     * @return mixed|void
     */
    protected function initialize(array $config)
    {
        parent::initialize($config);
        $this->smsAccount = $config['sms_account'] ?? null;
        $this->smsToken = $config['sms_token'] ?? null;
        $this->payNotify = ($config['site_url'] ?? '') . '/api/sms/pay/notify';
        if ($this->smsAccount && $this->smsToken) {
            $this->status = true;
            $this->smsToken = md5($this->smsAccount . md5($this->smsToken));
        } else {
            $this->status = false;
        }
    }

    /**
     * 登陆状态
     * @return bool
     */
    public function isLogin()
    {
        return $this->status;
    }

    /**
     * 验证码接口
     * @return string
     */
    public function getSmsUrl()
    {
        return $this->smsUrl . 'sms/captcha';
    }


    /**
     * 短信注册
     * @param $account
     * @param $password
     * @param $url
     * @param $phone
     * @param $code
     * @param $sign
     * @return mixed
     */
    public function register($account, $password, $url, $phone, $code, $sign)
    {
        $data['account'] = $account;
        $data['password'] = $password;
        $data['url'] = $url;
        $data['phone'] = $phone;
        $data['sign'] = $sign;
        $data['code'] = $code;
        return json_decode(HttpService::postRequest($this->smsUrl . 'sms/register', $data), true);
    }

    /**
     * 公共短信模板列表
     * @param array $data
     * @return mixed
     */
    public function publictemp(array $data)
    {
        $data['account'] = $this->smsAccount;
        $data['token'] = $this->smsToken;
        return json_decode(HttpService::postRequest($this->smsUrl . 'sms/publictemp', $data), true);
    }

    /**
     * 公共短信模板添加
     * @param $id
     * @param $tempId
     * @return mixed
     */
    public function use($id, $tempId)
    {
        $data['account'] = $this->smsAccount;
        $data['token'] = $this->smsToken;
        $data['id'] = $id;
        $data['tempId'] = $tempId;
        return json_decode(HttpService::postRequest($this->smsUrl . 'sms/use', $data), true);
    }

    /**
     * 发送短信
     * @param $phone
     * @param $template
     * @param $param
     * @return bool|string
     */
    public function send(string $phone, string $templateId, array $data = [])
    {
        if (!$phone) {
            return $this->setError('Mobile number cannot be empty');
        }
        if (!$this->smsAccount) {
            return $this->setError('Account does not exist');
        }
        if (!$this->smsToken) {
            return $this->setError('Access token does not exist');
        }
        $formData['uid'] = $this->smsAccount;
        $formData['token'] = $this->smsToken;
        $formData['mobile'] = $phone;
        $formData['template'] = $this->getTemplateCode($templateId);
        if (is_null($formData['template'])) {
            return $this->setError('Missing template number');
        }
        $formData['param'] = json_encode($data);
        $resource = json_decode(HttpService::postRequest($this->smsUrl . 'sms/send', $formData), true);
        if ($resource['status'] === 400) {
            return $this->setError($resource['msg']);
        }
        return $resource;
    }

    /**
     * 账号信息
     * @return mixed
     */
    public function count()
    {
        return json_decode(HttpService::postRequest($this->smsUrl . 'sms/userinfo', [
            'account' => $this->smsAccount,
            'token' => $this->smsToken
        ]), true);
    }

    /**
     * 支付套餐
     * @param $page
     * @param $limit
     * @return mixed
     */
    public function meal($page, $limit)
    {
        return json_decode(HttpService::getRequest($this->smsUrl . 'sms/meal', [
            'page' => $page,
            'limit' => $limit
        ]), true);
    }

    /**
     * 支付码
     * @param $payType
     * @param $mealId
     * @param $price
     * @param $attach
     * @return mixed
     */
    public function pay($payType, $mealId, $price, $attach)
    {
        $data['uid'] = $this->smsAccount;
        $data['token'] = $this->smsToken;
        $data['payType'] = $payType;
        $data['mealId'] = $mealId;
        $data['notify'] = $this->payNotify;
        $data['price'] = $price;
        $data['attach'] = $attach;
        return json_decode(HttpService::postRequest($this->smsUrl . 'sms/mealpay', $data), true);
    }

    /**
     * 申请模板消息
     * @param $title
     * @param $content
     * @param $type
     * @return mixed
     */
    public function apply($title, $content, $type)
    {
        $data['account'] = $this->smsAccount;
        $data['token'] = $this->smsToken;
        $data['title'] = $title;
        $data['content'] = $content;
        $data['type'] = $type;
        return json_decode(HttpService::postRequest($this->smsUrl . 'sms/apply', $data), true);
    }

    /**
     * 短信模板列表
     * @param $data
     * @return mixed
     */
    public function template($data)
    {
        return json_decode(HttpService::postRequest($this->smsUrl . 'sms/template', $data + [
                'account' => $this->smsAccount, 'token' => $this->smsToken
            ]), true);
    }

    /**
     * 获取短息记录状态
     * @param $record_id
     * @return mixed
     */
    public function getStatus(array $record_id)
    {
        $data['record_id'] = json_encode($record_id);
        return json_decode(HttpService::postRequest($this->smsUrl . 'sms/status', $data), true);
    }
}