<?php
/**
 * Created by CRMEB.
 * User: 136327134@qq.com
 * Date: 2019/4/11 9:47
 */

namespace app\core\logic\routine;

use app\core\implement\ProviderInterface;

class RoutineLogin implements ProviderInterface
{
    public function register($config)
    {
        return ['routine_login',new self()];
    }




}