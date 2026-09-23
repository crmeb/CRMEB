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

namespace app\services\system;

use think\facade\Log;
use think\facade\Cache;
use crmeb\basic\BaseService;
use app\services\user\UserBillServices;
use app\services\user\UserMoneyServices;
use app\services\user\UserBrokerageServices;
use app\services\user\UserBrokerageFrozenServices;
use app\services\activity\coupon\StoreCouponIssueServices;
use app\services\activity\coupon\StoreCouponProductServices;

/**
 * 数据迁移服务
 * 用于跨版本升级时处理历史数据迁移
 * Class DataMigrationServices
 * @package app\services\system
 */
class DataMigrationServices extends BaseService
{
    /**
     * 迁移状态缓存前缀
     */
    const MIGRATION_STATUS_PREFIX = 'data_migration_';
    
    /**
     * 默认分页大小
     */
    const DEFAULT_LIMIT = 100;

    /**
     * 检查迁移是否已完成
     * @param string $name 迁移名称
     * @return bool
     */
    public function isMigrationCompleted(string $name): bool
    {
        return Cache::get(self::MIGRATION_STATUS_PREFIX . $name) === 'completed';
    }

    /**
     * 标记迁移已完成
     * @param string $name 迁移名称
     * @return void
     */
    public function markMigrationCompleted(string $name): void
    {
        Cache::set(self::MIGRATION_STATUS_PREFIX . $name, 'completed', 86400 * 30);
    }

    /**
     * 获取迁移进度
     * @param string $name 迁移名称
     * @return array
     */
    public function getMigrationProgress(string $name): array
    {
        $page = Cache::get(self::MIGRATION_STATUS_PREFIX . $name . '_page', 1);
        $total = Cache::get(self::MIGRATION_STATUS_PREFIX . $name . '_total', 0);
        $processed = Cache::get(self::MIGRATION_STATUS_PREFIX . $name . '_processed', 0);
        
        return [
            'page' => $page,
            'total' => $total,
            'processed' => $processed
        ];
    }

    /**
     * 更新迁移进度
     * @param string $name 迁移名称
     * @param int $page 当前页
     * @param int $processed 已处理数量
     * @return void
     */
    protected function updateMigrationProgress(string $name, int $page, int $processed): void
    {
        Cache::set(self::MIGRATION_STATUS_PREFIX . $name . '_page', $page, 86400);
        Cache::set(self::MIGRATION_STATUS_PREFIX . $name . '_processed', $processed, 86400);
    }

    /**
     * 执行数据迁移处理器
     * @param array $handler 处理器配置
     * @return array ['success' => bool, 'message' => string, 'completed' => bool]
     */
    public function executeHandler(array $handler): array
    {
        $name = $handler['name'] ?? '';
        $method = $handler['handler'] ?? '';
        $limit = $handler['limit'] ?? self::DEFAULT_LIMIT;
        $title = $handler['title'] ?? $name;
        
        if (!$name || !$method) {
            return ['success' => false, 'message' => '迁移配置错误', 'completed' => false];
        }
        
        // 检查是否已完成
        if ($this->isMigrationCompleted($name)) {
            return ['success' => true, 'message' => $title . ' 已完成', 'completed' => true, 'skipped' => true];
        }
        
        // 检查方法是否存在
        if (!method_exists($this, $method)) {
            return ['success' => false, 'message' => '迁移方法不存在: ' . $method, 'completed' => false];
        }
        
        try {
            // 获取当前进度
            $progress = $this->getMigrationProgress($name);
            $page = $progress['page'];
            
            // 执行迁移方法
            $result = $this->$method($page, $limit);
            
            if ($result['completed']) {
                $this->markMigrationCompleted($name);
                return [
                    'success' => true,
                    'message' => $title . ' 迁移完成',
                    'completed' => true,
                    'processed' => $result['processed'] ?? 0
                ];
            } else {
                // 更新进度
                $this->updateMigrationProgress($name, $page + 1, ($progress['processed'] ?? 0) + ($result['count'] ?? 0));
                return [
                    'success' => true,
                    'message' => $title . ' 处理中 (第' . $page . '页)',
                    'completed' => false,
                    'processed' => $result['count'] ?? 0
                ];
            }
        } catch (\Exception $e) {
            Log::error('数据迁移失败: ' . $e->getMessage(), ['handler' => $handler]);
            return ['success' => false, 'message' => $title . ' 迁移失败: ' . $e->getMessage(), 'completed' => false];
        }
    }

    /**
     * 执行所有数据迁移处理器（循环直到全部完成）
     * @param array $handlers 处理器列表
     * @return array
     */
    public function executeAllHandlers(array $handlers): array
    {
        $results = [];
        $allCompleted = true;
        
        foreach ($handlers as $handler) {
            $name = $handler['name'] ?? '';
            
            // 循环执行直到完成
            while (!$this->isMigrationCompleted($name)) {
                $result = $this->executeHandler($handler);
                
                if (!$result['success']) {
                    $results[$name] = $result;
                    $allCompleted = false;
                    break; // 失败则跳过此处理器
                }
                
                if ($result['completed']) {
                    $results[$name] = $result;
                    break;
                }
            }
            
            if (!isset($results[$name])) {
                $results[$name] = ['success' => true, 'message' => ($handler['title'] ?? $name) . ' 已跳过', 'completed' => true, 'skipped' => true];
            }
        }
        
        return [
            'success' => $allCompleted,
            'results' => $results
        ];
    }

    // ==================== 数据迁移方法 ====================

    /**
     * 处理历史余额数据
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function handleMoney(int $page = 1, int $limit = 100): array
    {
        /** @var UserBillServices $userBillServices */
        $userBillServices = app()->make(UserBillServices::class);
        $where = ['category' => 'now_money', 'type' => ['pay_product', 'pay_product_refund', 'system_add', 'system_sub', 'recharge', 'lottery_use', 'lottery_add']];
        $list = $userBillServices->getList($where, '*', $page, $limit, [], 'id asc');
        
        if (empty($list)) {
            return ['completed' => true, 'processed' => 0];
        }
        
        $allData = [];
        foreach ($list as $item) {
            $allData[] = [
                'uid' => $item['uid'],
                'link_id' => $item['link_id'],
                'pm' => $item['pm'],
                'title' => $item['title'],
                'type' => $item['type'],
                'number' => $item['number'],
                'balance' => $item['balance'],
                'mark' => $item['mark'],
                'add_time' => strtotime($item['add_time']),
            ];
        }
        
        if ($allData) {
            /** @var UserMoneyServices $userMoneyServices */
            $userMoneyServices = app()->make(UserMoneyServices::class);
            $userMoneyServices->saveAll($allData);
        }
        
        Log::notice(['type' => 'data_migration', 'handler' => 'handleMoney', 'page' => $page, 'count' => count($list)]);
        
        return ['completed' => false, 'count' => count($list)];
    }

    /**
     * 处理历史佣金数据
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function handleBrokerage(int $page = 1, int $limit = 100): array
    {
        /** @var UserBillServices $userBillServices */
        $userBillServices = app()->make(UserBillServices::class);
        $where = ['category' => ['', 'now_money'], 'type' => ['brokerage', 'brokerage_user', 'extract', 'refund', 'extract_fail']];
        $list = $userBillServices->getList($where, '*', $page, $limit, [], 'id asc');
        
        if (empty($list)) {
            return ['completed' => true, 'processed' => 0];
        }
        
        $allData = [];
        /** @var UserBrokerageFrozenServices $brokerageFrozenServices */
        $brokerageFrozenServices = app()->make(UserBrokerageFrozenServices::class);
        $frozenList = $brokerageFrozenServices->getColumn([['uill_id', 'in', array_column($list, 'id')], ['frozen_time', '>', time()]], 'uill_id,frozen_time', 'uill_id');
        
        foreach ($list as $item) {
            if (in_array($item['type'], ['brokerage_user', 'extract', 'refund', 'extract_fail'])) {
                $type = $item['type'];
            } else {
                $type = strpos($item['mark'], '二级') !== false ? 'two_brokerage' : 'one_brokerage';
            }
            
            $allData[] = [
                'uid' => $item['uid'],
                'link_id' => $item['link_id'],
                'pm' => $item['pm'],
                'title' => $item['title'],
                'type' => $type,
                'number' => $item['number'],
                'balance' => $item['balance'],
                'mark' => $item['mark'],
                'frozen_time' => $frozenList[$item['id']]['frozen_time'] ?? 0,
                'add_time' => strtotime($item['add_time']),
            ];
        }
        
        if ($allData) {
            /** @var UserBrokerageServices $userBrokerageServices */
            $userBrokerageServices = app()->make(UserBrokerageServices::class);
            $userBrokerageServices->saveAll($allData);
        }
        
        Log::notice(['type' => 'data_migration', 'handler' => 'handleBrokerage', 'page' => $page, 'count' => count($list)]);
        
        return ['completed' => false, 'count' => count($list)];
    }

    /**
     * 更新分类券数据
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function handleCoupon(int $page = 1, int $limit = 100): array
    {
        /** @var StoreCouponIssueServices $couponIssueServices */
        $couponIssueServices = app()->make(StoreCouponIssueServices::class);
        $list = $couponIssueServices->selectList([['category_id', '>', 0]], 'id,category_id', $page, $limit)->toArray();
        
        if (empty($list)) {
            return ['completed' => true, 'processed' => 0];
        }
        
        $allData = [];
        foreach ($list as $item) {
            $allData[] = [
                'coupon_id' => $item['id'],
                'product_id' => 0,
                'category_id' => $item['category_id']
            ];
        }
        
        if ($allData) {
            /** @var StoreCouponProductServices $couponProductServices */
            $couponProductServices = app()->make(StoreCouponProductServices::class);
            $couponProductServices->saveAll($allData);
        }
        
        Log::notice(['type' => 'data_migration', 'handler' => 'handleCoupon', 'page' => $page, 'count' => count($list)]);
        
        return ['completed' => false, 'count' => count($list)];
    }
}
