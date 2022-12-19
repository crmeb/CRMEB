<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendCardSmsRequest\cardObjects;
use AlibabaCloud\Tea\Model;

class SendCardSmsRequest extends Model
{
    /**
     * @var cardObjects[]
     */
    public $cardObjects;

    /**
     * @var string
     */
    public $cardTemplateCode;

    /**
     * @var string
     */
    public $digitalTemplateCode;

    /**
     * @var string
     */
    public $digitalTemplateParam;

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
    public $signName;

    /**
     * @var string
     */
    public $smsTemplateCode;

    /**
     * @var string
     */
    public $smsTemplateParam;

    /**
     * @var string
     */
    public $smsUpExtendCode;
    protected $_name = [
        'cardObjects'          => 'CardObjects',
        'cardTemplateCode'     => 'CardTemplateCode',
        'digitalTemplateCode'  => 'DigitalTemplateCode',
        'digitalTemplateParam' => 'DigitalTemplateParam',
        'fallbackType'         => 'FallbackType',
        'outId'                => 'OutId',
        'signName'             => 'SignName',
        'smsTemplateCode'      => 'SmsTemplateCode',
        'smsTemplateParam'     => 'SmsTemplateParam',
        'smsUpExtendCode'      => 'SmsUpExtendCode',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->cardObjects) {
            $res['CardObjects'] = [];
            if (null !== $this->cardObjects && \is_array($this->cardObjects)) {
                $n = 0;
                foreach ($this->cardObjects as $item) {
                    $res['CardObjects'][$n++] = null !== $item ? $item->toMap() : $item;
                }
            }
        }
        if (null !== $this->cardTemplateCode) {
            $res['CardTemplateCode'] = $this->cardTemplateCode;
        }
        if (null !== $this->digitalTemplateCode) {
            $res['DigitalTemplateCode'] = $this->digitalTemplateCode;
        }
        if (null !== $this->digitalTemplateParam) {
            $res['DigitalTemplateParam'] = $this->digitalTemplateParam;
        }
        if (null !== $this->fallbackType) {
            $res['FallbackType'] = $this->fallbackType;
        }
        if (null !== $this->outId) {
            $res['OutId'] = $this->outId;
        }
        if (null !== $this->signName) {
            $res['SignName'] = $this->signName;
        }
        if (null !== $this->smsTemplateCode) {
            $res['SmsTemplateCode'] = $this->smsTemplateCode;
        }
        if (null !== $this->smsTemplateParam) {
            $res['SmsTemplateParam'] = $this->smsTemplateParam;
        }
        if (null !== $this->smsUpExtendCode) {
            $res['SmsUpExtendCode'] = $this->smsUpExtendCode;
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return SendCardSmsRequest
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['CardObjects'])) {
            if (!empty($map['CardObjects'])) {
                $model->cardObjects = [];
                $n                  = 0;
                foreach ($map['CardObjects'] as $item) {
                    $model->cardObjects[$n++] = null !== $item ? cardObjects::fromMap($item) : $item;
                }
            }
        }
        if (isset($map['CardTemplateCode'])) {
            $model->cardTemplateCode = $map['CardTemplateCode'];
        }
        if (isset($map['DigitalTemplateCode'])) {
            $model->digitalTemplateCode = $map['DigitalTemplateCode'];
        }
        if (isset($map['DigitalTemplateParam'])) {
            $model->digitalTemplateParam = $map['DigitalTemplateParam'];
        }
        if (isset($map['FallbackType'])) {
            $model->fallbackType = $map['FallbackType'];
        }
        if (isset($map['OutId'])) {
            $model->outId = $map['OutId'];
        }
        if (isset($map['SignName'])) {
            $model->signName = $map['SignName'];
        }
        if (isset($map['SmsTemplateCode'])) {
            $model->smsTemplateCode = $map['SmsTemplateCode'];
        }
        if (isset($map['SmsTemplateParam'])) {
            $model->smsTemplateParam = $map['SmsTemplateParam'];
        }
        if (isset($map['SmsUpExtendCode'])) {
            $model->smsUpExtendCode = $map['SmsUpExtendCode'];
        }

        return $model;
    }
}
