<?php

// This file is auto-generated, don't edit it. Thanks.
namespace Alipay\EasySDK\Member\Identification\Models;

use AlibabaCloud\Tea\Model;

class IdentityParam extends Model {
    protected $_name = [
        'identityType' => 'identity_type',
        'certType' => 'cert_type',
        'certName' => 'cert_name',
        'certNo' => 'cert_no',
    ];
    public function validate() {
        Model::validateRequired('identityType', $this->identityType, true);
        Model::validateRequired('certType', $this->certType, true);
        Model::validateRequired('certName', $this->certName, true);
        Model::validateRequired('certNo', $this->certNo, true);
    }
    public function toMap() {
        $res = [];
        if (null !== $this->identityType) {
            $res['identity_type'] = $this->identityType;
        }
        if (null !== $this->certType) {
            $res['cert_type'] = $this->certType;
        }
        if (null !== $this->certName) {
            $res['cert_name'] = $this->certName;
        }
        if (null !== $this->certNo) {
            $res['cert_no'] = $this->certNo;
        }
        return $res;
    }
    /**
     * @param array $map
     * @return IdentityParam
     */
    public static function fromMap($map = []) {
        $model = new self();
        if(isset($map['identity_type'])){
            $model->identityType = $map['identity_type'];
        }
        if(isset($map['cert_type'])){
            $model->certType = $map['cert_type'];
        }
        if(isset($map['cert_name'])){
            $model->certName = $map['cert_name'];
        }
        if(isset($map['cert_no'])){
            $model->certNo = $map['cert_no'];
        }
        return $model;
    }
    /**
     * @var string
     */
    public $identityType;

    /**
     * @var string
     */
    public $certType;

    /**
     * @var string
     */
    public $certName;

    /**
     * @var string
     */
    public $certNo;

}
