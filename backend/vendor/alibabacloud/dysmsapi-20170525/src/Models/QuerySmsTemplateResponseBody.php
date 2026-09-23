<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models;

use AlibabaCloud\Tea\Model;

class QuerySmsTemplateResponseBody extends Model
{
    /**
     * @example OK
     *
     * @var string
     */
    public $code;

    /**
     * @example 2019-06-04 11:42:17
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
     * @example 0A974B78-02BF-4C79-ADF3-90CFBA1B55B1
     *
     * @var string
     */
    public $requestId;

    /**
     * @example SMS_16703****
     *
     * @var string
     */
    public $templateCode;

    /**
     * @var string
     */
    public $templateContent;

    /**
     * @var string
     */
    public $templateName;

    /**
     * @example 1
     *
     * @var int
     */
    public $templateStatus;

    /**
     * @example 1
     *
     * @var int
     */
    public $templateType;
    protected $_name = [
        'code'            => 'Code',
        'createDate'      => 'CreateDate',
        'message'         => 'Message',
        'reason'          => 'Reason',
        'requestId'       => 'RequestId',
        'templateCode'    => 'TemplateCode',
        'templateContent' => 'TemplateContent',
        'templateName'    => 'TemplateName',
        'templateStatus'  => 'TemplateStatus',
        'templateType'    => 'TemplateType',
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
        if (null !== $this->templateCode) {
            $res['TemplateCode'] = $this->templateCode;
        }
        if (null !== $this->templateContent) {
            $res['TemplateContent'] = $this->templateContent;
        }
        if (null !== $this->templateName) {
            $res['TemplateName'] = $this->templateName;
        }
        if (null !== $this->templateStatus) {
            $res['TemplateStatus'] = $this->templateStatus;
        }
        if (null !== $this->templateType) {
            $res['TemplateType'] = $this->templateType;
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return QuerySmsTemplateResponseBody
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
        if (isset($map['TemplateCode'])) {
            $model->templateCode = $map['TemplateCode'];
        }
        if (isset($map['TemplateContent'])) {
            $model->templateContent = $map['TemplateContent'];
        }
        if (isset($map['TemplateName'])) {
            $model->templateName = $map['TemplateName'];
        }
        if (isset($map['TemplateStatus'])) {
            $model->templateStatus = $map['TemplateStatus'];
        }
        if (isset($map['TemplateType'])) {
            $model->templateType = $map['TemplateType'];
        }

        return $model;
    }
}
