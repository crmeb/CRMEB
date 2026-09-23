<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
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
    protected $name = 'system_crontab';

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