<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models;

use AlibabaCloud\Tea\Model;

class GetMediaResourceIdRequest extends Model
{
    /**
     * @example {\"img_rate\":\"oneToOne\"}
     *
     * @var string
     */
    public $extendInfo;

    /**
     * @example 12
     *
     * @var int
     */
    public $fileSize;

    /**
     * @var string
     */
    public $memo;

    /**
     * @example oss://alicom-fc-media/1947741454322274/alicom-fc-media/pic/202205191526575398603697152.png
     *
     * @var string
     */
    public $ossKey;

    /**
     * @example 1
     *
     * @var int
     */
    public $resourceType;
    protected $_name = [
        'extendInfo'   => 'ExtendInfo',
        'fileSize'     => 'FileSize',
        'memo'         => 'Memo',
        'ossKey'       => 'OssKey',
        'resourceType' => 'ResourceType',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->extendInfo) {
            $res['ExtendInfo'] = $this->extendInfo;
        }
        if (null !== $this->fileSize) {
            $res['FileSize'] = $this->fileSize;
        }
        if (null !== $this->memo) {
            $res['Memo'] = $this->memo;
        }
        if (null !== $this->ossKey) {
            $res['OssKey'] = $this->ossKey;
        }
        if (null !== $this->resourceType) {
            $res['ResourceType'] = $this->resourceType;
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return GetMediaResourceIdRequest
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['ExtendInfo'])) {
            $model->extendInfo = $map['ExtendInfo'];
        }
        if (isset($map['FileSize'])) {
            $model->fileSize = $map['FileSize'];
        }
        if (isset($map['Memo'])) {
            $model->memo = $map['Memo'];
        }
        if (isset($map['OssKey'])) {
            $model->ossKey = $map['OssKey'];
        }
        if (isset($map['ResourceType'])) {
            $model->resourceType = $map['ResourceType'];
        }

        return $model;
    }
}
