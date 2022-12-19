<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendCardSmsRequest;

use AlibabaCloud\Tea\Model;

class cardObjects extends Model
{
    /**
     * @var string
     */
    public $customUrl;

    /**
     * @var string
     */
    public $dyncParams;

    /**
     * @var string
     */
    public $mobile;
    protected $_name = [
        'customUrl'  => 'customUrl',
        'dyncParams' => 'dyncParams',
        'mobile'     => 'mobile',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->customUrl) {
            $res['customUrl'] = $this->customUrl;
        }
        if (null !== $this->dyncParams) {
            $res['dyncParams'] = $this->dyncParams;
        }
        if (null !== $this->mobile) {
            $res['mobile'] = $this->mobile;
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return cardObjects
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['customUrl'])) {
            $model->customUrl = $map['customUrl'];
        }
        if (isset($map['dyncParams'])) {
            $model->dyncParams = $map['dyncParams'];
        }
        if (isset($map['mobile'])) {
            $model->mobile = $map['mobile'];
        }

        return $model;
    }
}
