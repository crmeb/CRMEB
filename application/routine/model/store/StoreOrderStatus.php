<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/28
 */

namespace app\routine\model\store;


use basic\ModelBasic;
use traits\ModelTrait;

class StoreOrderStatus extends ModelBasic
{
    use ModelTrait;

    public static function status($oid,$change_type,$change_message,$change_time = null)
    {
        if($change_time == null) $change_time = time();
        return self::set(compact('oid','change_type','change_message','change_time'));
    }

    public static function getTime($oid,$change_type)
    {
        return self::where('oid',$oid)->where('change_type',$change_type)->value('change_time');
    }

}