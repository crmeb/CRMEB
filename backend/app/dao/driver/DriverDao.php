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

declare(strict_types=1);

namespace app\dao\driver;

use app\model\driver\Driver;
use crmeb\basic\BaseDao;

/**
 * 通用驱动数据访问层（跨类型操作 eb_driver 表）
 *
 * 与按驱动类型拆分的 XxxDriverDao 不同，本 DAO 不限定 driver_type，
 * 供需要跨驱动类型统一查询与状态管理的场景使用（如 MCP 驱动管理工具）。
 *
 * Class DriverDao
 * @package app\dao\driver
 */
class DriverDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return Driver::class;
    }

    /**
     * 获取驱动列表（可按类型筛选，已过滤未挂载驱动插件的脏数据）
     * @param string $type 驱动类型（如 sms/email/upload），空串返回全部类型
     * @return array
     */
    public function getList(string $type = ''): array
    {
        $query = $this->getModel()
            ->where('owner_plugin_id', '<>', '')
            ->order('driver_type', 'asc')
            ->order('sort', 'desc')
            ->order('id', 'asc');
        if ($type !== '') {
            $query->where('driver_type', strtolower(trim($type)));
        }
        return $query->select()->toArray();
    }

    /**
     * 按驱动类型与标识获取驱动
     * @param string $type 驱动类型（如 sms/email/upload）
     * @param string $key 驱动标识（driver_key）
     * @return Driver|null 不存在时返回 null
     */
    public function getByTypeAndKey(string $type, string $key): ?Driver
    {
        return $this->getModel()
            ->where('driver_type', strtolower(trim($type)))
            ->where('driver_key', $key)
            ->find();
    }

    /**
     * 禁用同类型的其他驱动（启用互斥：启用一个前先禁用同类型其余驱动）
     * @param string $type 驱动类型
     * @param int $id 要保留（即将启用）的驱动 ID
     * @return int 受影响行数
     */
    public function disableOthers(string $type, int $id): int
    {
        return $this->getModel()
            ->where('driver_type', strtolower(trim($type)))
            ->where('id', '<>', $id)
            ->update(['status' => 0]);
    }
}
