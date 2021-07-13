<?php

// This file is auto-generated, don't edit it. Thanks.
namespace Alipay\EasySDK\Marketing\OpenLife\Models;

use AlibabaCloud\Tea\Model;

class Keyword extends Model {
    protected $_name = [
        'color' => 'color',
        'value' => 'value',
    ];
    public function validate() {
        Model::validateRequired('color', $this->color, true);
        Model::validateRequired('value', $this->value, true);
    }
    public function toMap() {
        $res = [];
        if (null !== $this->color) {
            $res['color'] = $this->color;
        }
        if (null !== $this->value) {
            $res['value'] = $this->value;
        }
        return $res;
    }
    /**
     * @param array $map
     * @return Keyword
     */
    public static function fromMap($map = []) {
        $model = new self();
        if(isset($map['color'])){
            $model->color = $map['color'];
        }
        if(isset($map['value'])){
            $model->value = $map['value'];
        }
        return $model;
    }
    /**
     * @var string
     */
    public $color;

    /**
     * @var string
     */
    public $value;

}
