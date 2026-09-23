<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace crmeb\exceptions;

use Exception;

/**
 * 授权异常
 * 各端授权共用
 * @package crmeb\exceptions
 */
class OAuthException extends Exception
{
    /**
     * @var array
     */
    protected $raw;

    public function __construct($message = "", $code = 0, $raw = [])
    {
        parent::__construct($message, $code);
        $this->raw = $raw;
    }

    /**
     * 获取原始数据
     * @return array
     */
    public function getRaw()
    {
        return $this->raw;
    }
}
