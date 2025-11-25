<?php

namespace app\model\system\crontab;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class SystemCrontab extends BaseModel
{
    use ModelTrait;

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'system_timer';

    /**
     * 不自动更新update_time
     * @var bool
     */
    protected $updateTime = false;

    /**
     * 是否自定义定时任务搜索器
     * @param $query
     * @param $value
     * @param $data
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/6/6
     */
    public function searchCustomAttr($query, $value, $data)
    {
        if ($value !== '') {
            if ($value == 0) {
                $query->where('mark', '<>', 'customTimer');
            } else {
                $query->where('mark', 'customTimer');
            }
        }
    }
}