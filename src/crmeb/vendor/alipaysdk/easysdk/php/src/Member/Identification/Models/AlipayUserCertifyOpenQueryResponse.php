<?php

// This file is auto-generated, don't edit it. Thanks.
namespace Alipay\EasySDK\Member\Identification\Models;

use AlibabaCloud\Tea\Model;

class AlipayUserCertifyOpenQueryResponse extends Model {
    protected $_name = [
        'httpBody' => 'http_body',
        'code' => 'code',
        'msg' => 'msg',
        'subCode' => 'sub_code',
        'subMsg' => 'sub_msg',
        'passed' => 'passed',
        'identityInfo' => 'identity_info',
        'materialInfo' => 'material_info',
    ];
    public function validate() {
        Model::validateRequired('httpBody', $this->httpBody, true);
        Model::validateRequired('code', $this->code, true);
        Model::validateRequired('msg', $this->msg, true);
        Model::validateRequired('subCode', $this->subCode, true);
        Model::validateRequired('subMsg', $this->subMsg, true);
        Model::validateRequired('passed', $this->passed, true);
        Model::validateRequired('identityInfo', $this->identityInfo, true);
        Model::validateRequired('materialInfo', $this->materialInfo, true);
    }
    public function toMap() {
        $res = [];
        if (null !== $this->httpBody) {
            $res['http_body'] = $this->httpBody;
        }
        if (null !== $this->code) {
            $res['code'] = $this->code;
        }
        if (null !== $this->msg) {
            $res['msg'] = $this->msg;
        }
        if (null !== $this->subCode) {
            $res['sub_code'] = $this->subCode;
        }
        if (null !== $this->subMsg) {
            $res['sub_msg'] = $this->subMsg;
        }
        if (null !== $this->passed) {
            $res['passed'] = $this->passed;
        }
        if (null !== $this->identityInfo) {
            $res['identity_info'] = $this->identityInfo;
        }
        if (null !== $this->materialInfo) {
            $res['material_info'] = $this->materialInfo;
        }
        return $res;
    }
    /**
     * @param array $map
     * @return AlipayUserCertifyOpenQueryResponse
     */
    public static function fromMap($map = []) {
        $model = new self();
        if(isset($map['http_body'])){
            $model->httpBody = $map['http_body'];
        }
        if(isset($map['code'])){
            $model->code = $map['code'];
        }
        if(isset($map['msg'])){
            $model->msg = $map['msg'];
        }
        if(isset($map['sub_code'])){
            $model->subCode = $map['sub_code'];
        }
        if(isset($map['sub_msg'])){
            $model->subMsg = $map['sub_msg'];
        }
        if(isset($map['passed'])){
            $model->passed = $map['passed'];
        }
        if(isset($map['identity_info'])){
            $model->identityInfo = $map['identity_info'];
        }
        if(isset($map['material_info'])){
            $model->materialInfo = $map['material_info'];
        }
        return $model;
    }
    /**
     * @description 响应原始字符串
     * @var string
     */
    public $httpBody;

    /**
     * @var string
     */
    public $code;

    /**
     * @var string
     */
    public $msg;

    /**
     * @var string
     */
    public $subCode;

    /**
     * @var string
     */
    public $subMsg;

    /**
     * @var string
     */
    public $passed;

    /**
     * @var string
     */
    public $identityInfo;

    /**
     * @var string
     */
    public $materialInfo;

}
