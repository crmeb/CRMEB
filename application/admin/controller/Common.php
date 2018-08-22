<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/07
 */

namespace app\admin\controller;


use service\UtilService;

class Common extends AuthController
{
    public function rmPublicResource($url)
    {
        $res = UtilService::rmPublicResource($url);
        if($res->status)
            return $this->successful('删除成功!');
        else
            return $this->failed($res->msg);
    }
}