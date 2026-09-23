<?php
declare(strict_types=1);

namespace app\model\user\concern;

use crmeb\plugin\ModuleStateChecker;
use think\facade\Db;

/**
 * 分销财务表兼容路由。
 *
 * 分销插件启用且自有表存在时，原有核心服务透明读写插件表；插件未安装、
 * 未启用或升级尚未执行时继续使用旧表，避免核心层直接依赖 modules 类。
 */
trait UsesDistributionOwnedTable
{
    /** @var array<string, bool> */
    private static array $distributionTableAvailable = [];

    protected function useDistributionOwnedTable(string $table): void
    {
        if (!ModuleStateChecker::isActive('distribution')) {
            return;
        }

        if (!array_key_exists($table, self::$distributionTableAvailable)) {
            try {
                Db::name($table)->limit(1)->select();
                self::$distributionTableAvailable[$table] = true;
            } catch (\Throwable $e) {
                self::$distributionTableAvailable[$table] = false;
            }
        }

        if (self::$distributionTableAvailable[$table]) {
            $this->setOption('name', $table);
        }
    }
}
