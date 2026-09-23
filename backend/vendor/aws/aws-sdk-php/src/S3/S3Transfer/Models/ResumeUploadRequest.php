<?php

namespace Aws\S3\S3Transfer\Models;

use Aws\S3\S3Transfer\Progress\AbstractTransferListener;

final class ResumeUploadRequest
{
    /** @var ResumableUpload|string */
    private ResumableUpload|string $resumableUpload;

    /** @var array */
    private array $listeners;

    /** @var AbstractTransferListener|null */
    private ?AbstractTransferListener $progressTracker;

    /**
     * @param ResumableUpload|string $resumableUpload
     * @param array $listeners
     * @param AbstractTransferListener|null $progressTracker
     */
    public function __construct(
        string|ResumableUpload $resumableUpload,
        array $listeners = [],
        ?AbstractTransferListener $progressTracker = null
    ) {
        $this->resumableUpload = $resumableUpload;
        $this->listeners = $listeners;
        $this->progressTracker = $progressTracker;
    }

    /**
     * @return string|ResumableUpload
     */
    public function getResumableUpload(): string|ResumableUpload
    {
        return $this->resumableUpload;
    }

    /**
     * @return array
     */
    public function getListeners(): array
    {
        return $this->listeners;
    }

    /**
     * @return AbstractTransferListener|null
     */
    public function getProgressTracker(): ?AbstractTransferListener
    {
        return $this->progressTracker;
    }
}
