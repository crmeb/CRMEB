<?php

namespace app\admin\controller\widget;

use app\admin\controller\AuthController;

/**
 * 文件校验控制器
 * Class SystemFile
 * @package app\admin\controller\system
 *
 */
class Widgets extends AuthController
{

    /**
     * icon
     * @return \think\response\Json
     */
    public function icon()
    {
        return $this->fetch('widget/icon');
    }

    /**
     * 会员列页面
     * @return \think\response\Json
     */
    public function userlist()
    {
        return $this->fetch('widget/icon');
    }

    /**
     * 产品列表页
     * @return \think\response\Json
     */
    public function productlist()
    {
        return $this->fetch('widget/icon');
    }

    /**
     * 图文列表页
     * @return \think\response\Json
     */
    public function newtlist()
    {
        return $this->fetch('widget/icon');
    }


}
