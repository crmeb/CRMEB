<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models;

use AlibabaCloud\Tea\Model;

class SendBatchCardSmsRequest extends Model
{
    /**
     * @example CARD_SMS_3245
     *
     * @var string
     */
    public $cardTemplateCode;

    /**
     * @example [{\"customurl\":\"http://www.alibaba.com\",\"dyncParams\":\"{\\\"a\\\":\\\"hello\\\",\\\"b\\\":\\\"world\\\"}\"}]
     *
     * @var string
     */
    public $cardTemplateParamJson;

    /**
     * @example DIGITAL_SMS_234080176
     *
     * @var string
     */
    public $digitalTemplateCode;

    /**
     * @example [{"a":1,"b":2},{"a":9,"b":8}]
     *
     * @var string
     */
    public $digitalTemplateParamJson;

    /**
     * @example SMS
     *
     * @var string
     */
    public $fallbackType;

    /**
     * @example 16545681783595370
     *
     * @var string
     */
    public $outId;

    /**
     * @example [\"1390000****\",\"1370000****\"]"
     *
     * @var string
     */
    public $phoneNumberJson;

    /**
     * @var string
     */
    public $signNameJson;

    /**
     * @example SMS_234251075
     *
     * @var string
     */
    public $smsTemplateCode;

    /**
     * @example [{"a":1,"b":2},{"a":9,"b":8}]
     *
     * @var string
     */
    public $smsTemplateParamJson;

    /**
     * @example [\"6\",\"6\"]
     *
     * @var string
     */
    public $smsUpExtendCodeJson;

    /**
     * @example SMS_20375****
     *
     * @var string
     */
    public $templateCode;

    /**
     * @example [{"name":"TemplateParamJson"},{"name":"TemplateParamJson"}]
     *
     * @var string
     */
    public $templateParamJson;
    protected $_name = [
        'cardTemplateCode'         => 'CardTemplateCode',
        'cardTemplateParamJson'    => 'CardTemplateParamJson',
        'digitalTemplateCode'      => 'DigitalTemplateCode',
        'digitalTemplateParamJson' => 'DigitalTemplateParamJson',
        'fallbackType'             => 'FallbackType',
        'outId'                    => 'OutId',
        'phoneNumberJson'          => 'PhoneNumberJson',
        'signNameJson'             => 'SignNameJson',
        'smsTemplateCode'          => 'SmsTemplateCode',
        'smsTemplateParamJson'     => 'SmsTemplateParamJson',
        'smsUpExtendCodeJson'      => 'SmsUpExtendCodeJson',
        'templateCode'             => 'TemplateCode',
        'templateParamJson'        => 'TemplateParamJson',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->cardTemplateCode) {
            $res['CardTemplateCode'] = $this->cardTemplateCode;
        }
        if (null !== $this->cardTemplateParamJson) {
            $res['CardTemplateParamJson'] = $this->cardTemplateParamJson;
        }
        if (null !== $this->digitalTemplateCode) {
            $res['DigitalTemplateCode'] = $this->digitalTemplateCode;
        }
        if (null !== $this->digitalTemplateParamJson) {
            $res['DigitalTemplateParamJson'] = $this->digitalTemplateParamJson;
        }
        if (null !== $this->fallbackType) {
            $res['FallbackType'] = $this->fallbackType;
        }
        if (null !== $this->outId) {
            $res['OutId'] = $this->outId;
        }
        if (null !== $this->phoneNumberJson) {
            $res['PhoneNumberJson'] = $this->phoneNumberJson;
        }
        if (null !== $this->signNameJson) {
            $res['SignNameJson'] = $this->signNameJson;
        }
        if (null !== $this->smsTemplateCode) {
            $res['SmsTemplateCode'] = $this->smsTemplateCode;
        }
        if (null !== $this->smsTemplateParamJson) {
            $res['SmsTemplateParamJson'] = $this->smsTemplateParamJson;
        }
        if (null !== $this->smsUpExtendCodeJson) {
            $res['SmsUpExtendCodeJson'] = $this->smsUpExtendCodeJson;
        }
        if (null !== $this->templateCode) {
            $res['TemplateCode'] = $this->templateCode;
        }
        if (null !== $this->templateParamJson) {
            $res['TemplateParamJson'] = $this->templateParamJson;
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return SendBatchCardSmsRequest
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['CardTemplateCode'])) {
            $model->cardTemplateCode = $map['CardTemplateCode'];
        }
        if (isset($map['CardTemplateParamJson'])) {
            $model->cardTemplateParamJson = $map['CardTemplateParamJson'];
        }
        if (isset($map['DigitalTemplateCode'])) {
            $model->digitalTemplateCode = $map['DigitalTemplateCode'];
        }
        if (isset($map['DigitalTemplateParamJson'])) {
            $model->digitalTemplateParamJson = $map['DigitalTemplateParamJson'];
        }
        if (isset($map['FallbackType'])) {
            $model->fallbackType = $map['FallbackType'];
        }
        if (isset($map['OutId'])) {
            $model->outId = $map['OutId'];
        }
        if (isset($map['PhoneNumberJson'])) {
            $model->phoneNumberJson = $map['PhoneNumberJson'];
        }
        if (isset($map['SignNameJson'])) {
            $model->signNameJson = $map['SignNameJson'];
        }
        if (isset($map['SmsTemplateCode'])) {
            $model->smsTemplateCode = $map['SmsTemplateCode'];
        }
        if (isset($map['SmsTemplateParamJson'])) {
            $model->smsTemplateParamJson = $map['SmsTemplateParamJson'];
        }
        if (isset($map['SmsUpExtendCodeJson'])) {
            $model->smsUpExtendCodeJson = $map['SmsUpExtendCodeJson'];
        }
        if (isset($map['TemplateCode'])) {
            $model->templateCode = $map['TemplateCode'];
        }
        if (isset($map['TemplateParamJson'])) {
            $model->templateParamJson = $map['TemplateParamJson'];
        }

        return $model;
    }
}
