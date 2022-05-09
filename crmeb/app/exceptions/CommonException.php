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

namespace app\exceptions;

use crmeb\utils\AdminApiErrorCode;
use Throwable;

/**
 * 公共错误信息
 * Class CommonException
 * @package app\exceptions
 */
class CommonException extends \RuntimeException
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

        // 通过错误获取code 当前方式不支持常量数组
        $errCode = AdminApiErrorCode::getCode($message);
        if ($errCode > 0) {
            $code = $errCode;
        }

        parent::__construct($message, $code, $previous);
    }
}