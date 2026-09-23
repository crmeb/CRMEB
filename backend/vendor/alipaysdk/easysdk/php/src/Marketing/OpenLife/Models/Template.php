<?php

// This file is auto-generated, don't edit it. Thanks.
namespace Alipay\EasySDK\Marketing\OpenLife\Models;

use AlibabaCloud\Tea\Model;

use Alipay\EasySDK\Marketing\OpenLife\Models\Context;

class Template extends Model {
    protected $_name = [
        'templateId' => 'template_id',
        'context' => 'context',
    ];
    public function validate() {
        Model::validateRequired('templateId', $this->templateId, true);
        Model::validateRequired('context', $this->context, true);
    }
    public function toMap() {
        $res = [];
        if (null !== $this->templateId) {
            $res['template_id'] = $this->templateId;
        }
        if (null !== $this->context) {
            $res['context'] = null !== $this->context ? $this->context->toMap() : null;
        }
        return $res;
    }
    /**
     * @param array $map
     * @return Template
     */
    public static function fromMap($map = []) {
        $model = new self();
        if(isset($map['template_id'])){
            $model->templateId = $map['template_id'];
        }
        if(isset($map['context'])){
            $model->context = Context::fromMap($map['context']);
        }
        return $model;
    }
    /**
     * @var string
     */
    public $templateId;

    /**
     * @var Context
     */
    public $context;

}
