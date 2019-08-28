<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/23
 */

namespace crmeb\services;

use FormBuilder\Form;

class FormBuilder extends Form
{

    /**
     * 快速创建POST提交表单
     * @param $title
     * @param array $field
     * @param $url
     * @param $jscallback $f.submitStatus({loading: false}); 成功按钮状态false
     * 1 父级刷新 不能再提交
     * 2 父级刷新关闭弹框 不能再提交 成功关闭
     * 3 父页面刷新可以重复添加 可以再次提交
     * 4 父级不刷新 不能再提交
     * 5 父级不刷新 不能再提交 关闭弹窗
     * 6 父级不刷新 当前窗口刷新
     * str 自定义
     * @return $this
     */
    public static function make_post_form($title,array $field,$url,$jscallback = 2){
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
            case 3:
                $js = 'parent.$(".J_iframe:visible")[0].contentWindow.location.reload();$r.btn.disabled(false);$f.btn.loading(false)();';//提交成功父级页面刷新继续添加
                break;
            case 4:
                $js = '$r.btn.disabled(false);$f.btn.loading(false)();';//提交成功不能再提交
                break;
            case 5:
                $js = '$r.btn.disabled(false);$f.btn.loading(false)();setTimeout(function(){parent.layer.close(parent.layer.getFrameIndex(window.name));},2000);';//父级不刷新 不能再提交 关闭弹窗
                break;
            case 6:
                $js = 'setTimeout(function(){window.location.reload(),2000});';//父级不刷新 当前窗口刷新
                break;
            case 7:
                $js = 'parent.$(".J_iframe:visible")[0].contentWindow.location.reload();$r.btn.disabled(false);$f.btn.loading(false)();';//父级刷新 提交成功不能再提交
                break;
            default:
                $js = $jscallback;
                break;
        }
        $form->setSuccessScript($js);//提交成功执行js
        return $form;
    }

}