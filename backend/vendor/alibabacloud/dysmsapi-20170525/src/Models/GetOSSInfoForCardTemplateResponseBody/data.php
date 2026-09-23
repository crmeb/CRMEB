<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models\GetOSSInfoForCardTemplateResponseBody;

use AlibabaCloud\Tea\Model;

class data extends Model
{
    /**
     * @example LTAIxetqt1Dg****
     *
     * @var string
     */
    public $accessKeyId;

    /**
     * @example 599333677478****
     *
     * @var string
     */
    public $aliUid;

    /**
     * @example alicom-cardsms-resources
     *
     * @var string
     */
    public $bucket;

    /**
     * @example 1634209418
     *
     * @var string
     */
    public $expireTime;

    /**
     * @example https://alicom-cardsms-resources.oss-cn-zhangjiakou.aliyuncs.com
     *
     * @var string
     */
    public $host;

    /**
     * @example eyJxxx0=
     *
     * @var string
     */
    public $policy;

    /**
     * @var string
     */
    public $signature;

    /**
     * @example 1631792777
     *
     * @var string
     */
    public $startPath;
    protected $_name = [
        'accessKeyId' => 'AccessKeyId',
        'aliUid'      => 'AliUid',
        'bucket'      => 'Bucket',
        'expireTime'  => 'ExpireTime',
        'host'        => 'Host',
        'policy'      => 'Policy',
        'signature'   => 'Signature',
        'startPath'   => 'StartPath',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->accessKeyId) {
            $res['AccessKeyId'] = $this->accessKeyId;
        }
        if (null !== $this->aliUid) {
            $res['AliUid'] = $this->aliUid;
        }
        if (null !== $this->bucket) {
            $res['Bucket'] = $this->bucket;
        }
        if (null !== $this->expireTime) {
            $res['ExpireTime'] = $this->expireTime;
        }
        if (null !== $this->host) {
            $res['Host'] = $this->host;
        }
        if (null !== $this->policy) {
            $res['Policy'] = $this->policy;
        }
        if (null !== $this->signature) {
            $res['Signature'] = $this->signature;
        }
        if (null !== $this->startPath) {
            $res['StartPath'] = $this->startPath;
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
        if (isset($map['AccessKeyId'])) {
            $model->accessKeyId = $map['AccessKeyId'];
        }
        if (isset($map['AliUid'])) {
            $model->aliUid = $map['AliUid'];
        }
        if (isset($map['Bucket'])) {
            $model->bucket = $map['Bucket'];
        }
        if (isset($map['ExpireTime'])) {
            $model->expireTime = $map['ExpireTime'];
        }
        if (isset($map['Host'])) {
            $model->host = $map['Host'];
        }
        if (isset($map['Policy'])) {
            $model->policy = $map['Policy'];
        }
        if (isset($map['Signature'])) {
            $model->signature = $map['Signature'];
        }
        if (isset($map['StartPath'])) {
            $model->startPath = $map['StartPath'];
        }

        return $model;
    }
}
