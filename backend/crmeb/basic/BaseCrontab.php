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

namespace crmeb\basic;

/**
 * 定时任务基类
 * 所有定时任务类继承此类
 * Class BaseCrontab
 * @package crmeb\basic
 */
abstract class BaseCrontab
{
    /**
     * 任务名称
     * @var string
     */
    public string $name = '';

    /**
     * Cron 表达式
     * @var string
     */
    public string $cron = '* * * * *';

    /**
     * 是否启用
     * @var bool
     */
    public bool $enable = true;

    /**
     * 是否单例运行（防止重复执行）
     * @var bool
     */
    public bool $singleton = true;

    /**
     * 执行方法
     * @return void
     */
    abstract public function handle(): void;

    /**
     * 自定义执行条件
     * @return bool
     */
    public function shouldRun(): bool
    {
        return true;
    }
}
