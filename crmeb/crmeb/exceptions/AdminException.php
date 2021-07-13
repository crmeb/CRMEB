<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\exceptions;


use Throwable;

/**
 * Class AuthException
 * @package crmeb\exceptions
 */
class AdminException extends \RuntimeException
{
    public function __construct($message, $code = 0, Throwable $previous = null)
    {
        if(is_array($message)){
            $errInfo = $message;
            $message = $errInfo[1] ?? '未知错误';
            if ($code === 0) {
                $code = $errInfo[0] ?? 400;
            }
        }

        parent::__construct($message, $code, $previous);
    }
}
