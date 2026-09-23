<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models\CheckMobilesCardSupportResponseBody;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\CheckMobilesCardSupportResponseBody\data\queryResult;
use AlibabaCloud\Tea\Model;

class data extends Model
{
    /**
     * @var queryResult[]
     */
    public $queryResult;
    protected $_name = [
        'queryResult' => 'queryResult',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->queryResult) {
            $res['queryResult'] = [];
            if (null !== $this->queryResult && \is_array($this->queryResult)) {
                $n = 0;
                foreach ($this->queryResult as $item) {
                    $res['queryResult'][$n++] = null !== $item ? $item->toMap() : $item;
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
        if (isset($map['queryResult'])) {
            if (!empty($map['queryResult'])) {
                $model->queryResult = [];
                $n                  = 0;
                foreach ($map['queryResult'] as $item) {
                    $model->queryResult[$n++] = null !== $item ? queryResult::fromMap($item) : $item;
                }
            }
        }

        return $model;
    }
}
