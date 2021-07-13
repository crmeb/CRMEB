<?php

namespace AlibabaCloud\Tea\Exception;

/**
 * Class TeaRetryError.
 */
class TeaRetryError extends TeaError
{
    /**
     * TeaRetryError constructor.
     *
     * @param string          $message
     * @param int             $code
     * @param null|\Throwable $previous
     */
    public function __construct($message = '', $code = 0, $previous = null)
    {
        parent::__construct([], $message, $code, $previous);
    }
}
