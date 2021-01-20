<?php

namespace AlibabaCloud\Tea\Exception;

use AlibabaCloud\Tea\Request;

/**
 * Class TeaUnableRetryError.
 */
class TeaUnableRetryError extends TeaError
{
    private $lastRequest;
    private $lastException;

    /**
     * TeaUnableRetryError constructor.
     *
     * @param Request         $lastRequest
     * @param null|\Exception $lastException
     */
    public function __construct($lastRequest, $lastException = null)
    {
        $error_info = [];
        if (null !== $lastException && $lastException instanceof TeaError) {
            $error_info = $lastException->getErrorInfo();
        }
        parent::__construct($error_info, $lastException->getMessage(), $lastException->getCode(), $lastException);
        $this->lastRequest   = $lastRequest;
        $this->lastException = $lastException;
    }

    public function getLastRequest()
    {
        return $this->lastRequest;
    }

    public function getLastException()
    {
        return $this->lastException;
    }
}
