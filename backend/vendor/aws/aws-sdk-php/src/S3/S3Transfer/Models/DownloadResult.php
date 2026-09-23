<?php

namespace Aws\S3\S3Transfer\Models;

use Aws\Result;

final class DownloadResult extends Result
{
    private readonly mixed $downloadDataResult;

    /**
     * @param mixed $downloadDataResult
     * @param array $data
     */
    public function __construct(
        mixed $downloadDataResult,
        array $data = []
    ) {
        parent::__construct($data);
        $this->downloadDataResult = $downloadDataResult;
    }

    /**
     * @return mixed
     */
    public function getDownloadDataResult(): mixed
    {
        return $this->downloadDataResult;
    }
}
