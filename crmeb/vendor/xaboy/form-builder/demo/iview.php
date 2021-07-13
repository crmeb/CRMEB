<?php

namespace App;


require '../vendor/autoload.php';

use FormBuilder\Annotation\Col;
use FormBuilder\Annotation\Group;
use  FormBuilder\Factory\Iview;
use FormBuilder\Handle\ElmFormHandle;
use FormBuilder\Handle\IviewFormHandle;
use FormBuilder\FormHandle;
use FormBuilder\UI\Elm\Components\Checkbox;
use FormBuilder\UI\Elm\Components\Rate;
use FormBuilder\UI\Iview\Components\DatePicker;


class GoodsForm extends IviewFormHandle
{
    protected $action = 'save.php';
    protected $title = '测试 Handle';
    protected $fieldTitles = [
        'start_time' => '开启时间',
        'star' => '点赞'
    ];

    protected $scene = 'get';

    protected function getScene()
    {
//        $this->except = ['goods_name'];
    }

    /**
     * @Col(12)
     * @return \FormBuilder\UI\Iview\Components\Input
     */
    public function goods_name_field()
    {
        return Iview::input('goods_name', '商品名称')->required();
    }

    /**
     * @Group(className="test")
     * @Col(12)
     * @return \FormBuilder\UI\Iview\Components\Input
     */
    public function goods_info_field()
    {
        return Iview::textarea('goods_info', '商品简介');
    }

    /**
     * @Group()
     * @Col(12)
     * @return \FormBuilder\UI\Iview\Components\Switches
     */
    public function is_open_field()
    {
        return Iview::switches('is_open', '是否开启');
    }

    public function id_field()
    {
        return Iview::hidden('1', '1');
    }

    public function frame_field()
    {
        return Iview::frame('as', 'asd', 'afsdfasdf');
    }

    public function test_field()
    {
        return Iview::dateTime('aaa', 'aaa')->required();
    }

    public function row_field()
    {
        return [
            'type' => 'row',
            'children' => [
                [
                    'type' => 'input',
                    'field' => 'row',
                    'title' => 'test Row',
                    'value' => '123',
                    'col' => [
                        'span' => 12
                    ]
                ],
                Iview::input('row2', 'row2', 'asdf')->col(12)
            ],
            'native' => true
        ];
    }

    /**
     * 通过依赖注入方式生成组件
     *
     * @param DatePicker $date
     * @return DatePicker
     */
    public function start_time_field(DatePicker $date)
    {
        return $date->required()->info('asdfasdfasdfsf');
    }

    public function starField(Rate $rate)
    {
        return $rate;
    }

    protected function getFormConfig()
    {
        $config = Iview::config();
        $config->createResetBtn()->show(true);

        return $config;
    }

    protected function getFormData()
    {
        return [
            'goods_name' => 'goods_name123',
            'asdf' => 'asdfafd',
            'is_open' => '0',
            'goods_info' => "asdf\r\nadfa",
            'start_time' => '1999-11-11',
            'star' => 0,
            'row' => 'adsfasdfasd'
        ];
    }
}

$formHtml = (new GoodsForm())->view();
//$formHtml = (new GoodsForm())->form()->view();

echo $formHtml;