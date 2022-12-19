<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\services\sms\storage;

use crmeb\services\sms\BaseSms;
use crmeb\exceptions\AdminException;
use think\facade\Config;


/**
 * Class yihaotong
 * @package crmeb\services\sms\storage
 */
class Yihaotong extends BaseSms
{

    /**
     * 开通
     */
    const SMS_OPEN = 'sms_v2/open';

    /**
     * 修改签名
     */
    const SMS_MODIFY = 'sms_v2/modify';

    /**
     * 用户信息
     */
    const SMS_INFO = 'sms_v2/info';

    /**
     * 发送短信
     */
    const SMS_SEND = 'sms_v2/send';

    /**
     * 短信模板
     */
    const SMS_TEMPS = 'sms_v2/temps';

    /**
     * 申请模板
     */
    const SMS_APPLY = 'sms_v2/apply';

    /**
     * 模板记录
     */
    const SMS_APPLYS = 'sms_v2/applys';

    /**
     * 发送记录
     */
    const SMS_RECORD = 'sms_v2/record';

    /**
     * 获取短信发送状态
     */
    const SMS_STSTUS = 'sms/status';

    /**
     * 短信签名
     * @var string
     */
    protected $sign = '';

    /** 初始化
     * @param array $config
     */
    protected function initialize(array $config = [])
    {
        parent::initialize($config);
    }


    /**
     * 设置签名
     * @param $sign
     * @return $this
     */
    public function setSign($sign)
    {
        $this->sign = $sign;
        return $this;
    }

    /**
     * 获取验证码
     * @param string $phone
     * @return array|mixed
     */
    public function captcha(string $phone)
    {
        $params = [
            'phone' => $phone
        ];
        return $this->accessToken->httpRequest('sms/captcha', $params, 'GET', false);
    }

    /**
     * 开通服务
     * @return array|bool|mixed
     */
    public function open()
    {
        $param = [
            'sign' => $this->sign
        ];
        return $this->accessToken->httpRequest(self::SMS_OPEN, $param);
    }

    /**
     * 修改签名
     * @param string $sign
     * @return array|bool|mixed
     */
    public function modify(string $sign = null, string $phone, string $code)
    {
        $param = [
            'sign' => $sign ?: $this->sign,
            'verify_code' => $code,
            'phone' => $phone
        ];
        return $this->accessToken->httpRequest(self::SMS_MODIFY, $param);
    }

    /**
     * 获取用户信息
     * @return array|bool|mixed
     */
    public function info()
    {
        return $this->accessToken->httpRequest(self::SMS_INFO, []);
    }

    /**
     * 获取短信模板
     * @param int $page
     * @param int $limit
     * @param int $type
     * @return array|mixed
     */
    public function temps(int $page = 0, int $limit = 10, int $type = 1)
    {
        $param = [
            'page' => $page,
            'limit' => $limit,
            'temp_type' => $type
        ];
        return $this->accessToken->httpRequest(self::SMS_TEMPS, $param);
    }

    /**
     * 申请模版
     * @param $title
     * @param $content
     * @param $type
     * @return array|bool|mixed
     */
    public function apply(string $title, string $content, int $type)
    {
        $param = [
            'title' => $title,
            'content' => $content,
            'type' => $type
        ];
        return $this->accessToken->httpRequest(self::SMS_APPLY, $param);
    }

    /**
     * 申请记录
     * @param $temp_type
     * @param int $page
     * @param int $limit
     * @return array|bool|mixed
     */
    public function applys(int $tempType, int $page, int $limit)
    {
        $param = [
            'temp_type' => $tempType,
            'page' => $page,
            'limit' => $limit
        ];
        return $this->accessToken->httpRequest(self::SMS_APPLYS, $param);
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
            throw new AdminException(400719);
        }
        $param = [
            'phone' => $phone,
            'host' =>  request()->host()
        ];
        $param['temp_id'] = $templateId;
        if (is_null($param['temp_id'])) {
            throw new AdminException(400720);
        }
        $param['param'] = json_encode($data);
        return $this->accessToken->httpRequest(self::SMS_SEND, $param);
    }

    /**
     * 发送记录
     * @param $record_id
     * @return array|bool|mixed
     */
    public function record($record_id)
    {
        $param = [
            'record_id' => $record_id
        ];
        return $this->accessToken->httpRequest(self::SMS_RECORD, $param);
    }

    /**
     * 获取发送状态
     * @param array $recordIds
     * @return array|mixed
     */
    public function getStatus(array $recordIds)
    {
        $data['record_id'] = json_encode($recordIds);
        return $this->accessToken->httpRequest(self::SMS_STSTUS, $data, 'POST', false);
    }
}
