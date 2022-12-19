<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QuerySmsTemplateListResponseBody\smsTemplateList;
use AlibabaCloud\Tea\Model;

class QuerySmsTemplateListResponseBody extends Model
{
    /**
     * @var string
     */
    public $code;

    /**
     * @var int
     */
    public $currentPage;

    /**
     * @var string
     */
    public $message;

    /**
     * @var int
     */
    public $pageSize;

    /**
     * @var string
     */
    public $requestId;

    /**
     * @var smsTemplateList[]
     */
    public $smsTemplateList;

    /**
     * @var int
     */
    public $totalCount;
    protected $_name = [
        'code'            => 'Code',
        'currentPage'     => 'CurrentPage',
        'message'         => 'Message',
        'pageSize'        => 'PageSize',
        'requestId'       => 'RequestId',
        'smsTemplateList' => 'SmsTemplateList',
        'totalCount'      => 'TotalCount',
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
        if (null !== $this->currentPage) {
            $res['CurrentPage'] = $this->currentPage;
        }
        if (null !== $this->message) {
            $res['Message'] = $this->message;
        }
        if (null !== $this->pageSize) {
            $res['PageSize'] = $this->pageSize;
        }
        if (null !== $this->requestId) {
            $res['RequestId'] = $this->requestId;
        }
        if (null !== $this->smsTemplateList) {
            $res['SmsTemplateList'] = [];
            if (null !== $this->smsTemplateList && \is_array($this->smsTemplateList)) {
                $n = 0;
                foreach ($this->smsTemplateList as $item) {
                    $res['SmsTemplateList'][$n++] = null !== $item ? $item->toMap() : $item;
                }
            }
        }
        if (null !== $this->totalCount) {
            $res['TotalCount'] = $this->totalCount;
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return QuerySmsTemplateListResponseBody
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['Code'])) {
            $model->code = $map['Code'];
        }
        if (isset($map['CurrentPage'])) {
            $model->currentPage = $map['CurrentPage'];
        }
        if (isset($map['Message'])) {
            $model->message = $map['Message'];
        }
        if (isset($map['PageSize'])) {
            $model->pageSize = $map['PageSize'];
        }
        if (isset($map['RequestId'])) {
            $model->requestId = $map['RequestId'];
        }
        if (isset($map['SmsTemplateList'])) {
            if (!empty($map['SmsTemplateList'])) {
                $model->smsTemplateList = [];
                $n                      = 0;
                foreach ($map['SmsTemplateList'] as $item) {
                    $model->smsTemplateList[$n++] = null !== $item ? smsTemplateList::fromMap($item) : $item;
                }
            }
        }
        if (isset($map['TotalCount'])) {
            $model->totalCount = $map['TotalCount'];
        }

        return $model;
    }
}
