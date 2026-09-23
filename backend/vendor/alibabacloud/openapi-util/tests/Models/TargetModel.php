<?php

// This file is auto-generated, don't edit it. Thanks.
namespace AlibabaCloud\OpenApiUtil\Tests\Models;

use AlibabaCloud\Tea\Model;
use GuzzleHttp\Psr7\Stream;

class TargetModel extends Model {
    protected $_name = [
        'test' => 'Test',
        'empty' => 'empty',
        'body' => 'body',
        'list' => 'list',
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
        if (null !== $this->body) {
            $res['body'] = $this->body;
        }
        if (null !== $this->list) {
            $res['list'] = $this->list;
        }
        if (null !== $this->urlList) {
            $res['urlList'] = [];
            if(null !== $this->urlList && is_array($this->urlList)){
                $n = 0;
                foreach($this->urlList as $item){
                    $res['urlList'][$n++] = null !== $item ? $item->toMap() : $item;
                }
            }
        }
        return $res;
    }
    /**
     * @param array $map
     * @return TargetModel
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
            $model->body = $map['body'];
        }
        if(isset($map['list'])){
            if(!empty($map['list'])){
                $model->list = $map['list'];
            }
        }
        if(isset($map['urlList'])){
            if(!empty($map['urlList'])){
                $model->urlList = [];
                $n = 0;
                foreach($map['urlList'] as $item) {
                    $model->urlList[$n++] = null !== $item ? urlList::fromMap($item) : $item;
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
    public $body;

    /**
     * @var string[]
     */
    public $list;

    public $urlList;

}

class urlList extends Model {
    protected $_name = [
        'url' => 'url',
    ];
    public function validate() {}
    public function toMap() {
        $res = [];
        if (null !== $this->url) {
            $res['url'] = $this->url;
        }
        return $res;
    }
    /**
     * @param array $map
     * @return urlList
     */
    public static function fromMap($map = []) {
        $model = new self();
        if(isset($map['url'])){
            $model->url = $map['url'];
        }
        return $model;
    }
    /**
     * @var string
     */
    public $url;

}