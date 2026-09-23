<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QueryMobilesCardSupportResponseBody;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QueryMobilesCardSupportResponseBody\data\queryResult;
use AlibabaCloud\Tea\Model;

class data extends Model
{
    /**
     * @var queryResult[]
     */
    public $queryResult;
    protected $_name = [
        'queryResult' => 'QueryResult',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->queryResult) {
            $res['QueryResult'] = [];
            if (null !== $this->queryResult && \is_array($this->queryResult)) {
                $n = 0;
                foreach ($this->queryResult as $item) {
                    $res['QueryResult'][$n++] = null !== $item ? $item->toMap() : $item;
                }
            }
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return data
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['QueryResult'])) {
            if (!empty($map['QueryResult'])) {
                $model->queryResult = [];
                $n                  = 0;
                foreach ($map['QueryResult'] as $item) {
                    $model->queryResult[$n++] = null !== $item ? queryResult::fromMap($item) : $item;
                }
            }
        }

        return $model;
    }
}
