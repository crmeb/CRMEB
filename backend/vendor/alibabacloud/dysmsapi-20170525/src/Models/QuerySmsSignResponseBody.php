<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models;

use AlibabaCloud\Tea\Model;

class QuerySmsSignResponseBody extends Model
{
    /**
     * @example OK
     *
     * @var string
     */
    public $code;

    /**
     * @example 2019-01-08 16:44:13
     *
     * @var string
     */
    public $createDate;

    /**
     * @example OK
     *
     * @var string
     */
    public $message;

    /**
     * @var string
     */
    public $reason;

    /**
     * @example CC89A90C-978F-46AC-B80D-54738371E7CA
     *
     * @var string
     */
    public $requestId;

    /**
     * @var string
     */
    public $signName;

    /**
     * @example 1
     *
     * @var int
     */
    public $signStatus;
    protected $_name = [
        'code'       => 'Code',
        'createDate' => 'CreateDate',
        'message'    => 'Message',
        'reason'     => 'Reason',
        'requestId'  => 'RequestId',
        'signName'   => 'SignName',
        'signStatus' => 'SignStatus',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->code) {
            $res['Code'] = $this->code;
        }
        if (null !== $this->createDate) {
            $res['CreateDate'] = $this->createDate;
        }
        if (null !== $this->message) {
            $res['Message'] = $this->message;
        }
        if (null !== $this->reason) {
            $res['Reason'] = $this->reason;
        }
        if (null !== $this->requestId) {
            $res['RequestId'] = $this->requestId;
        }
        if (null !== $this->signName) {
            $res['SignName'] = $this->signName;
        }
        if (null !== $this->signStatus) {
            $res['SignStatus'] = $this->signStatus;
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return QuerySmsSignResponseBody
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['Code'])) {
            $model->code = $map['Code'];
        }
        if (isset($map['CreateDate'])) {
            $model->createDate = $map['CreateDate'];
        }
        if (isset($map['Message'])) {
            $model->message = $map['Message'];
        }
        if (isset($map['Reason'])) {
            $model->reason = $map['Reason'];
        }
        if (isset($map['RequestId'])) {
            $model->requestId = $map['RequestId'];
        }
        if (isset($map['SignName'])) {
            $model->signName = $map['SignName'];
        }
        if (isset($map['SignStatus'])) {
            $model->signStatus = $map['SignStatus'];
        }

        return $model;
    }
}
