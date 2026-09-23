<?php

// This file is auto-generated, don't edit it. Thanks.
namespace Alipay\EasySDK\Marketing\OpenLife\Models;

use AlibabaCloud\Tea\Model;

class Article extends Model {
    protected $_name = [
        'title' => 'title',
        'desc' => 'desc',
        'imageUrl' => 'image_url',
        'url' => 'url',
        'actionName' => 'action_name',
    ];
    public function validate() {
        Model::validateRequired('desc', $this->desc, true);
        Model::validateRequired('url', $this->url, true);
    }
    public function toMap() {
        $res = [];
        if (null !== $this->title) {
            $res['title'] = $this->title;
        }
        if (null !== $this->desc) {
            $res['desc'] = $this->desc;
        }
        if (null !== $this->imageUrl) {
            $res['image_url'] = $this->imageUrl;
        }
        if (null !== $this->url) {
            $res['url'] = $this->url;
        }
        if (null !== $this->actionName) {
            $res['action_name'] = $this->actionName;
        }
        return $res;
    }
    /**
     * @param array $map
     * @return Article
     */
    public static function fromMap($map = []) {
        $model = new self();
        if(isset($map['title'])){
            $model->title = $map['title'];
        }
        if(isset($map['desc'])){
            $model->desc = $map['desc'];
        }
        if(isset($map['image_url'])){
            $model->imageUrl = $map['image_url'];
        }
        if(isset($map['url'])){
            $model->url = $map['url'];
        }
        if(isset($map['action_name'])){
            $model->actionName = $map['action_name'];
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
    public $desc;

    /**
     * @var string
     */
    public $imageUrl;

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $actionName;

}
