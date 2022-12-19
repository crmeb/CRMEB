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

namespace crmeb\services\app;

use crmeb\services\easywechat\Application;

class WechatOpenService
{

    protected function options()
    {
        $options = [
            'app_id' => sys_config('wechat_open_app_id'),
            'secret' => sys_config('wechat_open_app_secret')
        ];
        return $options;
    }

    /**
     * @return Application
     */
    protected function application()
    {
        return new Application($this->options());
    }

    /**
     * @return mixed
     */
    public function serve()
    {
        return $this->application()->open_platform->server->serve();
    }

    /**
     * 使用授权码换取公众号的接口调用凭据和授权信息
     * @param string $code
     * @return \EasyWeChat\Support\Collection
     */
    public function getAuthorizationInfo()
    {
        return $this->application()->oauth->user();
    }

    /**
     * 获取授权方的公众号帐号基本信息
     * @param string $appid
     * @return \EasyWeChat\Support\Collection
     */
    public function getAuthorizerInfo()
    {
        return $this->application()->oauth->user();
    }


}
