<?php
namespace  app\routine\model\routine;

use basic\ModelBasic;
use traits\ModelTrait;

/**
 * 表单ID表
 * Class RoutineFormId
 * @package app\routine\model\routine
 */
class RoutineFormId extends ModelBasic {

    use ModelTrait;

    /**
     * 删除已失效的formID
     * @return int
     */
    public static function delStatusInvalid(){
        return self::where('status',2)->where('stop_time','LT',time())->delete();
    }

    /**
     * 获取一个可以使用的formId
     * @return bool|mixed
     */
    public static function getFormIdOne($uid = 0){
        $formId = self::where('status',1)->where('stop_time','GT',time())->where('uid',$uid)->order('id asc')->find();
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