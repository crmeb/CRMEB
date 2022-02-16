<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\services\easywechat;


use crmeb\services\easywechat\oauth2\wechat\WechatOauth2Provider;
use crmeb\services\easywechat\wechatlive\ProgramProvider as LiveProgramProvider;


/**
 * Class Application
 * @package crmeb\services\easywechat
 * @property LiveProgramProvider $wechat_live
 * @property WechatOauth2Provider $oauth2
 */
class Application extends \EasyWeChat\Foundation\Application
{

    /**
     * @var string[]
     */
    protected $providersNew = [
        LiveProgramProvider::class,
        WechatOauth2Provider::class
    ];

    /**
     * Application constructor.
     * @param $config
     */
    public function __construct($config)
    {
        $this->providers = array_merge($this->providers, $this->providersNew);
        parent::__construct($config);
    }

}
