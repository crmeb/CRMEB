<?php

namespace Aws\S3\S3Transfer\Models;

use Throwable;

final class UploadDirectoryResult
{
    /** @var int */
    private int $objectsUploaded;

    /** @var int */
    private int $objectsFailed;

    /** @var Throwable|null */
    private ?Throwable $reason;

    /**
     * @param int $objectsUploaded
     * @param int $objectsFailed
     * @param Throwable|null $exception
     */
    public function __construct(
        int $objectsUploaded,
        int $objectsFailed,
        ?Throwable $exception = null
    )
    {
        $this->objectsUploaded = $objectsUploaded;
        $this->objectsFailed = $objectsFailed;
        $this->reason = $exception;
    }

    /**
     * @return int
     */
    public function getObjectsUploaded(): int
    {
        return $this->objectsUploaded;
    }

    /**
     * @return int
     */
    public function getObjectsFailed(): int
    {
        return $this->objectsFailed;
    }

    /**
     * @return Throwable|null
     */
    public function getReason(): ?Throwable
    {
        return $this->reason;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf(
            "UploadDirectoryResult: %d objects uploaded, %d objects failed",
            $this->objectsUploaded,
            $this->objectsFailed
        );
    }
}
