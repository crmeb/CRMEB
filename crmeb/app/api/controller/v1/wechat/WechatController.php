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

namespace app\api\controller\v1\wechat;


use app\Request;
use app\services\wechat\WechatServices as WechatAuthServices;

/**
 * 微信公众号
 * Class WechatController
 * @package app\api\controller\wechat
 */
class WechatController
{
    protected $services = NUll;

    /**
     * WechatController constructor.
     * @param WechatAuthServices $services
     */
    public function __construct(WechatAuthServices $services)
    {
        $this->services = $services;
    }

    /**
     * 微信公众号服务
     * @return \think\Response
     */
    public function serve()
    {
        return $this->services->serve();
    }

    /**
     * 支付异步回调
     */
    public function notify()
    {
        return $this->services->notify();
    }

    /**
     * 公众号权限配置信息获取
     * @param Request $request
     * @return mixed
     */
    public function config(Request $request)
    {
        return app('json')->success($this->services->config($request->get('url')));
    }

    /**
     * 公众号授权登陆
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function auth(Request $request)
    {
        [$spreadId, $login_type] = $request->getMore([
            [['spread', 'd'], 0],
            ['login_type', ''],
        ], true);
        $token = $this->services->auth($spreadId, $login_type);
        if ($token && isset($token['key'])) {
            return app('json')->success('授权成功，请绑定手机号', $token);
        } else if ($token) {
            return app('json')->success('登录成功', ['userInfo' => $token['userInfo']]);
        } else
            return app('json')->fail('登录失败');
    }

    public function follow()
    {
        $data = $this->services->follow();
        if ($data) {
            return app('json')->success('ok', $data);
        } else {
            return app('json')->fail('获取失败');
        }

    }
}
