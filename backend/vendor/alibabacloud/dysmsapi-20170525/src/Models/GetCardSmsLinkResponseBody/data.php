<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models\GetCardSmsLinkResponseBody;

use AlibabaCloud\Tea\Model;

class data extends Model
{
    /**
     * @var string
     */
    public $cardPhoneNumbers;

    /**
     * @var string
     */
    public $cardSignNames;

    /**
     * @example [\"mw2m.cn/LAaGGa\",\"mw2m.cn/LAAaes\"]
     *
     * @var string
     */
    public $cardSmsLinks;

    /**
     * @example 0
     *
     * @var int
     */
    public $cardTmpState;

    /**
     * @example 1390000****
     *
     * @var string
     */
    public $notMediaMobiles;
    protected $_name = [
        'cardPhoneNumbers' => 'CardPhoneNumbers',
        'cardSignNames'    => 'CardSignNames',
        'cardSmsLinks'     => 'CardSmsLinks',
        'cardTmpState'     => 'CardTmpState',
        'notMediaMobiles'  => 'NotMediaMobiles',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->cardPhoneNumbers) {
            $res['CardPhoneNumbers'] = $this->cardPhoneNumbers;
        }
        if (null !== $this->cardSignNames) {
            $res['CardSignNames'] = $this->cardSignNames;
        }
        if (null !== $this->cardSmsLinks) {
            $res['CardSmsLinks'] = $this->cardSmsLinks;
        }
        if (null !== $this->cardTmpState) {
            $res['CardTmpState'] = $this->cardTmpState;
        }
        if (null !== $this->notMediaMobiles) {
            $res['NotMediaMobiles'] = $this->notMediaMobiles;
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
        if (isset($map['CardPhoneNumbers'])) {
            $model->cardPhoneNumbers = $map['CardPhoneNumbers'];
        }
        if (isset($map['CardSignNames'])) {
            $model->cardSignNames = $map['CardSignNames'];
        }
        if (isset($map['CardSmsLinks'])) {
            $model->cardSmsLinks = $map['CardSmsLinks'];
        }
        if (isset($map['CardTmpState'])) {
            $model->cardTmpState = $map['CardTmpState'];
        }
        if (isset($map['NotMediaMobiles'])) {
            $model->notMediaMobiles = $map['NotMediaMobiles'];
        }

        return $model;
    }
}
