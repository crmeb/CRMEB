<?php

namespace Aws\S3\S3Transfer\Models;

use Aws\Result;

final class UploadResult extends Result
{
    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}
