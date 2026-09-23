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

namespace modules\channel\h5;

use think\App as ThinkApp;

/**
 * H5端渠道模块
 * @package modules\channel\h5
 */
class App
{
    protected static string $name = 'H5浏览器';

    protected static string $formType = 'h5';

    public static function detect(): bool
    {
        return strtolower(app('request')->header('Form-type', '')) === static::$formType;
    }

    public static function getFormType(): string
    {
        return static::$formType;
    }

    public static function getName(): string
    {
        return static::$name;
    }

    public static function register(ThinkApp $app): void
    {
    }

    public static function boot(ThinkApp $app): void
    {
    }
}
