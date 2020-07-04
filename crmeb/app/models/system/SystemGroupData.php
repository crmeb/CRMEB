<?php

namespace app\models\system;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class SystemGroupData extends BaseModel
{
    use ModelTrait;

    /**
     * 根据id获取当前记录中的数据
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getDateValue($id)
    {
        $value = self::alias('a')->where(array("id" => $id))->find();
        if (!$value) {
            return false;
        }
        $data["id"] = $value["id"];
        $fields = json_decode($value["value"], true);
        foreach ($fields as $index => $field) {
            $data[$index] = $field["value"];
        }
        return $data;
    }
}