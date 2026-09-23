<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models;

use AlibabaCloud\Tea\Model;

class CreateCardSmsTemplateRequest extends Model
{
    /**
     * @example XiaoMi
     *
     * @var string
     */
    public $factorys;

    /**
     * @var string
     */
    public $memo;

    /**
     * @var mixed[]
     */
    public $template;

    /**
     * @var string
     */
    public $templateName;
    protected $_name = [
        'factorys'     => 'Factorys',
        'memo'         => 'Memo',
        'template'     => 'Template',
        'templateName' => 'TemplateName',
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
        if (null !== $this->template) {
            $res['Template'] = $this->template;
        }
        if (null !== $this->templateName) {
            $res['TemplateName'] = $this->templateName;
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return CreateCardSmsTemplateRequest
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
            $model->template = $map['Template'];
        }
        if (isset($map['TemplateName'])) {
            $model->templateName = $map['TemplateName'];
        }

        return $model;
    }
}
