<?php

// This file is auto-generated, don't edit it. Thanks.
namespace Alipay\EasySDK\Payment\Huabei\Models;

use AlibabaCloud\Tea\Model;

class HuabeiConfig extends Model {
    protected $_name = [
        'hbFqNum' => 'hb_fq_num',
        'hbFqSellerPercent' => 'hb_fq_seller_percent',
    ];
    public function validate() {
        Model::validateRequired('hbFqNum', $this->hbFqNum, true);
        Model::validateRequired('hbFqSellerPercent', $this->hbFqSellerPercent, true);
    }
    public function toMap() {
        $res = [];
        if (null !== $this->hbFqNum) {
            $res['hb_fq_num'] = $this->hbFqNum;
        }
        if (null !== $this->hbFqSellerPercent) {
            $res['hb_fq_seller_percent'] = $this->hbFqSellerPercent;
        }
        return $res;
    }
    /**
     * @param array $map
     * @return HuabeiConfig
     */
    public static function fromMap($map = []) {
        $model = new self();
        if(isset($map['hb_fq_num'])){
            $model->hbFqNum = $map['hb_fq_num'];
        }
        if(isset($map['hb_fq_seller_percent'])){
            $model->hbFqSellerPercent = $map['hb_fq_seller_percent'];
        }
        return $model;
    }
    /**
     * @var string
     */
    public $hbFqNum;

    /**
     * @var string
     */
    public $hbFqSellerPercent;

}
