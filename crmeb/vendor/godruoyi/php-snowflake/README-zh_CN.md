<div>
  <p align="center">
    <image src="https://www.pngkey.com/png/full/105-1052235_snowflake-png-transparent-background-snowflake-with-clear-background.png" width="250" height="250">
  </p>
  <p align="center">An ID Generator for PHP based on Snowflake Algorithm (Twitter announced).</p>
  <p align="center">
    <a href="https://scrutinizer-ci.com/g/godruoyi/php-snowflake/">
      <image src="https://scrutinizer-ci.com/g/godruoyi/php-snowflake/badges/quality-score.png?b=master" alt="quality score">
    </a>
<!--     <a href="https://scrutinizer-ci.com/g/godruoyi/php-snowflake/">
      <image src="https://scrutinizer-ci.com/g/godruoyi/php-snowflake/badges/coverage.png?b=master" alt="php-snowflake">
    </a> -->
    <a href="https://github.com/godruoyi/php-snowflake">
      <image src="https://poser.pugx.org/godruoyi/php-snowflake/license" alt="License">
    </a>
    <a href="https://packagist.org/packages/godruoyi/php-snowflake">
      <image src="https://poser.pugx.org/godruoyi/php-snowflake/v/stable" alt="Packagist Version">
    </a>
    <a href="https://packagist.org/packages/godruoyi/php-snowflake">
      <image src="https://scrutinizer-ci.com/g/godruoyi/php-snowflake/badges/build.png?b=master" alt="build passed">
    </a>
    <a href="https://packagist.org/packages/godruoyi/php-snowflake">
      <image src="https://poser.pugx.org/godruoyi/php-snowflake/downloads" alt="Total Downloads">
    </a>
  </p>
</div>

## 说明

雪花算法的 PHP 实现

![file](https://images.godruoyi.com/comments/201908/13/_1565668072_AbkRnhQaYk.png)

Snowflake 是 Twitter 内部的一个 ID 生算法，可以通过一些简单的规则保证在大规模分布式情况下生成唯一的 ID 号码。其组成为：

* 第一个 bit 为未使用的符号位。
* 第二部分由 41 位的时间戳（毫秒）构成，他的取值是当前时间相对于某一时间的偏移量。
* 第三部分和第四部分的 5 个 bit 位表示数据中心和机器ID，其能表示的最大值为 2^5 -1 = 31。
* 最后部分由 12 个 bit 组成，其表示每个工作节点**每毫秒**生成的序列号 ID，同一毫秒内最多可生成 2^12 -1 即 4095 个 ID。

需要注意的是：

* 在分布式环境中，5 个 bit 位的 datacenter 和 worker 表示最多能部署 31 个数据中心，每个数据中心最多可部署 31 台节点
* 41 位的二进制长度最多能表示 2^41 -1 毫秒即 69 年，所以雪花算法最多能正常使用 69 年，为了能最大限度的使用该算法，你应该为其指定一个开始时间。

> 由上可知，雪花算法生成的 ID 并不能保证唯一，如当两个不同请求同一时刻进入相同的数据中心的相同节点时，而此时该节点生成的 sequence 又是相同时，就会导致生成的 ID 重复。

所以要想使用雪花算法生成唯一的 ID，就需要保证同一节点同一毫秒内生成的序列号是唯一的。基于此，我们在 SDK 中集成了多种序列号提供者：

* RandomSequenceResolver（随机生成）
* RedisSequenceResolver （基于 redis psetex 和 incrby 生成）
* LaravelSequenceResolver（基于 redis psetex 和 incrby 生成）
* SwooleSequenceResolver（基于 swoole_lock 锁）

不同的提供者只需要保证**同一毫秒生成的序列号不同**，就能得到唯一的 ID。

## 要求

1. PHP >= 7.0
2. **[Composer](https://getcomposer.org/)**

## 安装

```shell
$ composer require godruoyi/php-snowflake -vvv
```

## 使用

1. 简单使用.

```php
$snowflake = new \Godruoyi\Snowflake\Snowflake;

$snowflake->id();
// 1537200202186752
```

2. 指定数据中心ID及机器ID.

```php
$snowflake = new \Godruoyi\Snowflake\Snowflake($datacenterId, $workerId);

$snowflake->id();
```

3. 指定开始时间.

```php
$snowflake = new \Godruoyi\Snowflake\Snowflake;
$snowflake->setStartTimeStamp(strtotime('2019-08-08')*1000);

$snowflake->id();
```

## 高级

1. 在 Laravel 中使用

因为 SDK 相对简单，我们并没有提供 Laravel 的扩展包，你可通过下面的方式快速集成到 Laravel 中。

```php
// App\Providers\AppServiceProvider

use Godruoyi\Snowflake\Snowflake;
use Godruoyi\Snowflake\LaravelSequenceResolver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('snowflake', function () {
            return (new Snowflake())
                ->setStartTimeStamp(strtotime('2019-08-08')*1000)
                ->setSequenceResolver(
                    new LaravelSequenceResolver($this->app->get('cache')->store()
                ));
        });
    }
}
```

2. 自定义序列号解决器

你可以通过实现 Godruoyi\Snowflake\SequenceResolver 接口来自定义序列号解决器。

```php
class YourSequence implements SequenceResolver
{
    /**
     *  {@inheritdoc}
     */
    public function sequence(int $currentTime)
    {
          // Just test.
        return mt_rand(0, 1);
    }
}

// usage

$snowflake->setSequenceResolver(new YourSequence);
$snowflake->id();
```

你也可以直接使用闭包：

```php
$snowflake = new \Godruoyi\Snowflake\Snowflake;
$snowflake->setSequenceResolver(function ($currentTime) {
    static $lastTime;
    static $sequence;

    if ($lastTime == $currentTime) {
        ++$sequence;
    } else {
        $sequence = 0;
    }

    $lastTime = $currentTime;

    return $sequence;
})->id();
```

## License

MIT