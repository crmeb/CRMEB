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

namespace app\model\system;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * 站内信旧模型（已废弃，eb_message_system 表已删除）
 * 兼容层 MessageSystemServices 已委托给 NoticeServices，不再直接操作此模型
 * @deprecated 后续完全下线兼容层时删除
 * Class MessageSystem
 * @package app\model\system
 */
class MessageSystem extends BaseModel
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
    protected $name = 'message_system';


    protected $insert = ['add_time'];

    /**
     * ID搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchIdAttr($query, $value, $data)
    {
        $query->where('id', $value);
    }
    /**
     * UID搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchUidAttr($query, $value, $data)
    {
        $query->where('uid', $value);
    }
    /**
     * Look搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchLookAttr($query, $value, $data)
    {
        $query->where('look', $value);
    }
    /**
     * del搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchIsDelAttr($query, $value, $data)
    {
        $query->where('is_del', $value);
    }

}
