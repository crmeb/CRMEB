<?php

namespace app\admin\model\ump;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class StoreSeckillTime extends BaseModel
{
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_seckill_time';

    use ModelTrait;

    /**
     * 秒杀添加时间段ID
     * @param $id
     * @param $time
     * @throws \Exception
     */
    public static function saveSeckillTime($id, $time)
    {
        self::where('seckill_id', $id)->delete();
        $data = [];
        foreach ($time as $k => $v) {
            $data[$k]['seckill_id'] = $id;
            $data[$k]['time_id'] = $v;
        }
        self::insertAll($data);
    }

    /**
     * 修改秒杀获取时间ID数组
     * @param $id
     * @return array
     */
    public static function getSeckillTime($id)
    {
        return self::where('seckill_id', $id)->column('time_id');
    }

    /**
     * 获取时间段内秒杀活动ID数组
     * @param $time_id
     * @return array
     */
    public static function getSeckillId($time_id)
    {
        return self::where('time_id', $time_id)->column('seckill_id');
    }

}