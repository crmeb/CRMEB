<?php
/**
 * Created by CRMEB.
 * User: 136327134@qq.com
 * Date: 2019/4/12 11:19
 */

namespace crmeb\services;



/**
 * 错误码统一存放类
 * Class ApiErrorCode
 * @package crmeb\services
 */
class ApiErrorCode
{

    const SUCCESS = [200,'SUCCESS'];
    const ERROR   = [400,'操作失败'];

    const ERR_LOGIN = [40010,'登陆过期'];


}