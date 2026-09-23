<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models;

use AlibabaCloud\Tea\Model;

class GetCardSmsLinkRequest extends Model
{
    /**
     * @example 2
     *
     * @var int
     */
    public $cardCodeType;

    /**
     * @example 1
     *
     * @var int
     */
    public $cardLinkType;

    /**
     * @example CARD_SMS_****
     *
     * @var string
     */
    public $cardTemplateCode;

    /**
     * @var string
     */
    public $cardTemplateParamJson;

    /**
     * @example abCde
     *
     * @var string
     */
    public $customShortCodeJson;

    /**
     * @var string
     */
    public $domain;

    /**
     * @example BC20220608102511660860762****
     *
     * @var string
     */
    public $outId;

    /**
     * @example [\"1390000****
     * \",\"1370000****
     * \"]
     * @var string
     */
    public $phoneNumberJson;

    /**
     * @var string
     */
    public $signNameJson;
    protected $_name = [
        'cardCodeType'          => 'CardCodeType',
        'cardLinkType'          => 'CardLinkType',
        'cardTemplateCode'      => 'CardTemplateCode',
        'cardTemplateParamJson' => 'CardTemplateParamJson',
        'customShortCodeJson'   => 'CustomShortCodeJson',
        'domain'                => 'Domain',
        'outId'                 => 'OutId',
        'phoneNumberJson'       => 'PhoneNumberJson',
        'signNameJson'          => 'SignNameJson',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->cardCodeType) {
            $res['CardCodeType'] = $this->cardCodeType;
        }
        if (null !== $this->cardLinkType) {
            $res['CardLinkType'] = $this->cardLinkType;
        }
        if (null !== $this->cardTemplateCode) {
            $res['CardTemplateCode'] = $this->cardTemplateCode;
        }
        if (null !== $this->cardTemplateParamJson) {
            $res['CardTemplateParamJson'] = $this->cardTemplateParamJson;
        }
        if (null !== $this->customShortCodeJson) {
            $res['CustomShortCodeJson'] = $this->customShortCodeJson;
        }
        if (null !== $this->domain) {
            $res['Domain'] = $this->domain;
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

        return $res;
    }

    /**
     * @param array $map
     *
     * @return GetCardSmsLinkRequest
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['CardCodeType'])) {
            $model->cardCodeType = $map['CardCodeType'];
        }
        if (isset($map['CardLinkType'])) {
            $model->cardLinkType = $map['CardLinkType'];
        }
        if (isset($map['CardTemplateCode'])) {
            $model->cardTemplateCode = $map['CardTemplateCode'];
        }
        if (isset($map['CardTemplateParamJson'])) {
            $model->cardTemplateParamJson = $map['CardTemplateParamJson'];
        }
        if (isset($map['CustomShortCodeJson'])) {
            $model->customShortCodeJson = $map['CustomShortCodeJson'];
        }
        if (isset($map['Domain'])) {
            $model->domain = $map['Domain'];
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

        return $model;
    }
}
