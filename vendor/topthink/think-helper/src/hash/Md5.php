<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------
namespace think\helper\hash;

class Md5
{

    protected $salt = 'think';

    public function make($value, array $options = [])
    {
        $salt = isset($options['salt']) ? $options['salt'] : $this->salt;

        return md5(md5($value) . $salt);
    }

    public function check($value, $hashedValue, array $options = [])
    {
        if (strlen($hashedValue) === 0) {
            return false;
        }

        $salt = isset($options['salt']) ? $options['salt'] : $this->salt;

        return md5(md5($value) . $salt) == $hashedValue;
    }

    public function setSalt($salt)
    {
        $this->salt = (string)$salt;

        return $this;
    }
}