<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/15
 */
namespace traits;

use service\JsonService;
use think\Request;

trait CurdControllerTrait
{
    public function change_field($id,$field)
    {
        if(!isset($this->bindModel)) return exception('方法不存在!');
        if(!class_exists($this->bindModel)) return JsonService::fail('操作Model不存在!');
        $model = new $this->bindModel;
        $pk = $model->getPk();
        if(strtolower($pk) == strtolower($field)) return JsonService::fail('主键不允许修改!');
        $data = $model->where($pk,$id)->find();
        if(!$data) JsonService::fail('记录不存在!');
        $value = Request::instance()->post($field);
        if($value === null) return JsonService::fail('请提交需要编辑的数据!');
        $data->$field = $value;
        return false !== $data->save() ? JsonService::successful('编辑成功!') : JsonService::fail('编辑失败!');

    }
}