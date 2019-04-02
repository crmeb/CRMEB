<?php
namespace app\routine\controller;
use service\MiniProgramService;


/**
 * 小程序支付回调
 * Class Routine
 * @package app\routine\controller
 */
class Routine
{
    /**
     *   支付  异步回调
     */
    public function notify()
    {
        MiniProgramService::handleNotify();
    }
}


