<?php
namespace app\wap\controller;

use app\admin\model\wechat\WechatReply;
use service\WechatService;


/**
 * 微信服务器  验证控制器
 * Class Wechat
 * @package app\wap\controller
 */
class Wechat
{

    /**
     * 微信服务器  验证
     */
    public function serve()
    {
        ob_clean();
        WechatService::serve();
    }

    /**
     *   支付  异步回调
     */
    public function notify()
    {
        WechatService::handleNotify();
    }

    public function text()
    {
        dump(WechatService::userGroupService());
    }


}


