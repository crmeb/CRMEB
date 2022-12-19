<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models;

use AlibabaCloud\Tea\Model;

class CreateCardSmsTemplateShrinkRequest extends Model
{
    /**
     * @var string
     */
    public $factorys;

    /**
     * @var string
     */
    public $memo;

    /**
     * @var string
     */
    public $templateShrink;

    /**
     * @var string
     */
    public $templateName;
    protected $_name = [
        'factorys'       => 'Factorys',
        'memo'           => 'Memo',
        'templateShrink' => 'Template',
        'templateName'   => 'TemplateName',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->factorys) {
            $res['Factorys'] = $this->factorys;
        }
        if (null !== $this->memo) {
            $res['Memo'] = $this->memo;
        }
        if (null !== $this->templateShrink) {
            $res['Template'] = $this->templateShrink;
        }
        if (null !== $this->templateName) {
            $res['TemplateName'] = $this->templateName;
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return CreateCardSmsTemplateShrinkRequest
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['Factorys'])) {
            $model->factorys = $map['Factorys'];
        }
        if (isset($map['Memo'])) {
            $model->memo = $map['Memo'];
        }
        if (isset($map['Template'])) {
            $model->templateShrink = $map['Template'];
        }
        if (isset($map['TemplateName'])) {
            $model->templateName = $map['TemplateName'];
        }

        return $model;
    }
}
