<?php

namespace Aws\S3\S3Transfer\Models;

use Throwable;

final class DownloadDirectoryResult
{
    /** @var int */
    private int $objectsDownloaded;

    /** @var int */
    private int $objectsFailed;

    /** @var \Throwable|null */
    private ?\Throwable $reason;

    /**
     * @param int $objectsDownloaded
     * @param int $objectsFailed
     * @param Throwable|null $reason
     */
    public function __construct(
        int $objectsDownloaded,
        int $objectsFailed,
        ?Throwable $reason = null
    ) {
        $this->objectsDownloaded = $objectsDownloaded;
        $this->objectsFailed = $objectsFailed;
        $this->reason = $reason;
    }

    /**
     * @return int
     */
    public function getObjectsDownloaded(): int
    {
        return $this->objectsDownloaded;
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
            "DownloadDirectoryResult: %d objects downloaded, %d objects failed",
            $this->objectsDownloaded,
            $this->objectsFailed
        );
    }
}
