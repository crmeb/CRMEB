<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QueryShortUrlResponseBody;

use AlibabaCloud\Tea\Model;

class data extends Model
{
    /**
     * @example 2019-01-08 16:44:13
     *
     * @var string
     */
    public $createDate;

    /**
     * @example 2019-01-22 11:21:11
     *
     * @var string
     */
    public $expireDate;

    /**
     * @example 300
     *
     * @var string
     */
    public $pageViewCount;

    /**
     * @example http://****.cn/6y8uy7
     *
     * @var string
     */
    public $shortUrl;

    /**
     * @var string
     */
    public $shortUrlName;

    /**
     * @example expired
     *
     * @var string
     */
    public $shortUrlStatus;

    /**
     * @example https://www.****.com/product/sms
     *
     * @var string
     */
    public $sourceUrl;

    /**
     * @example 23
     *
     * @var string
     */
    public $uniqueVisitorCount;
    protected $_name = [
        'createDate'         => 'CreateDate',
        'expireDate'         => 'ExpireDate',
        'pageViewCount'      => 'PageViewCount',
        'shortUrl'           => 'ShortUrl',
        'shortUrlName'       => 'ShortUrlName',
        'shortUrlStatus'     => 'ShortUrlStatus',
        'sourceUrl'          => 'SourceUrl',
        'uniqueVisitorCount' => 'UniqueVisitorCount',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->createDate) {
            $res['CreateDate'] = $this->createDate;
        }
        if (null !== $this->expireDate) {
            $res['ExpireDate'] = $this->expireDate;
        }
        if (null !== $this->pageViewCount) {
            $res['PageViewCount'] = $this->pageViewCount;
        }
        if (null !== $this->shortUrl) {
            $res['ShortUrl'] = $this->shortUrl;
        }
        if (null !== $this->shortUrlName) {
            $res['ShortUrlName'] = $this->shortUrlName;
        }
        if (null !== $this->shortUrlStatus) {
            $res['ShortUrlStatus'] = $this->shortUrlStatus;
        }
        if (null !== $this->sourceUrl) {
            $res['SourceUrl'] = $this->sourceUrl;
        }
        if (null !== $this->uniqueVisitorCount) {
            $res['UniqueVisitorCount'] = $this->uniqueVisitorCount;
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
        if (isset($map['CreateDate'])) {
            $model->createDate = $map['CreateDate'];
        }
        if (isset($map['ExpireDate'])) {
            $model->expireDate = $map['ExpireDate'];
        }
        if (isset($map['PageViewCount'])) {
            $model->pageViewCount = $map['PageViewCount'];
        }
        if (isset($map['ShortUrl'])) {
            $model->shortUrl = $map['ShortUrl'];
        }
        if (isset($map['ShortUrlName'])) {
            $model->shortUrlName = $map['ShortUrlName'];
        }
        if (isset($map['ShortUrlStatus'])) {
            $model->shortUrlStatus = $map['ShortUrlStatus'];
        }
        if (isset($map['SourceUrl'])) {
            $model->sourceUrl = $map['SourceUrl'];
        }
        if (isset($map['UniqueVisitorCount'])) {
            $model->uniqueVisitorCount = $map['UniqueVisitorCount'];
        }

        return $model;
    }
}
