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
namespace app\adminapi\controller\v1\application\wechat;


use app\adminapi\controller\AuthController;
use app\services\wechat\WechatMenuServices;
use think\facade\App;

/**
 * 微信菜单  控制器
 * Class Menus
 * @package app\admin\controller\wechat
 */
class Menus extends AuthController
{
    /**
     * 构造方法
     * Menus constructor.
     * @param App $app
     * @param WechatMenuServices $services
     */
    public function __construct(App $app, WechatMenuServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取菜单
     * @return mixed
     */
    public function index()
    {
        $menus = $this->services->getWechatMenu();
        return app('json')->success(compact('menus'));
    }

    /**
     * 保存菜单
     * @return mixed
     */
    public function save()
    {
        $buttons = request()->post('button/a', []);
        if (!count($buttons)) return app('json')->fail('请添加至少一个按钮');
        $this->services->saveMenu($buttons);
        return app('json')->success('修改成功!');
    }
}
