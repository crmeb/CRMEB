<div>
  <p align="center">
    <image src="https://www.pngkey.com/png/full/105-1052235_snowflake-png-transparent-background-snowflake-with-clear-background.png" width="250" height="250"></image>
  </p>
  <p align="center">An ID Generator for PHP based on Snowflake Algorithm (Twitter announced).</p>
  <p align="center">
    <a href="https://github.com/godruoyi/php-snowflake/actions/workflows/test.yml">
      <image src="https://github.com/godruoyi/php-snowflake/actions/workflows/test.yml/badge.svg" alt="build passed"></image>
    </a>
    <a href="https://codecov.io/gh/godruoyi/php-snowflake">
      <img src="https://codecov.io/gh/godruoyi/php-snowflake/branch/master/graph/badge.svg?token=7AAOYCJK97" alt=""/>
    </a>
    <a href="https://github.com/godruoyi/php-snowflake">
      <image src="https://poser.pugx.org/godruoyi/php-snowflake/license" alt="License"></image>
    </a>
    <a href="https://packagist.org/packages/godruoyi/php-snowflake">
      <image src="https://poser.pugx.org/godruoyi/php-snowflake/v/stable" alt="Packagist Version"></image>
    </a>
    <a href="https://github.com/godruoyi/php-snowflake">
      <image src="https://poser.pugx.org/godruoyi/php-snowflake/downloads" alt="Total Downloads"></image>
    </a>
  </p>
</div>

## Description

Snowflake & Sonyflake algorithm PHP implementation [中文文档](https://github.com/godruoyi/php-snowflake/blob/master/README-zh_CN.md).

![file](https://images.godruoyi.com/logos/201908/13/_1565672621_LPW65Pi8cG.png)

Snowflake is a network service that generates unique ID numbers at a high scale with simple guarantees.

1. The first bit is an unused sign bit.
2. The second part consists of a 41-bit timestamp (in milliseconds) representing the offset of the current time relative to a certain reference time.
3. The third and fourth parts are represented by 5 bits each, indicating the data centerID and workerID. The maximum value for both is 31 (2^5 -1).
4. The last part consists of 12 bits, which represents the length of the serial number generated per millisecond per working node. A maximum of 4095 IDs can be generated in the same millisecond (2^12 -1).

If you want to generate unique IDs using the snowflake algorithm, you must ensure that sequence numbers generated within the same millisecond on the same node are unique.
Based on this requirement, we have created this package which integrates multiple sequence number providers.

* RandomSequenceResolver (Random Sequence Number, UnSafe)
* FileLockResolver (Uses PHP file lock `fopen/flock`, **Concurrency Safety**)
* RedisSequenceResolver (Redis psetex and incrby, **Concurrency safety**)
* PredisSequenceResolver (redis psetex and incrby, **Concurrency Safety**)
* LaravelSequenceResolver (Laravel Cache [add](https://github.com/laravel/framework/blob/11.x/src/Illuminate/Contracts/Cache/Repository.php#L39) lock mechanism)
* SwooleSequenceResolver (swoole_lock for **Concurrency Safety**)

## Requirement

1. PHP >= 8.1
2. **[Composer](https://getcomposer.org/)**

## Installation

```shell
$ composer require godruoyi/php-snowflake -vvv

# Install `predis/predis` package if you are using PredisSequenceResolver
$ composer require "predis/predis"

# Install `Redis` extensions if you are using RedisSequenceResolver
$ pecl install redis

# Install `Swoole` extensions if you are using SwooleSequenceResolver
$ pecl install swoole
```

## Usage

1. simple to use.

```php
$snowflake = new \Godruoyi\Snowflake\Snowflake;

$snowflake->id();
// 1537200202186752
```

2. Specify the data center ID and machine ID.

```php
$snowflake = new \Godruoyi\Snowflake\Snowflake($datacenterId, $workerId);

$snowflake->id();
```

3. Specify the start time.

```php
$snowflake = new \Godruoyi\Snowflake\Snowflake;
$snowflake->setStartTimeStamp(strtotime('2019-09-09')*1000); // millisecond

$snowflake->id();
```

> The maximum value of a 41-bit timestamp (in milliseconds) can represent up to 69 years, so the Snowflake algorithm can run safely for 69 years. In order to make the most of it, we recommend setting a start time.

4. Using different sequence number resolvers (optional).

```php
$snowflake = new \Godruoyi\Snowflake\Snowflake;
$snowflake->setSequenceResolver(new \Godruoyi\Snowflake\RandomSequenceResolver);

$snowflake->id();
```

5. Use Sonyflake

```php
$sonyflake = new \Godruoyi\Snowflake\Sonyflake;

$sonyflake->id();
```
## Advanced

1. Used in Laravel.

Since the SDK is quite straightforward, we do not offer a specific extension for Laravel. However, you can easily integrate it into your Laravel project by following these steps.

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
        $this->app->singleton('snowflake', function ($app) {
            return (new Snowflake())
                ->setStartTimeStamp(strtotime('2019-10-10')*1000)
                ->setSequenceResolver(new LaravelSequenceResolver($app->get('cache.store')));
        });
    }
}
```

2. Custom

To customize the sequence number resolver, you need to implement the Godruoyi\Snowflake\SequenceResolver interface.

```php
class YourSequence implements SequenceResolver
{
    /**
     *  {@inheritdoc}
     */
    public function sequence(int $currentMillisecond)
    {
          // Just test.
        return mt_rand(0, 1);
    }
}

// usage

$snowflake->setSequenceResolver(new YourSequence);
$snowflake->id();
```

And you also can use the Closure:

```php
$snowflake = new \Godruoyi\Snowflake\Snowflake;
$snowflake->setSequenceResolver(function ($currentMillisecond) {
    static $lastTime;
    static $sequence;

    if ($lastTime == $currentMillisecond) {
        ++$sequence;
    } else {
        $sequence = 0;
    }

    $lastTime = $currentMillisecond;

    return $sequence;
})->id();
```

## License

MIT
