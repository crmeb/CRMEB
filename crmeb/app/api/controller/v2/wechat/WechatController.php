<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\api\controller\v2\wechat;

use app\Request;
use app\services\wechat\WechatServices;
use crmeb\services\CacheService;

/**
 * Class WechatController
 * @package app\api\controller\v2\wechat
 */
class WechatController
{
    protected $services = NUll;

    /**
     * WechatController constructor.
     * @param WechatServices $services
     */
    public function __construct(WechatServices $services)
    {
        $this->services = $services;
    }

    /**
     * 公众号授权登录，返回token
     * @param $spread
     * @return \think\Response
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/12
     */
    public function authLogin($spread = '', $agent_id = '')
    {
        $data = $this->services->authLogin($spread, $agent_id);
        return app('json')->success($data);
    }

    /**
     * 公众号授权绑定手机号
     * @param string $key
     * @param string $phone
     * @param string $captcha
     * @return \think\Response
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/12
     */
    public function authBindingPhone($key = '', $phone = '', $captcha = '')
    {
        //验证验证码
        $verifyCode = CacheService::get('code_' . $phone);
        if (!$verifyCode)
            return app('json')->fail(410009);
        $verifyCode = substr($verifyCode, 0, 6);
        if ($verifyCode != $captcha) {
            CacheService::delete('code_' . $phone);
            return app('json')->fail(410010);
        }
        CacheService::delete('code_' . $phone);
        $data = $this->services->authBindingPhone($key, $phone);
        return app('json')->success($data);
    }
}
