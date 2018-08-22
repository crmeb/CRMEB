<?php
namespace app\wechat\controller;

use app\admin\model\wechat\WechatReply;
use service\WechatService;

class Index
{

    public function serve()
    {
        WechatService::serve();
    }

    public function notify()
    {
        WechatService::handleNotify();
    }

    public function text()
    {
        dump(WechatService::userGroupService());
    }


}


