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
declare (strict_types=1);

namespace app\dao\system;

use crmeb\basic\BaseDao;
use app\model\system\MessageSystem;

/**
 * 站内信旧 DAO（已废弃，eb_message_system 表已删除）
 * 兼容层 MessageSystemServices 已委托给 NoticeServices，不再直接操作此 DAO
 * @deprecated 后续完全下线兼容层时删除
 * Class MessageSystemDao
 * @package app\dao\system
 */
class MessageSystemDao extends BaseDao
{

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return MessageSystem::class;
    }

    /**
     * @param array $where
     * @param string $field
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getMessageList(array $where, string $field = '*', int $page = 0, $limit = 0)
    {
        return $this->getModel()->where($where)->field($field)->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->order('add_time desc')->select()->toArray();
    }
}
