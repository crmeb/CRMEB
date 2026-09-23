<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models;

use AlibabaCloud\Tea\Model;

class QueryMobilesCardSupportShrinkRequest extends Model
{
    /**
     * @var string
     */
    public $mobilesShrink;

    /**
     * @var string
     */
    public $templateCode;
    protected $_name = [
        'mobilesShrink' => 'Mobiles',
        'templateCode'  => 'TemplateCode',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->mobilesShrink) {
            $res['Mobiles'] = $this->mobilesShrink;
        }
        if (null !== $this->templateCode) {
            $res['TemplateCode'] = $this->templateCode;
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return QueryMobilesCardSupportShrinkRequest
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['Mobiles'])) {
            $model->mobilesShrink = $map['Mobiles'];
        }
        if (isset($map['TemplateCode'])) {
            $model->templateCode = $map['TemplateCode'];
        }

        return $model;
    }
}
