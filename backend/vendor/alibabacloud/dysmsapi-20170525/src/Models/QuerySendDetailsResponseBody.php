<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QuerySendDetailsResponseBody\smsSendDetailDTOs;
use AlibabaCloud\Tea\Model;

class QuerySendDetailsResponseBody extends Model
{
    /**
     * @example OK
     *
     * @var string
     */
    public $code;

    /**
     * @example OK
     *
     * @var string
     */
    public $message;

    /**
     * @example 819BE656-D2E0-4858-8B21-B2E477085AAF
     *
     * @var string
     */
    public $requestId;

    /**
     * @var smsSendDetailDTOs
     */
    public $smsSendDetailDTOs;

    /**
     * @example 1
     *
     * @var string
     */
    public $totalCount;
    protected $_name = [
        'code'              => 'Code',
        'message'           => 'Message',
        'requestId'         => 'RequestId',
        'smsSendDetailDTOs' => 'SmsSendDetailDTOs',
        'totalCount'        => 'TotalCount',
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
        if (null !== $this->message) {
            $res['Message'] = $this->message;
        }
        if (null !== $this->requestId) {
            $res['RequestId'] = $this->requestId;
        }
        if (null !== $this->smsSendDetailDTOs) {
            $res['SmsSendDetailDTOs'] = null !== $this->smsSendDetailDTOs ? $this->smsSendDetailDTOs->toMap() : null;
        }
        if (null !== $this->totalCount) {
            $res['TotalCount'] = $this->totalCount;
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return QuerySendDetailsResponseBody
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['Code'])) {
            $model->code = $map['Code'];
        }
        if (isset($map['Message'])) {
            $model->message = $map['Message'];
        }
        if (isset($map['RequestId'])) {
            $model->requestId = $map['RequestId'];
        }
        if (isset($map['SmsSendDetailDTOs'])) {
            $model->smsSendDetailDTOs = smsSendDetailDTOs::fromMap($map['SmsSendDetailDTOs']);
        }
        if (isset($map['TotalCount'])) {
            $model->totalCount = $map['TotalCount'];
        }

        return $model;
    }
}
