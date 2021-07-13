<?php

// This file is auto-generated, don't edit it. Thanks.
namespace Alipay\EasySDK\Marketing\OpenLife\Models;

use AlibabaCloud\Tea\Model;

class Context extends Model {
    protected $_name = [
        'headColor' => 'head_color',
        'url' => 'url',
        'actionName' => 'action_name',
        'keyword1' => 'keyword1',
        'keyword2' => 'keyword2',
        'first' => 'first',
        'remark' => 'remark',
    ];
    public function validate() {
        Model::validateRequired('headColor', $this->headColor, true);
        Model::validateRequired('url', $this->url, true);
        Model::validateRequired('actionName', $this->actionName, true);
    }
    public function toMap() {
        $res = [];
        if (null !== $this->headColor) {
            $res['head_color'] = $this->headColor;
        }
        if (null !== $this->url) {
            $res['url'] = $this->url;
        }
        if (null !== $this->actionName) {
            $res['action_name'] = $this->actionName;
        }
        if (null !== $this->keyword1) {
            $res['keyword1'] = null !== $this->keyword1 ? $this->keyword1->toMap() : null;
        }
        if (null !== $this->keyword2) {
            $res['keyword2'] = null !== $this->keyword2 ? $this->keyword2->toMap() : null;
        }
        if (null !== $this->first) {
            $res['first'] = null !== $this->first ? $this->first->toMap() : null;
        }
        if (null !== $this->remark) {
            $res['remark'] = null !== $this->remark ? $this->remark->toMap() : null;
        }
        return $res;
    }
    /**
     * @param array $map
     * @return Context
     */
    public static function fromMap($map = []) {
        $model = new self();
        if(isset($map['head_color'])){
            $model->headColor = $map['head_color'];
        }
        if(isset($map['url'])){
            $model->url = $map['url'];
        }
        if(isset($map['action_name'])){
            $model->actionName = $map['action_name'];
        }
        if(isset($map['keyword1'])){
            $model->keyword1 = Keyword::fromMap($map['keyword1']);
        }
        if(isset($map['keyword2'])){
            $model->keyword2 = Keyword::fromMap($map['keyword2']);
        }
        if(isset($map['first'])){
            $model->first = Keyword::fromMap($map['first']);
        }
        if(isset($map['remark'])){
            $model->remark = Keyword::fromMap($map['remark']);
        }
        return $model;
    }
    /**
     * @var string
     */
    public $headColor;

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $actionName;

    /**
     * @var Keyword
     */
    public $keyword1;

    /**
     * @var Keyword
     */
    public $keyword2;

    /**
     * @var Keyword
     */
    public $first;

    /**
     * @var Keyword
     */
    public $remark;

}
