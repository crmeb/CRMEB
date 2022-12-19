<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\services\oauth;


use crmeb\basic\BaseManager;
use crmeb\services\oauth\storage\MiniProgram;
use crmeb\services\oauth\storage\TouTiao;
use crmeb\services\oauth\storage\Wechat;
use think\facade\Config;

/**
 * 第三方登录
 * Class OAuth
 * @package crmeb\services\oauth
 * @mixin Wechat
 * @mixin TouTiao
 * @mixin MiniProgram
 */
class OAuth extends BaseManager
{

    /**
     * 空间名
     * @var string
     */
    protected $namespace = '\\crmeb\\services\\oauth\\storage\\';

    /**
     * @return mixed
     */
    protected function getDefaultDriver()
    {
        return Config::get('oauth.default', 'wechat');
    }
}
