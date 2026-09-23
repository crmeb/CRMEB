<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QuerySmsTemplateListResponseBody;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QuerySmsTemplateListResponseBody\smsTemplateList\reason;
use AlibabaCloud\Tea\Model;

class smsTemplateList extends Model
{
    /**
     * @example AUDIT_STATE_PASS
     *
     * @var string
     */
    public $auditStatus;

    /**
     * @example 2020-06-04 11:42:17
     *
     * @var string
     */
    public $createDate;

    /**
     * @example 2361****
     *
     * @var string
     */
    public $orderId;

    /**
     * @example 0
     *
     * @var int
     */
    public $outerTemplateType;

    /**
     * @var reason
     */
    public $reason;

    /**
     * @example SMS_1525***
     *
     * @var string
     */
    public $templateCode;

    /**
     * @example 123456789
     *
     * @var string
     */
    public $templateContent;

    /**
     * @var string
     */
    public $templateName;

    /**
     * @example 7
     *
     * @var int
     */
    public $templateType;
    protected $_name = [
        'auditStatus'       => 'AuditStatus',
        'createDate'        => 'CreateDate',
        'orderId'           => 'OrderId',
        'outerTemplateType' => 'OuterTemplateType',
        'reason'            => 'Reason',
        'templateCode'      => 'TemplateCode',
        'templateContent'   => 'TemplateContent',
        'templateName'      => 'TemplateName',
        'templateType'      => 'TemplateType',
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
        if (null !== $this->createDate) {
            $res['CreateDate'] = $this->createDate;
        }
        if (null !== $this->orderId) {
            $res['OrderId'] = $this->orderId;
        }
        if (null !== $this->outerTemplateType) {
            $res['OuterTemplateType'] = $this->outerTemplateType;
        }
        if (null !== $this->reason) {
            $res['Reason'] = null !== $this->reason ? $this->reason->toMap() : null;
        }
        if (null !== $this->templateCode) {
            $res['TemplateCode'] = $this->templateCode;
        }
        if (null !== $this->templateContent) {
            $res['TemplateContent'] = $this->templateContent;
        }
        if (null !== $this->templateName) {
            $res['TemplateName'] = $this->templateName;
        }
        if (null !== $this->templateType) {
            $res['TemplateType'] = $this->templateType;
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return smsTemplateList
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['AuditStatus'])) {
            $model->auditStatus = $map['AuditStatus'];
        }
        if (isset($map['CreateDate'])) {
            $model->createDate = $map['CreateDate'];
        }
        if (isset($map['OrderId'])) {
            $model->orderId = $map['OrderId'];
        }
        if (isset($map['OuterTemplateType'])) {
            $model->outerTemplateType = $map['OuterTemplateType'];
        }
        if (isset($map['Reason'])) {
            $model->reason = reason::fromMap($map['Reason']);
        }
        if (isset($map['TemplateCode'])) {
            $model->templateCode = $map['TemplateCode'];
        }
        if (isset($map['TemplateContent'])) {
            $model->templateContent = $map['TemplateContent'];
        }
        if (isset($map['TemplateName'])) {
            $model->templateName = $map['TemplateName'];
        }
        if (isset($map['TemplateType'])) {
            $model->templateType = $map['TemplateType'];
        }

        return $model;
    }
}
