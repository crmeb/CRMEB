<?php
/**
 * PHP表单生成器
 *
 * @package  FormBuilder
 * @author   xaboy <xaboy2005@qq.com>
 * @version  2.0
 * @license  MIT
 * @link     https://github.com/xaboy/form-builder
 * @document http://php.form-create.com
 */

namespace App;

use FormBuilder\Factory\Elm;

$api = '/save.php';
$form = Elm::createForm($api);
$rules = [
    Elm::input('svip_name', '会员名：')->required(),
    Elm::radio('svip_type', '会员类别：', '2')
        ->setOptions([
            ['value' => '1', 'label' => '试用期',],
            ['value' => '2', 'label' => '有限期',],
            ['value' => '3', 'label' => '永久期',],
        ])->control([
            [
                'value' => '1',
                'rule' => [
                    Elm::number('svip_number', '有效期（天）：')->required()->min(0),
                ]
            ],
            [
                'value' =>'2',
                'rule' => [
                    Elm::number('svip_number', '有效期（天）：')->required()->min(0),
                ]
            ],
            [
                'value' => '3',
                'rule' => [
                    Elm::input('svip_number1', '有效期（天）：','永久期')->disabled(true)->placeholder('请输入有效期'),
                    Elm::input('svip_number', '有效期（天）：','永久期')->hiddenStatus(true)->placeholder('请输入有效期'),
                ]
            ],
        ])->appendRule('suffix', [
            'type' => 'div',
            'style' => ['color' => '#999999'],
            'domProps' => [
                'innerHTML' =>'试用期每个用户只能购买一次，购买过付费会员之后将不在展示，不可购买',
            ]
        ]),
    Elm::number('cost_price', '原价：')->required(),
    Elm::number('price', '优惠价：')->required(),
    Elm::number('sort', '排序：'),
    Elm::switches('status', '是否显示：')->activeValue(1)->inactiveValue(0)->inactiveText('关')->activeText('开'),
];
$form->setRule($rules);
$form->setTitle('demo');

$rule = $form->formRule();
$action = $form->getAction();
$method = $form->getMethod();
$title = $form->getTitle();
$view = $form->view();

var_dump(compact('rule', 'action', 'method', 'title', 'view', 'api'));
