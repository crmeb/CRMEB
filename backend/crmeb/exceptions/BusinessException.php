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

/**
 * 业务异常
 * 用于业务逻辑中的非正常流程中断
 * Class BusinessException
 * @package crmeb\exceptions
 */
class BusinessException extends \RuntimeException
{
    /**
     * 错误码
     * @var int
     */
    protected int $errorCode = 0;

    /**
     * 附加数据
     * @var array
     */
    protected array $extra = [];

    public function __construct(string $message = '', int $code = 0, int $errorCode = 0, array $extra = [], ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errorCode = $errorCode;
        $this->extra = $extra;
    }

    /**
     * 获取错误码
     * @return int
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * 获取附加数据
     * @return array
     */
    public function getExtra(): array
    {
        return $this->extra;
    }
}
