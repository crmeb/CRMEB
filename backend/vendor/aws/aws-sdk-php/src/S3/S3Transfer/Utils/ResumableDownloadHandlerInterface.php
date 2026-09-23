<?php

namespace Aws\S3\S3Transfer\Utils;

interface ResumableDownloadHandlerInterface
{
    /**
     * @return string
     */
    public function getResumeFilePath(): string;

    /**
     * @return string
     */
    public function getTemporaryFilePath(): string;

    /**
     * @return string
     */
    public function getDestination(): string;

    /**
     * @return int
     */
    public function getFixedPartSize(): int;
}
