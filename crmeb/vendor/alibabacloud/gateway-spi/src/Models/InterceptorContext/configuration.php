<?php

// This file is auto-generated, don't edit it. Thanks.
namespace Darabonba\GatewaySpi\Models\InterceptorContext;

use AlibabaCloud\Tea\Model;

class configuration extends Model {
    public function validate() {
        Model::validateRequired('regionId', $this->regionId, true);
    }
    public function toMap() {
        $res = [];
        if (null !== $this->regionId) {
            $res['regionId'] = $this->regionId;
        }
        if (null !== $this->endpoint) {
            $res['endpoint'] = $this->endpoint;
        }
        if (null !== $this->endpointRule) {
            $res['endpointRule'] = $this->endpointRule;
        }
        if (null !== $this->endpointMap) {
            $res['endpointMap'] = $this->endpointMap;
        }
        if (null !== $this->endpointType) {
            $res['endpointType'] = $this->endpointType;
        }
        if (null !== $this->network) {
            $res['network'] = $this->network;
        }
        if (null !== $this->suffix) {
            $res['suffix'] = $this->suffix;
        }
        return $res;
    }
    /**
     * @param array $map
     * @return configuration
     */
    public static function fromMap($map = []) {
        $model = new self();
        if(isset($map['regionId'])){
            $model->regionId = $map['regionId'];
        }
        if(isset($map['endpoint'])){
            $model->endpoint = $map['endpoint'];
        }
        if(isset($map['endpointRule'])){
            $model->endpointRule = $map['endpointRule'];
        }
        if(isset($map['endpointMap'])){
            $model->endpointMap = $map['endpointMap'];
        }
        if(isset($map['endpointType'])){
            $model->endpointType = $map['endpointType'];
        }
        if(isset($map['network'])){
            $model->network = $map['network'];
        }
        if(isset($map['suffix'])){
            $model->suffix = $map['suffix'];
        }
        return $model;
    }
    /**
     * @var string
     */
    public $regionId;

    public $endpoint;

    public $endpointRule;

    public $endpointMap;

    public $endpointType;

    public $network;

    public $suffix;

}
