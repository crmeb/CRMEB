<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QuerySmsSignListResponseBody;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QuerySmsSignListResponseBody\smsSignList\reason;
use AlibabaCloud\Tea\Model;

class smsSignList extends Model
{
    /**
     * @example AUDIT_STATE_NOT_PASS
     *
     * @var string
     */
    public $auditStatus;

    /**
     * @var string
     */
    public $businessType;

    /**
     * @example 2020-01-08 16:44:13
     *
     * @var string
     */
    public $createDate;

    /**
     * @example 236****5
     *
     * @var string
     */
    public $orderId;

    /**
     * @var reason
     */
    public $reason;

    /**
     * @var string
     */
    public $signName;
    protected $_name = [
        'auditStatus'  => 'AuditStatus',
        'businessType' => 'BusinessType',
        'createDate'   => 'CreateDate',
        'orderId'      => 'OrderId',
        'reason'       => 'Reason',
        'signName'     => 'SignName',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->auditStatus) {
            $res['AuditStatus'] = $this->auditStatus;
        }
        if (null !== $this->businessType) {
            $res['BusinessType'] = $this->businessType;
        }
        if (null !== $this->createDate) {
            $res['CreateDate'] = $this->createDate;
        }
        if (null !== $this->orderId) {
            $res['OrderId'] = $this->orderId;
        }
        if (null !== $this->reason) {
            $res['Reason'] = null !== $this->reason ? $this->reason->toMap() : null;
        }
        if (null !== $this->signName) {
            $res['SignName'] = $this->signName;
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return smsSignList
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['AuditStatus'])) {
            $model->auditStatus = $map['AuditStatus'];
        }
        if (isset($map['BusinessType'])) {
            $model->businessType = $map['BusinessType'];
        }
        if (isset($map['CreateDate'])) {
            $model->createDate = $map['CreateDate'];
        }
        if (isset($map['OrderId'])) {
            $model->orderId = $map['OrderId'];
        }
        if (isset($map['Reason'])) {
            $model->reason = reason::fromMap($map['Reason']);
        }
        if (isset($map['SignName'])) {
            $model->signName = $map['SignName'];
        }

        return $model;
    }
}
