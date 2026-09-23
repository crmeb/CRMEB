<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models;

use AlibabaCloud\Tea\Model;

class SendBatchSmsRequest extends Model
{
    /**
     * @example abcdefg
     *
     * @var string
     */
    public $outId;

    /**
     * @var int
     */
    public $ownerId;

    /**
     * @example ["1590000****","1350000****"]
     *
     * @var string
     */
    public $phoneNumberJson;

    /**
     * @var string
     */
    public $resourceOwnerAccount;

    /**
     * @var int
     */
    public $resourceOwnerId;

    /**
     * @var string
     */
    public $signNameJson;

    /**
     * @example ["90999","90998"]
     *
     * @var string
     */
    public $smsUpExtendCodeJson;

    /**
     * @example SMS_15255****
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
        'outId'                => 'OutId',
        'ownerId'              => 'OwnerId',
        'phoneNumberJson'      => 'PhoneNumberJson',
        'resourceOwnerAccount' => 'ResourceOwnerAccount',
        'resourceOwnerId'      => 'ResourceOwnerId',
        'signNameJson'         => 'SignNameJson',
        'smsUpExtendCodeJson'  => 'SmsUpExtendCodeJson',
        'templateCode'         => 'TemplateCode',
        'templateParamJson'    => 'TemplateParamJson',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->outId) {
            $res['OutId'] = $this->outId;
        }
        if (null !== $this->ownerId) {
            $res['OwnerId'] = $this->ownerId;
        }
        if (null !== $this->phoneNumberJson) {
            $res['PhoneNumberJson'] = $this->phoneNumberJson;
        }
        if (null !== $this->resourceOwnerAccount) {
            $res['ResourceOwnerAccount'] = $this->resourceOwnerAccount;
        }
        if (null !== $this->resourceOwnerId) {
            $res['ResourceOwnerId'] = $this->resourceOwnerId;
        }
        if (null !== $this->signNameJson) {
            $res['SignNameJson'] = $this->signNameJson;
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
     * @return SendBatchSmsRequest
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['OutId'])) {
            $model->outId = $map['OutId'];
        }
        if (isset($map['OwnerId'])) {
            $model->ownerId = $map['OwnerId'];
        }
        if (isset($map['PhoneNumberJson'])) {
            $model->phoneNumberJson = $map['PhoneNumberJson'];
        }
        if (isset($map['ResourceOwnerAccount'])) {
            $model->resourceOwnerAccount = $map['ResourceOwnerAccount'];
        }
        if (isset($map['ResourceOwnerId'])) {
            $model->resourceOwnerId = $map['ResourceOwnerId'];
        }
        if (isset($map['SignNameJson'])) {
            $model->signNameJson = $map['SignNameJson'];
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
