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

namespace crmeb\utils;

use Godruoyi\Snowflake\FileLockResolver;
use Godruoyi\Snowflake\RedisSequenceResolver;
use Godruoyi\Snowflake\Snowflake;
use think\facade\Cache;
use think\facade\Config;

/**
 * 雪花算法订单号生成器
 *
 * 底层公共工具，供订单中台、充值模块等任何需要唯一单号的业务调用。
 * 不依赖任何业务表，仅依赖缓存（file/redis）保证序列号唯一性。
 */
class SnowflakeIdGenerator
{
    /**
     * 生成带前缀的唯一订单号
     *
     * @param string $prefix 订单号前缀（如 wx、cz）
     * @return string 前缀 + 雪花ID
     */
    public static function generate(string $prefix = 'wx'): string
    {
        $snowflake = (new Snowflake())->setStartTimeStamp(strtotime('2022-01-01') * 1000);

        if (Config::get('cache.default') === 'file') {
            $lockDirectory = runtime_path('snowflake');
            if (!is_dir($lockDirectory) && !mkdir($lockDirectory, 0755, true) && !is_dir($lockDirectory)) {
                throw new \RuntimeException(sprintf('Unable to create Snowflake lock directory: %s', $lockDirectory));
            }
            $sequenceResolver = new FileLockResolver($lockDirectory);
        } else {
            $redis = Cache::store('redis');
            $sequenceResolver = (new RedisSequenceResolver($redis->handler()))
                ->setCachePrefix((string)Config::get('cache.stores.redis.prefix', '') . 'snowflake:');
        }

        return $prefix . $snowflake->setSequenceResolver($sequenceResolver)->id();
    }
}
