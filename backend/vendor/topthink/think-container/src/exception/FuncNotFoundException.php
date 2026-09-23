<?php

namespace think\exception;

use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
use Throwable;

class FuncNotFoundException extends RuntimeException implements NotFoundExceptionInterface
{
    public function __construct(string $message, protected string $func = '', ?Throwable $previous = null)
    {
        $this->message = $message;

        parent::__construct($message, 0, $previous);
    }

    /**
     * 获取方法名
     * @access public
     * @return string
     */
    public function getFunc()
    {
        return $this->func;
    }
}
