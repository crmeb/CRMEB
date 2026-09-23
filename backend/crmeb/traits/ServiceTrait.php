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

namespace crmeb\traits;

/**
 * 服务复用 Trait
 * 提供通用的服务层辅助方法
 * Trait ServiceTrait
 * @package crmeb\traits
 */
trait ServiceTrait
{
    /**
     * 事务处理
     * @param callable $callback
     * @return mixed
     * @throws \Throwable
     */
    protected function transaction(callable $callback)
    {
        return \think\facade\Db::transaction($callback);
    }

    /**
     * 获取请求参数
     * @param array $field 需要获取的字段
     * @param bool $must 是否必须
     * @return array
     */
    protected function requestParams(array $field, bool $must = false): array
    {
        $data = [];
        foreach ($field as $k => $v) {
            if (is_numeric($k)) {
                $key = $v;
                $default = null;
            } else {
                $key = $k;
                $default = $v;
            }
            $value = request()->param($key, $default);
            if ($must && is_null($value)) {
                throw new \think\exception\ValidateException("参数 {$key} 不能为空");
            }
            $data[$key] = $value;
        }
        return $data;
    }

    /**
     * 获取分页参数
     * @return array{page: int, limit: int}
     */
    protected function getPage(): array
    {
        $page = (int)request()->param('page', 1);
        $limit = (int)request()->param('limit', 10);
        $limit = min($limit, 100); // 最大100条
        return compact('page', 'limit');
    }
}
