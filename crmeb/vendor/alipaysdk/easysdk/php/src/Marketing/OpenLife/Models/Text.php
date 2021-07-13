<?php

// This file is auto-generated, don't edit it. Thanks.
namespace Alipay\EasySDK\Marketing\OpenLife\Models;

use AlibabaCloud\Tea\Model;

class Text extends Model {
    protected $_name = [
        'title' => 'title',
        'content' => 'content',
    ];
    public function validate() {
        Model::validateRequired('title', $this->title, true);
        Model::validateRequired('content', $this->content, true);
    }
    public function toMap() {
        $res = [];
        if (null !== $this->title) {
            $res['title'] = $this->title;
        }
        if (null !== $this->content) {
            $res['content'] = $this->content;
        }
        return $res;
    }
    /**
     * @param array $map
     * @return Text
     */
    public static function fromMap($map = []) {
        $model = new self();
        if(isset($map['title'])){
            $model->title = $map['title'];
        }
        if(isset($map['content'])){
            $model->content = $map['content'];
        }
        return $model;
    }
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $content;

}
