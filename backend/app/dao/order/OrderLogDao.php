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

namespace app\dao\order;

use crmeb\basic\BaseDao;
use app\model\order\OrderLog;

/**
 * 订单状态日志DAO
 * Class OrderLogDao
 * @package app\dao\order
 */
class OrderLogDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return OrderLog::class;
    }

    /**
     * 获取订单日志列表（按时间正序）
     * @param array $where 查询条件
     * @return array 返回日志记录数组
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getLogList(array $where): array
    {
        return $this->search($where)->order('add_time asc')->select()->toArray();
    }
}
