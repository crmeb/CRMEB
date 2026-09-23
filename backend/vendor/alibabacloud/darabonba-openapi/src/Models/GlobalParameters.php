<?php

// This file is auto-generated, don't edit it. Thanks.
namespace Darabonba\OpenApi\Models;

use AlibabaCloud\Tea\Model;

class GlobalParameters extends Model
{
    public function validate() {}
    public function toMap()
    {
        $res = [];
        if (null !== $this->headers) {
            $res['headers'] = $this->headers;
        }
        if (null !== $this->queries) {
            $res['queries'] = $this->queries;
        }
        return $res;
    }
    /**
     * @param array $map
     * @return GlobalParameters
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['headers'])) {
            $model->headers = $map['headers'];
        }
        if (isset($map['queries'])) {
            $model->queries = $map['queries'];
        }
        return $model;
    }
    public $headers;

    public $queries;
}
