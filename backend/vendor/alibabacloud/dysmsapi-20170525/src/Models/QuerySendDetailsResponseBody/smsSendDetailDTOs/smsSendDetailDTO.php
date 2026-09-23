<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QuerySendDetailsResponseBody\smsSendDetailDTOs;

use AlibabaCloud\Tea\Model;

class smsSendDetailDTO extends Model
{
    /**
     * @var string
     */
    public $content;

    /**
     * @example DELIVERED
     *
     * @var string
     */
    public $errCode;

    /**
     * @example 123
     *
     * @var string
     */
    public $outId;

    /**
     * @example 1390000****
     *
     * @var string
     */
    public $phoneNum;

    /**
     * @example 2019-01-08 16:44:13
     *
     * @var string
     */
    public $receiveDate;

    /**
     * @example 2019-01-08 16:44:10
     *
     * @var string
     */
    public $sendDate;

    /**
     * @example 3
     *
     * @var int
     */
    public $sendStatus;

    /**
     * @example SMS_12231****
     *
     * @var string
     */
    public $templateCode;
    protected $_name = [
        'content'      => 'Content',
        'errCode'      => 'ErrCode',
        'outId'        => 'OutId',
        'phoneNum'     => 'PhoneNum',
        'receiveDate'  => 'ReceiveDate',
        'sendDate'     => 'SendDate',
        'sendStatus'   => 'SendStatus',
        'templateCode' => 'TemplateCode',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->content) {
            $res['Content'] = $this->content;
        }
        if (null !== $this->errCode) {
            $res['ErrCode'] = $this->errCode;
        }
        if (null !== $this->outId) {
            $res['OutId'] = $this->outId;
        }
        if (null !== $this->phoneNum) {
            $res['PhoneNum'] = $this->phoneNum;
        }
        if (null !== $this->receiveDate) {
            $res['ReceiveDate'] = $this->receiveDate;
        }
        if (null !== $this->sendDate) {
            $res['SendDate'] = $this->sendDate;
        }
        if (null !== $this->sendStatus) {
            $res['SendStatus'] = $this->sendStatus;
        }
        if (null !== $this->templateCode) {
            $res['TemplateCode'] = $this->templateCode;
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return smsSendDetailDTO
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['Content'])) {
            $model->content = $map['Content'];
        }
        if (isset($map['ErrCode'])) {
            $model->errCode = $map['ErrCode'];
        }
        if (isset($map['OutId'])) {
            $model->outId = $map['OutId'];
        }
        if (isset($map['PhoneNum'])) {
            $model->phoneNum = $map['PhoneNum'];
        }
        if (isset($map['ReceiveDate'])) {
            $model->receiveDate = $map['ReceiveDate'];
        }
        if (isset($map['SendDate'])) {
            $model->sendDate = $map['SendDate'];
        }
        if (isset($map['SendStatus'])) {
            $model->sendStatus = $map['SendStatus'];
        }
        if (isset($map['TemplateCode'])) {
            $model->templateCode = $map['TemplateCode'];
        }

        return $model;
    }
}
