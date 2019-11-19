<?php

namespace app\models\routine;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * TODO 表单ID表
 * Class RoutineFormId
 * @package app\models\routine
 */
class RoutineFormId extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'routine_form_id';

    use ModelTrait;

    /**
     * TODO 删除已失效的formID
     * @return bool
     * @throws \Exception
     */
    public static function delStatusInvalid()
    {
        return self::where('status', 2)->where('stop_time', '<', time())->delete();
    }

    /**
     * TODO
     * @param int $uid
     * @param bool $isArray
     * @return array|bool|mixed|null|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getFormIdOne($uid = 0, $isArray = false)
    {
        $formId = self::where('status', 1)->where('stop_time', '>', time())->where('uid', $uid)->order('id asc')->find();
        if ($isArray) return $formId;
        if ($formId) return $formId['form_id'];
        else return false;
    }

    /**
     * 修改一个FormID为已使用
     * @param string $formId
     * @return $this|bool
     */
    public static function delFormIdOne($formId = '')
    {
        if ($formId == '') return true;
        return self::where('form_id', $formId)->update(['status' => 2]);
    }

    /**
     * TODO 创建formid
     * @param $formId
     * @param $uid
     * @return RoutineFormId|bool|\think\Model
     */
    public static function SetFormId($formId, $uid)
    {
        if (!strlen(trim($formId)) || $formId == 'the formId is a mock one') return false;
        $data['form_id'] = $formId;
        $data['uid'] = $uid;
        $data['status'] = 1;
        $data['stop_time'] = bcadd(time(), bcmul(6, 86400, 0), 0);
        return self::create($data);
    }
}