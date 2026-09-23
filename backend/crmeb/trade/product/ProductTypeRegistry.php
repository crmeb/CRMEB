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

namespace crmeb\trade\product;

/**
 * 产品类型注册中心
 * 管理所有已注册的产品类型 Handler
 */
class ProductTypeRegistry
{
    /**
     * 已注册的处理器类
     * @var array<string, class-string>
     */
    protected array $handlers = [];

    /**
     * 处理器实例缓存
     * @var array<string, ProductTypeHandlerInterface>
     */
    protected array $instances = [];

    /**
     * 注册产品类型
     * @param string $typeKey
     * @param class-string $handlerClass
     * @return void
     */
    public function register(string $typeKey, string $handlerClass): void
    {
        $this->handlers[$typeKey] = $handlerClass;
    }

    /**
     * 获取处理器实例
     * 未注册的类型回退到 normal；normal 也未注册时抛出明确异常，而非 undefined index 致命错误
     * @param string $typeKey
     * @return ProductTypeHandlerInterface
     * @throws \RuntimeException 当类型未注册且无 normal 兜底时
     */
    public function get(string $typeKey): ProductTypeHandlerInterface
    {
        if (!isset($this->instances[$typeKey])) {
            // 确定实际使用的 key：请求的类型未注册时回退到 normal
            $actualKey = isset($this->handlers[$typeKey]) ? $typeKey : 'normal';
            if (!isset($this->handlers[$actualKey])) {
                throw new \RuntimeException("[ProductTypeRegistry] 产品类型未注册且无 normal 兜底: {$typeKey}");
            }
            $this->instances[$typeKey] = new $this->handlers[$actualKey]();
        }
        return $this->instances[$typeKey];
    }

    /**
     * 是否已注册
     * @param string $typeKey
     * @return bool
     */
    public function has(string $typeKey): bool
    {
        return isset($this->handlers[$typeKey]);
    }

    /**
     * 获取所有已注册类型标识
     * @return array<string>
     */
    public function all(): array
    {
        return array_keys($this->handlers);
    }

    /**
     * 获取所有类型信息（供下拉选择）
     * @return array
     */
    public function allInfo(): array
    {
        $list = [];
        foreach ($this->handlers as $typeKey => $class) {
            $handler = $this->get($typeKey);
            $list[] = [
                'key'  => $handler->getTypeKey(),
                'name' => $handler->getTypeName(),
            ];
        }
        return $list;
    }
}
