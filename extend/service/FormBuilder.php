<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/23
 */

namespace service;

use FormBuilder\Form;

class FormBuilder extends Form
{

    /**
     * 快速创建POST提交表单
     * @param $title
     * @param array $field
     * @param $url
     * @param $jscallback null 不执行 1 父级刷新 2 父级刷新关闭弹框 str 自定义
     * @return $this
     */
    public static function make_post_form($title,array $field,$url,$jscallback = 1){
        $form = Form::create($url);//提交地址
        $form->setMethod('POST');//提交方式
        $form->components($field);//表单字段
        $form->setTitle($title);//表单标题
        $js = '';//提交成功不执行任何动作
        switch ($jscallback){
            case 1:
                $js = 'parent.$(".J_iframe:visible")[0].contentWindow.location.reload();';//提交成功父级页面刷新
                break;
            case 2:
                $js = 'parent.$(".J_iframe:visible")[0].contentWindow.location.reload(); setTimeout(function(){parent.layer.close(parent.layer.getFrameIndex(window.name));},2000);';//提交成功父级页面刷新并关闭当前页面
                break;
            default:
                $js = $jscallback;
                break;
        }
        $form->setSuccessScript($js);//提交成功执行js
        return $form;
    }

}