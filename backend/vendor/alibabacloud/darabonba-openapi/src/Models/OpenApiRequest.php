<?php

// This file is auto-generated, don't edit it. Thanks.
namespace Darabonba\OpenApi\Models;

use AlibabaCloud\Tea\Model;

class OpenApiRequest extends Model
{
    public function validate() {}
    public function toMap()
    {
        $res = [];
        if (null !== $this->headers) {
            $res['headers'] = $this->headers;
        }
        if (null !== $this->query) {
            $res['query'] = $this->query;
        }
        if (null !== $this->body) {
            $res['body'] = $this->body;
        }
        if (null !== $this->stream) {
            $res['stream'] = $this->stream;
        }
        if (null !== $this->hostMap) {
            $res['hostMap'] = $this->hostMap;
        }
        if (null !== $this->endpointOverride) {
            $res['endpointOverride'] = $this->endpointOverride;
        }
        return $res;
    }
    /**
     * @param array $map
     * @return OpenApiRequest
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['headers'])) {
            $model->headers = $map['headers'];
        }
        if (isset($map['query'])) {
            $model->query = $map['query'];
        }
        if (isset($map['body'])) {
            $model->body = $map['body'];
        }
        if (isset($map['stream'])) {
            $model->stream = $map['stream'];
        }
        if (isset($map['hostMap'])) {
            $model->hostMap = $map['hostMap'];
        }
        if (isset($map['endpointOverride'])) {
            $model->endpointOverride = $map['endpointOverride'];
        }
        return $model;
    }
    public $headers;

    public $query;

    public $body;

    public $stream;

    public $hostMap;

    public $endpointOverride;
}
