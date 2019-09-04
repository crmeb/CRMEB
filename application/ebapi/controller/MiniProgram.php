<?php
namespace app\ebapi\controller;

use app\core\util\MiniProgramService;


/**
 * TODO  小程序消息推送配置
 * Class MiniProgram
 * @package app\ebapi\controller
 */
class MiniProgram
{

    /**
     * 微信服务器  验证
     */
    public function serve()
    {
        ob_clean();
        MiniProgramService::serve();
    }

}


