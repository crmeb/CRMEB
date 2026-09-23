<?php

// This file is auto-generated, don't edit it. Thanks.
namespace AlibabaCloud\OpenApiUtil\Tests\Models;

use AlibabaCloud\Tea\Model;
use GuzzleHttp\Psr7\Stream;

class SourceModel extends Model {
    protected $_name = [
        'test' => 'Test',
        'empty' => 'empty',
        'bodyObject' => 'body',
        'listObject' => 'list',
    ];
    public function validate() {}
    public function toMap() {
        $res = [];
        if (null !== $this->test) {
            $res['Test'] = $this->test;
        }
        if (null !== $this->empty) {
            $res['empty'] = $this->empty;
        }
        if (null !== $this->bodyObject) {
            $res['body'] = $this->bodyObject;
        }
        if (null !== $this->listObject) {
            $res['list'] = $this->listObject;
        }
        if (null !== $this->urlListObject) {
            $res['urlList'] = [];
            if(null !== $this->urlListObject && is_array($this->urlListObject)){
                $n = 0;
                foreach($this->urlListObject as $item){
                    $res['urlList'][$n++] = null !== $item ? $item->toMap() : $item;
                }
            }
        }
        return $res;
    }
    /**
     * @param array $map
     * @return SourceModel
     */
    public static function fromMap($map = []) {
        $model = new self();
        if(isset($map['Test'])){
            $model->test = $map['Test'];
        }
        if(isset($map['empty'])){
            $model->empty = $map['empty'];
        }
        if(isset($map['body'])){
            $model->bodyObject = $map['body'];
        }
        if(isset($map['list'])){
            if(!empty($map['list'])){
                $model->listObject = $map['list'];
            }
        }
        if(isset($map['urlList'])){
            if(!empty($map['urlList'])){
                $model->urlListObject = [];
                $n = 0;
                foreach($map['urlList'] as $item) {
                    $model->urlListObject[$n++] = null !== $item ? urlListObject::fromMap($item) : $item;
                }
            }
        }
        return $model;
    }
    /**
     * @var string
     */
    public $test;

    /**
     * @var float
     */
    public $empty;

    /**
     * @var Stream
     */
    public $bodyObject;

    /**
     * @var Stream[]
     */
    public $listObject;

    public $urlListObject;

}

class urlListObject extends Model {
    protected $_name = [
        'urlObject' => 'url',
    ];
    public function validate() {}
    public function toMap() {
        $res = [];
        if (null !== $this->urlObject) {
            $res['url'] = $this->urlObject;
        }
        return $res;
    }
    /**
     * @param array $map
     * @return urlListObject
     */
    public static function fromMap($map = []) {
        $model = new self();
        if(isset($map['url'])){
            $model->urlObject = $map['url'];
        }
        return $model;
    }
    /**
     * @var Stream
     */
    public $urlObject;

}

