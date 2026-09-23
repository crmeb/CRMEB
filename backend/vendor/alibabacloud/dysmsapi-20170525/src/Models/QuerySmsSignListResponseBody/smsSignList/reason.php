<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QuerySmsSignListResponseBody\smsSignList;

use AlibabaCloud\Tea\Model;

class reason extends Model
{
    /**
     * @example 2020-01-08 19:02:13
     *
     * @var string
     */
    public $rejectDate;

    /**
     * @var string
     */
    public $rejectInfo;

    /**
     * @var string
     */
    public $rejectSubInfo;
    protected $_name = [
        'rejectDate'    => 'RejectDate',
        'rejectInfo'    => 'RejectInfo',
        'rejectSubInfo' => 'RejectSubInfo',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->rejectDate) {
            $res['RejectDate'] = $this->rejectDate;
        }
        if (null !== $this->rejectInfo) {
            $res['RejectInfo'] = $this->rejectInfo;
        }
        if (null !== $this->rejectSubInfo) {
            $res['RejectSubInfo'] = $this->rejectSubInfo;
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return reason
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['RejectDate'])) {
            $model->rejectDate = $map['RejectDate'];
        }
        if (isset($map['RejectInfo'])) {
            $model->rejectInfo = $map['RejectInfo'];
        }
        if (isset($map['RejectSubInfo'])) {
            $model->rejectSubInfo = $map['RejectSubInfo'];
        }

        return $model;
    }
}
