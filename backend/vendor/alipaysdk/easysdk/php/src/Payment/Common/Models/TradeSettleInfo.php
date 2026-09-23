<?php

// This file is auto-generated, don't edit it. Thanks.
namespace Alipay\EasySDK\Payment\Common\Models;

use AlibabaCloud\Tea\Model;

use Alipay\EasySDK\Payment\Common\Models\TradeSettleDetail;

class TradeSettleInfo extends Model {
    protected $_name = [
        'tradeSettleDetailList' => 'trade_settle_detail_list',
    ];
    public function validate() {
        Model::validateRequired('tradeSettleDetailList', $this->tradeSettleDetailList, true);
    }
    public function toMap() {
        $res = [];
        if (null !== $this->tradeSettleDetailList) {
            $res['trade_settle_detail_list'] = [];
            if(null !== $this->tradeSettleDetailList && is_array($this->tradeSettleDetailList)){
                $n = 0;
                foreach($this->tradeSettleDetailList as $item){
                    $res['trade_settle_detail_list'][$n++] = null !== $item ? $item->toMap() : $item;
                }
            }
        }
        return $res;
    }
    /**
     * @param array $map
     * @return TradeSettleInfo
     */
    public static function fromMap($map = []) {
        $model = new self();
        if(isset($map['trade_settle_detail_list'])){
            if(!empty($map['trade_settle_detail_list'])){
                $model->tradeSettleDetailList = [];
                $n = 0;
                foreach($map['trade_settle_detail_list'] as $item) {
                    $model->tradeSettleDetailList[$n++] = null !== $item ? TradeSettleDetail::fromMap($item) : $item;
                }
            }
        }
        return $model;
    }
    /**
     * @var TradeSettleDetail[]
     */
    public $tradeSettleDetailList;

}
