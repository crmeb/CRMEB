<?php

namespace Aws\S3\S3Transfer\Models;

use Aws\S3\S3Transfer\Utils\FileDownloadHandler;

final class DownloadFileRequest
{
    /** @var string */
    private string $destination;

    /**
     * To decide whether an error should be raised
     * if the destination file exists.
     *
     * @var bool
     */
    private bool $failsWhenDestinationExists;

    /** @var DownloadRequest */
    private DownloadRequest $downloadRequest;

    /**
     * @param string $destination
     * @param bool $failsWhenDestinationExists
     * @param DownloadRequest $downloadRequest
     */
    public function __construct(
        string $destination,
        bool $failsWhenDestinationExists,
        DownloadRequest $downloadRequest
    ) {
        $this->destination = $destination;
        $this->failsWhenDestinationExists = $failsWhenDestinationExists;
        $this->downloadRequest = DownloadRequest::fromDownloadRequestAndDownloadHandler(
            $downloadRequest,
            new FileDownloadHandler(
                $destination,
                $failsWhenDestinationExists,
                $downloadRequest->getConfig()['resume_enabled'] ?? false
            )
        );
    }

    /**
     * @return string
     */
    public function getDestination(): string
    {
        return $this->destination;
    }

    /**
     * @return bool
     */
    public function isFailsWhenDestinationExists(): bool
    {
        return $this->failsWhenDestinationExists;
    }

    /**
     * @return DownloadRequest
     */
    public function getDownloadRequest(): DownloadRequest
    {
        return $this->downloadRequest;
    }
}
