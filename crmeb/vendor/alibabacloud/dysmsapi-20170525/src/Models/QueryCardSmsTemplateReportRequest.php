<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models;

use AlibabaCloud\Tea\Model;

class QueryCardSmsTemplateReportRequest extends Model
{
    /**
     * @var string
     */
    public $endDate;

    /**
     * @var string
     */
    public $startDate;

    /**
     * @var string[]
     */
    public $templateCodes;
    protected $_name = [
        'endDate'       => 'EndDate',
        'startDate'     => 'StartDate',
        'templateCodes' => 'TemplateCodes',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->endDate) {
            $res['EndDate'] = $this->endDate;
        }
        if (null !== $this->startDate) {
            $res['StartDate'] = $this->startDate;
        }
        if (null !== $this->templateCodes) {
            $res['TemplateCodes'] = $this->templateCodes;
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return QueryCardSmsTemplateReportRequest
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['EndDate'])) {
            $model->endDate = $map['EndDate'];
        }
        if (isset($map['StartDate'])) {
            $model->startDate = $map['StartDate'];
        }
        if (isset($map['TemplateCodes'])) {
            if (!empty($map['TemplateCodes'])) {
                $model->templateCodes = $map['TemplateCodes'];
            }
        }

        return $model;
    }
}
