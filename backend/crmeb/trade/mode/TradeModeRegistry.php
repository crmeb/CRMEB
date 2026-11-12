<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace crmeb\trade\mode;

/**
 * 交易模式注册中心
 * 管理所有已注册的交易模式（即旧称"活动"）处理器
 * 等同于旧版 ActivityRegistry，仅命名空间变更
 */
class TradeModeRegistry
{
    /**
     * 已注册的处理器类
     * @var array<string, class-string>
     */
    protected array $handlers = [];

    /**
     * 处理器实例缓存
     * @var array<string, TradeModeHandlerInterface>
     */
    protected array $instances = [];

    /**
     * 注册交易模式处理器
     * @param string $modeKey 交易模式标识
     * @param class-string $handlerClass 处理器类名
     * @return void
     */
    public function register(string $modeKey, string $handlerClass): void
    {
        $this->handlers[$modeKey] = $handlerClass;
    }

    /**
     * 获取处理器实例
     * @param string $modeKey 交易模式标识
     * @return TradeModeHandlerInterface
     */
    public function get(string $modeKey): TradeModeHandlerInterface
    {
        if (!isset($this->instances[$modeKey])) {
            if (!isset($this->handlers[$modeKey])) {
                // 未注册的交易模式统一走普通购买处理器，保证默认流程可用
                $this->instances[$modeKey] = new DefaultTradeModeHandler();
            } else {
                $class = $this->handlers[$modeKey];
                $this->instances[$modeKey] = new $class();
            }
        }
        return $this->instances[$modeKey];
    }

    /**
     * 是否已注册
     * @param string $modeKey 交易模式标识
     * @return bool
     */
    public function has(string $modeKey): bool
    {
        return isset($this->handlers[$modeKey]);
    }

    /**
     * 获取所有已注册交易模式标识
     * @return array<string>
     */
    public function all(): array
    {
        return array_keys($this->handlers);
    }

    /**
     * 获取所有已注册交易模式的key和中文标签
     * 标签由各 Handler 的 getLabel() 方法动态提供
     * @return array 返回 [['key' => 'seckill', 'label' => '秒杀'], ...]
     */
    public function allInfo(): array
    {
        $list = [];
        foreach ($this->handlers as $modeKey => $class) {
            $handler = $this->get($modeKey);
            $list[] = [
                'key'   => $modeKey,
                'label' => $handler->getLabel(),
            ];
        }
        return $list;
    }

    /**
     * 获取所有营销类交易模式标识（可参与活动的模式，不含普通购买等基础交易模式）
     * 由各 Handler 的 isMarketingActivity() 声明，新增营销活动无需改动调用方
     * @return array<string>
     */
    public function getMarketingKeys(): array
    {
        $keys = [];
        foreach ($this->allInstances() as $modeKey => $handler) {
            if ($handler->isMarketingActivity()) {
                $keys[] = $modeKey;
            }
        }
        return $keys;
    }

    /**
     * 根据旧版数字 type 协议获取交易模式处理器
     * 兼容购物车/下单接口的旧版数字交易类型（0=普通、1=秒杀、2=砍价、3=拼团、6=预售）
     * 遍历所有已注册处理器，匹配其 getLegacyType() 声明，未匹配时降级为普通购买处理器
     * @param int $type 旧版数字交易类型
     * @return TradeModeHandlerInterface 交易模式处理器
     */
    public function getByLegacyType(int $type): TradeModeHandlerInterface
    {
        foreach ($this->allInstances() as $handler) {
            if ($handler->getLegacyType() === $type) {
                return $handler;
            }
        }
        // 未匹配到旧版数字类型时，降级为普通购买处理器，保证默认流程可用
        return $this->get('normal');
    }

    /**
     * 获取所有处理器实例
     * @return array<string, TradeModeHandlerInterface>
     */
    public function allInstances(): array
    {
        $instances = [];
        foreach (array_keys($this->handlers) as $modeKey) {
            $instances[$modeKey] = $this->get($modeKey);
        }
        return $instances;
    }

    /**
     * 检查商品是否参与任意交易模式
     * @param array $productIds 商品ID数组
     * @return array 返回商品ID为key的数组，值为交易模式类型数组
     *               例如：[1 => ['seckill' => true, 'bargain' => false], ...]
     */
    public function checkProductActivities(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $result = [];
        foreach ($productIds as $productId) {
            $result[$productId] = [];
        }

        foreach ($this->allInstances() as $modeKey => $handler) {
            $existMap = $handler->getProductExist($productIds);
            foreach ($productIds as $productId) {
                $result[$productId][$modeKey] = $existMap[$productId] ?? false;
            }
        }

        return $result;
    }

    /**
     * 从购物车行解析活动类型与活动ID
     * 优先读 activity_type、activity_id。旧的立即购买快照只有 seckill_id 等字段时按字段补上。
     * @param array $item 购物车行
     * @return array{0: string, 1: int}
     */
    public function resolveCartActivity(array $item): array
    {
        $type = (string)($item['activity_type'] ?? '');
        $id = (int)($item['activity_id'] ?? 0);
        if ($type === '' || $type === 'normal' || $id <= 0) {
            $legacyIdKey = [
                'seckill' => 'seckill_id',
                'bargain' => 'bargain_id',
                'combination' => 'combination_id',
            ];
            foreach ($legacyIdKey as $mode => $key) {
                $legacyId = (int)($item[$key] ?? 0);
                if ($legacyId > 0) {
                    $type = $mode;
                    $id = $legacyId;
                    break;
                }
            }
        }
        if ($type === 'normal') {
            $type = '';
        }
        return [$type, $id];
    }

    /**
     * 取购物车里第一件活动商品
     * @param array $cartItems 购物车行
     * @return array{0: string, 1: int}
     */
    public function primaryCartActivity(array $cartItems): array
    {
        foreach ($cartItems as $item) {
            if (!is_array($item)) {
                continue;
            }
            [$type, $id] = $this->resolveCartActivity($item);
            if ($type !== '' && $id > 0) {
                return [$type, $id];
            }
        }
        return ['', 0];
    }

    /**
     * 这单购物车是否还能使用优惠
     * 任一已注册活动声明不允许优惠时，整单都不使用
     * @param array $cartItems 购物车行
     * @return bool
     */
    public function orderAllowsDiscount(array $cartItems): bool
    {
        foreach ($cartItems as $item) {
            if (!is_array($item)) {
                continue;
            }
            [$type, $id] = $this->resolveCartActivity($item);
            if ($type !== '' && $id > 0 && $this->has($type) && !$this->get($type)->allowsOrderDiscount()) {
                return false;
            }
        }
        return true;
    }

    /**
     * 从订单和活动快照解析活动类型与活动ID
     * 优先读订单列，再读 extra_info.activity_info。旧单只有 seckill_id 等快照字段时按类型补活动ID。
     * @param array $order 中台订单
     * @return array{0: string, 1: int} [活动类型, 活动ID]，普通订单类型为空串
     */
    public function resolveOrderActivity(array $order): array
    {
        $type = (string)($order['activity_type'] ?? '');
        $id = (int)($order['activity_id'] ?? 0);
        $extra = $order['extra_info'] ?? [];
        if (is_string($extra)) {
            $extra = json_decode($extra, true) ?: [];
        }
        if (!is_array($extra)) {
            $extra = [];
        }
        $info = $extra['activity_info'] ?? [];
        if (!is_array($info)) {
            $info = [];
        }
        if ($type === '' || $type === 'normal') {
            $snapshotType = (string)($info['activity_type'] ?? '');
            if ($snapshotType !== '' && $snapshotType !== 'normal') {
                $type = $snapshotType;
            }
        }
        if ($id <= 0) {
            $id = (int)($info['activity_id'] ?? 0);
        }
        if ($id <= 0) {
            $legacyIdKey = [
                'seckill' => 'seckill_id',
                'combination' => 'combination_id',
                'bargain' => 'bargain_id',
            ];
            if (isset($legacyIdKey[$type])) {
                $id = (int)($info[$legacyIdKey[$type]] ?? 0);
            }
        }
        if ($type === 'normal') {
            $type = '';
        }
        return [$type, $id];
    }

    /**
     * 获取所有交易模式的ID列表
     * @param array $productIds 商品ID数组，为空则获取所有
     * @param array $field 需要返回的字段
     * @return array 返回交易模式类型为key的ID列表
     */
    public function getAllActivityIdsList(array $productIds = [], array $field = []): array
    {
        $result = [];
        foreach ($this->allInstances() as $modeKey => $handler) {
            $result[$modeKey] = $handler->getActivityIdsList($productIds, $field);
        }
        return $result;
    }
}
