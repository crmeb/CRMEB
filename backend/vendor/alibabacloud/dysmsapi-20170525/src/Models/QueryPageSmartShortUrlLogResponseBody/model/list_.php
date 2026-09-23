<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QueryPageSmartShortUrlLogResponseBody\model;

use AlibabaCloud\Tea\Model;

class list_ extends Model
{
    /**
     * @example 87
     *
     * @var int
     */
    public $clickState;

    /**
     * @example 51
     *
     * @var int
     */
    public $clickTime;

    /**
     * @example 64
     *
     * @var int
     */
    public $createTime;

    /**
     * @example 示例值示例值
     *
     * @var string
     */
    public $phoneNumber;

    /**
     * @example 示例值示例值示例值
     *
     * @var string
     */
    public $shortName;

    /**
     * @example 示例值示例值示例值
     *
     * @var string
     */
    public $shortUrl;
    protected $_name = [
        'clickState'  => 'ClickState',
        'clickTime'   => 'ClickTime',
        'createTime'  => 'CreateTime',
        'phoneNumber' => 'PhoneNumber',
        'shortName'   => 'ShortName',
        'shortUrl'    => 'ShortUrl',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->clickState) {
            $res['ClickState'] = $this->clickState;
        }
        if (null !== $this->clickTime) {
            $res['ClickTime'] = $this->clickTime;
        }
        if (null !== $this->createTime) {
            $res['CreateTime'] = $this->createTime;
        }
        if (null !== $this->phoneNumber) {
            $res['PhoneNumber'] = $this->phoneNumber;
        }
        if (null !== $this->shortName) {
            $res['ShortName'] = $this->shortName;
        }
        if (null !== $this->shortUrl) {
            $res['ShortUrl'] = $this->shortUrl;
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return list_
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['ClickState'])) {
            $model->clickState = $map['ClickState'];
        }
        if (isset($map['ClickTime'])) {
            $model->clickTime = $map['ClickTime'];
        }
        if (isset($map['CreateTime'])) {
            $model->createTime = $map['CreateTime'];
        }
        if (isset($map['PhoneNumber'])) {
            $model->phoneNumber = $map['PhoneNumber'];
        }
        if (isset($map['ShortName'])) {
            $model->shortName = $map['ShortName'];
        }
        if (isset($map['ShortUrl'])) {
            $model->shortUrl = $map['ShortUrl'];
        }

        return $model;
    }
}
