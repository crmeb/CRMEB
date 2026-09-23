<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models\CheckMobilesCardSupportResponseBody\data;

use AlibabaCloud\Tea\Model;

class queryResult extends Model
{
    /**
     * @example 1390000****
     *
     * @var string
     */
    public $mobile;

    /**
     * @example true
     *
     * @var bool
     */
    public $support;
    protected $_name = [
        'mobile'  => 'mobile',
        'support' => 'support',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->mobile) {
            $res['mobile'] = $this->mobile;
        }
        if (null !== $this->support) {
            $res['support'] = $this->support;
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return queryResult
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['mobile'])) {
            $model->mobile = $map['mobile'];
        }
        if (isset($map['support'])) {
            $model->support = $map['support'];
        }

        return $model;
    }
}
