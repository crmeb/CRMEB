<?php
namespace  app\admin\model\routine;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * 表单ID表
 * Class RoutineFormId
 * @package app\admin\model\routine
 */
class RoutineFormId extends BaseModel {

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
     * 删除已失效的formID
     * @return int
     */
    public static function delStatusInvalid(){
        return self::where('status',2)->where('stop_time','<',time())->delete();
    }

    /**
     * 获取一个可以使用的formId
     * @return bool|mixed
     */
    public static function getFormIdOne($uid = 0){
        $formId = self::where('status',1)->where('stop_time','>',time())->where('uid',$uid)->order('id asc')->find();
        if($formId) return $formId['form_id'];
        else return false;
    }

    /**
     * 修改一个FormID为已使用
     * @param string $formId
     * @return $this|bool
     */
    public static function delFormIdOne($formId = ''){
        if($formId == '') return true;
        return self::where('form_id',$formId)->update(['status'=>2]);
    }
}