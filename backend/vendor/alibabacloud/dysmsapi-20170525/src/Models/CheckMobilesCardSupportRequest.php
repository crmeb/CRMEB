<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models;

use AlibabaCloud\Tea\Model;

class CheckMobilesCardSupportRequest extends Model
{
    /**
     * @var mixed[][]
     */
    public $mobiles;

    /**
     * @example CARD_SMS_****
     *
     * @var string
     */
    public $templateCode;
    protected $_name = [
        'mobiles'      => 'Mobiles',
        'templateCode' => 'TemplateCode',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->mobiles) {
            $res['Mobiles'] = $this->mobiles;
        }
        if (null !== $this->templateCode) {
            $res['TemplateCode'] = $this->templateCode;
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return CheckMobilesCardSupportRequest
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['Mobiles'])) {
            if (!empty($map['Mobiles'])) {
                $model->mobiles = $map['Mobiles'];
            }
        }
        if (isset($map['TemplateCode'])) {
            $model->templateCode = $map['TemplateCode'];
        }

        return $model;
    }
}
