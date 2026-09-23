<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models\CreateSmartShortUrlResponseBody;

use AlibabaCloud\Tea\Model;

class model_ extends Model
{
    /**
     * @example 示例值
     *
     * @var string
     */
    public $domain;

    /**
     * @example 11
     *
     * @var int
     */
    public $expiration;

    /**
     * @example 示例值
     *
     * @var string
     */
    public $phoneNumber;

    /**
     * @example 示例值
     *
     * @var string
     */
    public $shortName;

    /**
     * @example 示例值示例值
     *
     * @var string
     */
    public $shortUrl;
    protected $_name = [
        'domain'      => 'Domain',
        'expiration'  => 'Expiration',
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
        if (null !== $this->domain) {
            $res['Domain'] = $this->domain;
        }
        if (null !== $this->expiration) {
            $res['Expiration'] = $this->expiration;
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
     * @return model_
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['Domain'])) {
            $model->domain = $map['Domain'];
        }
        if (isset($map['Expiration'])) {
            $model->expiration = $map['Expiration'];
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
