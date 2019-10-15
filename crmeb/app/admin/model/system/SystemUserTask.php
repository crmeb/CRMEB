<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/13
 */

namespace app\admin\model\system;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * 设置等级任务 model
 * Class SystemUserTask
 * @package app\admin\model\system
 */
class SystemUserTask extends BaseModel
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
    protected $name = 'system_user_task';

    use ModelTrait;

    /**
     * 任务类型
     * type 记录在数据库中用来区分任务
     * name 任务名 (任务名中的{$num}会自动替换成设置的数字 + 单位)
     * max_number 最大设定数值 0为不限定
     * min_number 最小设定数值
     * unit 单位
     * @var array
     */
    protected static $TaskType=[
        [
            'type'=>'SatisfactionIntegral',
            'name'=>'满足积分{$num}',
            'real_name'=>'积分数',
            'max_number'=>0,
            'min_number'=>0,
            'unit'=>'分'
        ],
        [
            'type'=>'ConsumptionAmount',
            'name'=>'消费满{$num}',
            'real_name'=>'消费金额',
            'max_number'=>0,
            'min_number'=>0,
            'unit'=>'元'
        ],
        [
            'type'=>'ConsumptionFrequency',
            'name'=>'消费{$num}',
            'real_name'=>'消费次数',
            'max_number'=>0,
            'min_number'=>0,
            'unit'=>'次'
        ],
        [
            'type'=>'CumulativeAttendance',
            'name'=>'累计签到{$num}',
            'real_name'=>'累计签到',
            'max_number'=>365,
            'min_number'=>1,
            'unit'=>'天'
        ],
        [
            'type'=>'SharingTimes',
            'name'=>'分享给朋友{$num}',
            'real_name'=>'分享给朋友',
            'max_number'=>1000,
            'min_number'=>1,
            'unit'=>'次'
        ],
        [
            'type'=>'InviteGoodFriends',
            'name'=>'邀请好友{$num}成为下线',
            'real_name'=>'邀请好友成为下线',
            'max_number'=>1000,
            'min_number'=>1,
            'unit'=>'人'
        ],
        [
            'type'=>'InviteGoodFriendsLevel',
            'name'=>'邀请好友{$num}成为会员',
            'real_name'=>'邀请好友成为会员',
            'max_number'=>1000,
            'min_number'=>1,
            'unit'=>'人'
        ],
    ];

    public function profile()
    {
        return $this->hasOne('SystemUserLevel','level_id','id')->field('name');
    }

    public static function getTaskTypeAll()
    {
        return self::$TaskType;
    }

    /**
     * 获取某个任务
     * @param $type
     * @return mixed
     */
    public static function getTaskType($type)
    {
        foreach (self::$TaskType as $item){
            if($item['type']==$type) return $item;
        }
    }

    /**
     * 设置任务名
     * @param $type
     * @param $num
     * @return mixed
     */
    public static function setTaskName($type,$num)
    {
        $systemType=self::getTaskType($type);
        return str_replace('{$num}',$num.$systemType['unit'],$systemType['name']);
    }

    /**
     * 获取等级会员任务列表
     * @param $level_id
     * @param $page
     * @param $limit
     * @return array
     */
    public static function getTashList($level_id,$page,$limit)
    {
        $data=self::where('level_id',$level_id)->order('sort desc,add_time desc')->page($page,$limit)->select();
        $data=count($data) ? $data->toArray() : [];
        foreach ($data as &$item){
            $item['level_name']=SystemUserLevel::where('id',$item['level_id'])->value('name');
        }
        $count=self::where('level_id',$level_id)->count();
        return compact('data','count');
    }

}