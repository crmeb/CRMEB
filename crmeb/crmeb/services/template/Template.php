<?php
/**
 * Created by PhpStorm.
 * User: xurongyao <763569752@qq.com>
 * Date: 2019/11/23 3:47 PM
 */

namespace crmeb\services\template;

use crmeb\basic\BaseManager;
use think\facade\Config;

/**
 * Class Template
 * @package crmeb\services\template
 * @mixin \crmeb\services\template\storage\Wechat
 */
class Template extends BaseManager
{

    /**
     * 空间名
     * @var string
     */
    protected $namespace = '\\crmeb\\services\\template\\storage\\';

    /**
     * 设置默认
     * @return mixed
     */
    protected function getDefaultDriver()
    {
        return Config::get('template.default', 'wechat');
    }
}