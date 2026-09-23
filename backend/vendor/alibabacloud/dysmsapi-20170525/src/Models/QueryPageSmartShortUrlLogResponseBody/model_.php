<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QueryPageSmartShortUrlLogResponseBody;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QueryPageSmartShortUrlLogResponseBody\model\list_;
use AlibabaCloud\Tea\Model;

class model_ extends Model
{
    /**
     * @var list_[]
     */
    public $list;

    /**
     * @example 74
     *
     * @var int
     */
    public $pageNo;

    /**
     * @example 15
     *
     * @var int
     */
    public $pageSize;

    /**
     * @example 66
     *
     * @var int
     */
    public $totalCount;

    /**
     * @example 86
     *
     * @var int
     */
    public $totalPage;
    protected $_name = [
        'list'       => 'List',
        'pageNo'     => 'PageNo',
        'pageSize'   => 'PageSize',
        'totalCount' => 'TotalCount',
        'totalPage'  => 'TotalPage',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->list) {
            $res['List'] = [];
            if (null !== $this->list && \is_array($this->list)) {
                $n = 0;
                foreach ($this->list as $item) {
                    $res['List'][$n++] = null !== $item ? $item->toMap() : $item;
                }
            }
        }
        if (null !== $this->pageNo) {
            $res['PageNo'] = $this->pageNo;
        }
        if (null !== $this->pageSize) {
            $res['PageSize'] = $this->pageSize;
        }
        if (null !== $this->totalCount) {
            $res['TotalCount'] = $this->totalCount;
        }
        if (null !== $this->totalPage) {
            $res['TotalPage'] = $this->totalPage;
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return model_
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['List'])) {
            if (!empty($map['List'])) {
                $model->list = [];
                $n           = 0;
                foreach ($map['List'] as $item) {
                    $model->list[$n++] = null !== $item ? list_::fromMap($item) : $item;
                }
            }
        }
        if (isset($map['PageNo'])) {
            $model->pageNo = $map['PageNo'];
        }
        if (isset($map['PageSize'])) {
            $model->pageSize = $map['PageSize'];
        }
        if (isset($map['TotalCount'])) {
            $model->totalCount = $map['TotalCount'];
        }
        if (isset($map['TotalPage'])) {
            $model->totalPage = $map['TotalPage'];
        }

        return $model;
    }
}
