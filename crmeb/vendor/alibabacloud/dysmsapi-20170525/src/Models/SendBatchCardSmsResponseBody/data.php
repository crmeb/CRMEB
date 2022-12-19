<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendBatchCardSmsResponseBody;

use AlibabaCloud\Tea\Model;

class data extends Model
{
    /**
     * @var string
     */
    public $bizCardId;

    /**
     * @var string
     */
    public $bizDigitalId;

    /**
     * @var string
     */
    public $bizSmsId;

    /**
     * @var int
     */
    public $cardTmpState;

    /**
     * @var string
     */
    public $mediaMobiles;

    /**
     * @var string
     */
    public $notMediaMobiles;
    protected $_name = [
        'bizCardId'       => 'BizCardId',
        'bizDigitalId'    => 'BizDigitalId',
        'bizSmsId'        => 'BizSmsId',
        'cardTmpState'    => 'CardTmpState',
        'mediaMobiles'    => 'MediaMobiles',
        'notMediaMobiles' => 'NotMediaMobiles',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->bizCardId) {
            $res['BizCardId'] = $this->bizCardId;
        }
        if (null !== $this->bizDigitalId) {
            $res['BizDigitalId'] = $this->bizDigitalId;
        }
        if (null !== $this->bizSmsId) {
            $res['BizSmsId'] = $this->bizSmsId;
        }
        if (null !== $this->cardTmpState) {
            $res['CardTmpState'] = $this->cardTmpState;
        }
        if (null !== $this->mediaMobiles) {
            $res['MediaMobiles'] = $this->mediaMobiles;
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
        if (isset($map['BizCardId'])) {
            $model->bizCardId = $map['BizCardId'];
        }
        if (isset($map['BizDigitalId'])) {
            $model->bizDigitalId = $map['BizDigitalId'];
        }
        if (isset($map['BizSmsId'])) {
            $model->bizSmsId = $map['BizSmsId'];
        }
        if (isset($map['CardTmpState'])) {
            $model->cardTmpState = $map['CardTmpState'];
        }
        if (isset($map['MediaMobiles'])) {
            $model->mediaMobiles = $map['MediaMobiles'];
        }
        if (isset($map['NotMediaMobiles'])) {
            $model->notMediaMobiles = $map['NotMediaMobiles'];
        }

        return $model;
    }
}
