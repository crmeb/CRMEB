<?php

namespace crmeb\basic;

use app\Request;

abstract class BaseAuth extends BaseStorage
{

    /**
     * 获取当前句柄名
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 登陆
     * @param Request $request
     * @return mixed
     */
    abstract public function login(Request $request);

    /**
     * 退出登陆
     * @param Request $request
     * @return mixed
     */
    abstract public function logout(Request $request);
}