<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models;

use AlibabaCloud\Tea\Model;

class SendBatchCardSmsRequest extends Model
{
    /**
     * @var string
     */
    public $cardTemplateCode;

    /**
     * @var string
     */
    public $cardTemplateParamJson;

    /**
     * @var string
     */
    public $digitalTemplateCode;

    /**
     * @var string
     */
    public $digitalTemplateParamJson;

    /**
     * @var string
     */
    public $fallbackType;

    /**
     * @var string
     */
    public $outId;

    /**
     * @var string
     */
    public $phoneNumberJson;

    /**
     * @var string
     */
    public $signNameJson;

    /**
     * @var string
     */
    public $smsTemplateCode;

    /**
     * @var string
     */
    public $smsTemplateParamJson;

    /**
     * @var string
     */
    public $smsUpExtendCodeJson;
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

        return $model;
    }
}
