<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/23
 */

namespace service;

use FormBuilder\Form;
use think\Url;

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
                $js = 'parent.$(".J_iframe:visible")[0].contentWindow.location.reload();$r.btn.disabled(false);$r.btn.finish();';//提交成功父级页面刷新继续添加
                break;
            case 4:
                $js = '$r.btn.disabled(false);$r.btn.finish();';//提交成功不能再提交
                break;
            case 5:
                $js = '$r.btn.disabled(false);$r.btn.finish();setTimeout(function(){parent.layer.close(parent.layer.getFrameIndex(window.name));},2000);';//父级不刷新 不能再提交 关闭弹窗
                break;
            case 6:
                $js = 'setTimeout(function(){window.location.reload(),2000});';//父级不刷新 当前窗口刷新
                break;
            case 7:
                $js = 'parent.$(".J_iframe:visible")[0].contentWindow.location.reload();$r.btn.disabled(false);$r.btn.finish();';//父级刷新 提交成功不能再提交
                break;
            default:
                $js = $jscallback;
                break;
        }
        $form->setSuccessScript($js);//提交成功执行js
        return $form;
    }

    /** 选择多图片
     * @param string $title 表单名称
     * @param string $field 表单字段名称
     * @param array $value 表单值
     * @method maxLength(int $length) value的最大数量, 默认无限制
     * @method icon(String $icon) 打开弹出框的按钮图标
     * @method height(String $height) 弹出框高度
     * @method width(String $width) 弹出框宽度
     * @method spin(Boolean $bool) 是否显示加载动画, 默认为 true
     * @method frameTitle(String $title) 弹出框标题
     * @method handleIcon(Boolean $bool) 操作按钮的图标, 设置为false将不显示, 设置为true为默认的预览图标, 类型为file时默认为false, image类型默认为true
     * @method allowRemove(Boolean $bool) 是否可删除, 设置为false是不显示删除按钮
     */
    public static function formFrameImages($field,$title,$value = [],$icon = 'images',$frameTitle = '图库',$maxLength=5,$width = '945px',$height = '500px',$spin = true,$handleIcon = true,$allowRemove = true){
        $url = Url::build('admin/widget.images/index',array('fodder'=>$field));
        return Form::frameImages($field,$title,$url,$value)->maxLength($maxLength)->icon($icon)->width($width)->height($height)->frameTitle($frameTitle)->spin($spin)->handleIcon($handleIcon)->allowRemove($allowRemove);

    }
    /** 选择单图片
     * @param string $title 表单名称
     * @param string $field 表单字段名称
     * @param array $value 表单值
     * @method icon(String $icon) 打开弹出框的按钮图标
     * @method height(String $height) 弹出框高度
     * @method width(String $width) 弹出框宽度
     * @method spin(Boolean $bool) 是否显示加载动画, 默认为 true
     * @method frameTitle(String $title) 弹出框标题
     * @method handleIcon(Boolean $bool) 操作按钮的图标, 设置为false将不显示, 设置为true为默认的预览图标, 类型为file时默认为false, image类型默认为true
     * @method allowRemove(Boolean $bool) 是否可删除, 设置为false是不显示删除按钮
     */
    public static function formFrameImageOne($field,$title,$value = '',$icon = 'images',$frameTitle = '图库',$width = '945px',$height = '500px',$spin = true,$handleIcon = true,$allowRemove = true){
        $url = Url::build('admin/widget.images/index',array('fodder'=>$field));
        return Form::frameImageOne($field,$title,$url,$value)->icon($icon)->width($width)->height($height)->frameTitle($frameTitle)->spin($spin)->handleIcon($handleIcon)->allowRemove($allowRemove);

    }

}