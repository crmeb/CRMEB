<?php

namespace Aws\S3\S3Transfer\Models;

use Aws\S3\S3Transfer\Progress\AbstractTransferListener;
use Aws\S3\S3Transfer\Utils\FileDownloadHandler;

final class ResumeDownloadRequest
{
    /** @var ResumableDownload|string */
    private ResumableDownload|string $resumableDownload;

    /** @var string */
    private string $downloadHandlerClass;

    /** @var array */
    private array $listeners;

    /** @var AbstractTransferListener|null */
    private ?AbstractTransferListener $progressTracker;

    /**
     * @param ResumableDownload|string $resumableDownload
     * @param string $downloadHandlerClass
     * @param array $listeners
     * @param AbstractTransferListener|null $progressTracker
     */
    public function __construct(
        string|ResumableDownload $resumableDownload,
        string $downloadHandlerClass = FileDownloadHandler::class,
        array $listeners = [],
        ?AbstractTransferListener $progressTracker = null
    ) {
        $this->resumableDownload = $resumableDownload;
        $this->downloadHandlerClass = $downloadHandlerClass;
        $this->listeners = $listeners;
        $this->progressTracker = $progressTracker;
    }

    /**
     * @return string|ResumableDownload
     */
    public function getResumableDownload(): string|ResumableDownload
    {
        return $this->resumableDownload;
    }

    /**
     * @return string
     */
    public function getDownloadHandlerClass(): string
    {
        return $this->downloadHandlerClass;
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
