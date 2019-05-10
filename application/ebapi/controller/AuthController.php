<?php
namespace app\ebapi\controller;


class AuthController extends Basic
{
    protected $uid = 0;

    protected $userInfo = [];

    protected function _initialize()
    {
        parent::_initialize();
        //验证TOken并获取user信息
        $this->userInfo=$this->checkTokenGetUserInfo();
        $this->uid=isset($this->userInfo['uid']) ? $this->userInfo['uid'] : 0;
    }
}