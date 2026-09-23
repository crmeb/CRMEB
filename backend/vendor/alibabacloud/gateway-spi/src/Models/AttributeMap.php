<?php

// This file is auto-generated, don't edit it. Thanks.
namespace Darabonba\GatewaySpi\Models;

use AlibabaCloud\Tea\Model;

class AttributeMap extends Model {
    public function validate() {
        Model::validateRequired('attributes', $this->attributes, true);
        Model::validateRequired('key', $this->key, true);
    }
    public function toMap() {
        $res = [];
        if (null !== $this->attributes) {
            $res['attributes'] = $this->attributes;
        }
        if (null !== $this->key) {
            $res['key'] = $this->key;
        }
        return $res;
    }
    /**
     * @param array $map
     * @return AttributeMap
     */
    public static function fromMap($map = []) {
        $model = new self();
        if(isset($map['attributes'])){
            $model->attributes = $map['attributes'];
        }
        if(isset($map['key'])){
            $model->key = $map['key'];
        }
        return $model;
    }
    /**
     * @var mixed[]
     */
    public $attributes;

    /**
     * @var string[]
     */
    public $key;

}
