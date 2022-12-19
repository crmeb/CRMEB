<?php

// This file is auto-generated, don't edit it. Thanks.
namespace Darabonba\GatewaySpi\Models;

use AlibabaCloud\Tea\Model;

use Darabonba\GatewaySpi\Models\InterceptorContext\request;
use Darabonba\GatewaySpi\Models\InterceptorContext\configuration;
use Darabonba\GatewaySpi\Models\InterceptorContext\response;

class InterceptorContext extends Model {
    public function validate() {
        Model::validateRequired('request', $this->request, true);
        Model::validateRequired('configuration', $this->configuration, true);
        Model::validateRequired('response', $this->response, true);
    }
    public function toMap() {
        $res = [];
        if (null !== $this->request) {
            $res['request'] = null !== $this->request ? $this->request->toMap() : null;
        }
        if (null !== $this->configuration) {
            $res['configuration'] = null !== $this->configuration ? $this->configuration->toMap() : null;
        }
        if (null !== $this->response) {
            $res['response'] = null !== $this->response ? $this->response->toMap() : null;
        }
        return $res;
    }
    /**
     * @param array $map
     * @return InterceptorContext
     */
    public static function fromMap($map = []) {
        $model = new self();
        if(isset($map['request'])){
            $model->request = request::fromMap($map['request']);
        }
        if(isset($map['configuration'])){
            $model->configuration = configuration::fromMap($map['configuration']);
        }
        if(isset($map['response'])){
            $model->response = response::fromMap($map['response']);
        }
        return $model;
    }
    /**
     * @var request
     */
    public $request;

    /**
     * @var configuration
     */
    public $configuration;

    /**
     * @var response
     */
    public $response;

}
