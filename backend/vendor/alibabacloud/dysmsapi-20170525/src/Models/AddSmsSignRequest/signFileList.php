<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models\AddSmsSignRequest;

use AlibabaCloud\Tea\Model;

class signFileList extends Model
{
    /**
     * @example R0lGODlhHAAmAKIHAKqqqsvLy0hISObm5vf394uL****
     *
     * @var string
     */
    public $fileContents;

    /**
     * @example jpg
     *
     * @var string
     */
    public $fileSuffix;
    protected $_name = [
        'fileContents' => 'FileContents',
        'fileSuffix'   => 'FileSuffix',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->fileContents) {
            $res['FileContents'] = $this->fileContents;
        }
        if (null !== $this->fileSuffix) {
            $res['FileSuffix'] = $this->fileSuffix;
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return signFileList
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['FileContents'])) {
            $model->fileContents = $map['FileContents'];
        }
        if (isset($map['FileSuffix'])) {
            $model->fileSuffix = $map['FileSuffix'];
        }

        return $model;
    }
}
