<?php
namespace  app\core\model\routine;

use basic\ModelBasic;
use traits\ModelTrait;

/**
 * 表单ID表
 * Class RoutineFormId
 * @package app\core\model\routine
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
    public static function getFormIdOne($uid = 0,$isArray=false){
        $formId = self::where('status',1)->where('stop_time','GT',time())->where('uid',$uid)->order('id asc')->find();
        if($isArray) return $formId;
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

    /*
     * 创建formid
     * @param string $formId
     * @param int $uid
     * @return array
     * */
    public static function SetFormId($formId,$uid)
    {
        if(!strlen(trim($formId)) || $formId == 'the formId is a mock one') return false;
        $data['form_id'] = $formId;
        $data['uid'] = $uid;
        $data['status'] = 1;
        $data['stop_time'] = bcadd(time(),bcmul(6,86400,0),0);
        return self::set($data);
    }
}