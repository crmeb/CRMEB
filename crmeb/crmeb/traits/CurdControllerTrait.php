<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\traits;

use crmeb\utils\Json;

trait CurdControllerTrait
{
    /**
     * 错误信息
     * @var string
     */
    protected $errorInfo;

    /**保存数据库
     * @param $id
     * @param $field
     */
    public function change_field($id, $field)
    {
        if (!isset($this->bindModel)) return exception('方法不存在!');
        if (!class_exists($this->bindModel)) return Json::fail('操作Model不存在!');
        $model = new $this->bindModel;
        $pk = $model->getPk();
        if (strtolower($pk) == strtolower($field)) return Json::fail('主键不允许修改!');
        $data = $model->where($pk, $id)->find();
        if (!$data) JsonService::fail('记录不存在!');
        $value = app('request')->post($field);
        if ($value === null) return Json::fail('请提交需要编辑的数据!');
        $data->$field = $value;
        return false !== $data->save() ? Json::successful('编辑成功!') : Json::fail('编辑失败!');

    }

    /**
     * 修改数据信息
     * @param $id
     * @param array|null $updateData
     * @return bool
     */
    public function model_save($id, array $updateData = null)
    {
        if (!class_exists($this->bindModel)) return $this->setErrorInfo('操作Model不存在!');
        $model = new $this->bindModel;
        $pk = $model->getPk();
        if (!($modelData = $model->where($pk, $id)->find())) return $this->setErrorInfo('修改的信息不存在');
        $data = $updateData ?: app('request')->post();
        if ($data === null || !is_array($data)) return $this->setErrorInfo('请提交需要修改的数据');
        $dataKey = array_keys($data);
        if (in_array($pk, $dataKey)) return $this->setErrorInfo('主键不允许修改');
        foreach ($data as $key => $value) {
            $modelData->{$key} = $value;
        }
        return $modelData->save() ? true : $this->setErrorInfo('保存失败');
    }

    /**
     * 设置错误信息
     * @param $errorInfo
     * @return bool
     */
    public function setErrorInfo($errorInfo)
    {
        $this->errorInfo = $errorInfo;
        return false;
    }

    /**
     * 获取错误信息
     * @param string|null $msgError
     * @return string
     */
    public function getErrorInfo(string $msgError = null)
    {
        $errorInfo = $this->errorInfo ?: $msgError;
        $this->errorInfo = null;
        return $errorInfo;
    }
}
