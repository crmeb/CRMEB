<?php

namespace Aws\S3\S3Transfer;

use Aws\MetricsBuilder;
use Aws\ResultInterface;
use Aws\S3\S3Client;
use Aws\S3\S3ClientInterface;
use Aws\S3\S3Transfer\Exception\S3TransferException;
use Aws\S3\S3Transfer\Models\DownloadDirectoryRequest;
use Aws\S3\S3Transfer\Models\DownloadFileRequest;
use Aws\S3\S3Transfer\Models\DownloadRequest;
use Aws\S3\S3Transfer\Models\ResumableDownload;
use Aws\S3\S3Transfer\Models\AbstractResumableTransfer;
use Aws\S3\S3Transfer\Models\ResumableUpload;
use Aws\S3\S3Transfer\Models\ResumeDownloadRequest;
use Aws\S3\S3Transfer\Models\ResumeUploadRequest;
use Aws\S3\S3Transfer\Models\S3TransferManagerConfig;
use Aws\S3\S3Transfer\Models\UploadDirectoryRequest;
use Aws\S3\S3Transfer\Models\UploadRequest;
use Aws\S3\S3Transfer\Models\UploadResult;
use Aws\S3\S3Transfer\Progress\SingleProgressTracker;
use Aws\S3\S3Transfer\Progress\AbstractTransferListener;
use Aws\S3\S3Transfer\Progress\TransferListenerNotifier;
use Aws\S3\S3Transfer\Progress\TransferProgressSnapshot;
use Aws\S3\S3Transfer\Utils\AbstractDownloadHandler;
use Aws\S3\S3Transfer\Utils\FileDownloadHandler;
use GuzzleHttp\Promise\PromiseInterface;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Throwable;

final class S3TransferManager
{
    /** @var S3Client  */
    private S3ClientInterface $s3Client;

    /** @var S3TransferManagerConfig  */
    private S3TransferManagerConfig $config;

    /**
     * @param S3ClientInterface|null $s3Client If provided as null then,
     * a default client will be created where its region will be the one
     * resolved from either the default from the config or the provided.
     * @param array|S3TransferManagerConfig|null $config
     */
    public function __construct(
        ?S3ClientInterface $s3Client = null,
        array|S3TransferManagerConfig|null $config = null
    ) {
        if ($config === null || is_array($config)) {
            $this->config = S3TransferManagerConfig::fromArray($config ?? []);
        } else {
            $this->config = $config;
        }

        if ($s3Client === null) {
            $this->s3Client = $this->defaultS3Client();
        } else {
            $this->s3Client = $s3Client;
        }

        MetricsBuilder::appendMetricsCaptureMiddleware(
            $this->s3Client->getHandlerList(),
            MetricsBuilder::S3_TRANSFER
        );
    }

    /**
     * @return S3ClientInterface
     */
    public function getS3Client(): S3ClientInterface
    {
        return $this->s3Client;
    }

    /**
     * @return S3TransferManagerConfig
     */
    public function getConfig(): S3TransferManagerConfig
    {
        return $this->config;
    }

    /**
     * @param UploadRequest $uploadRequest
     * @param S3ClientInterface|null $s3Client
     *
     * @return PromiseInterface
     */
    public function upload(
        UploadRequest $uploadRequest,
        ?S3ClientInterface $s3Client = null
    ): PromiseInterface
    {
        $client = $s3Client ?? $this->s3Client;
        // Make sure it is a valid in path in case of a string
        $uploadRequest->validateSource();

        // Valid required parameters
        $uploadRequest->validateRequiredParameters();

        $uploadRequest->updateConfigWithDefaults(
            $this->config->toArray()
        );

        $uploadRequest->validateConfig();

        $config = $uploadRequest->getConfig();

        // Validate progress tracker
        $progressTracker = $uploadRequest->getProgressTracker();
        if ($progressTracker === null
            && ($config['track_progress']
                ?? $this->config->isTrackProgress())) {
            $progressTracker = new SingleProgressTracker();
        }

        // Append progress tracker to listeners if not null
        $listeners = $uploadRequest->getListeners();
        if ($progressTracker !== null) {
            $listeners[] = $progressTracker;
        }

        $listenerNotifier = new TransferListenerNotifier($listeners);

        // Validate multipart upload threshold
        $mupThreshold = $config['multipart_upload_threshold_bytes']
            ?? $this->config->getMultipartUploadThresholdBytes();
        if ($mupThreshold < AbstractMultipartUploader::PART_MIN_SIZE) {
            throw new InvalidArgumentException(
                "The provided config `multipart_upload_threshold_bytes`"
                ."must be greater than or equal to " . AbstractMultipartUploader::PART_MIN_SIZE
            );
        }

        if ($this->requiresMultipartUpload($uploadRequest->getSource(), $mupThreshold)) {
            return $this->tryMultipartUpload(
                $uploadRequest,
                $client,
                $listenerNotifier
            );
        }

        return $this->trySingleUpload(
            $uploadRequest->getSource(),
            $uploadRequest->getUploadRequestArgs(),
            $listenerNotifier,
            $client
        );
    }

    /**
     * @param UploadDirectoryRequest $uploadDirectoryRequest
     *
     * @return PromiseInterface
     */
    public function uploadDirectory(
        UploadDirectoryRequest $uploadDirectoryRequest,
    ): PromiseInterface
    {
        return (new DirectoryUploader(
            $this->s3Client,
            $this->config->toArray(),
            fn(S3ClientInterface $client, UploadRequest $request): PromiseInterface => $this->upload($request, $client),
            $uploadDirectoryRequest,
        ))->promise();
    }

    /**
     * @param DownloadRequest $downloadRequest
     *
     * @return PromiseInterface
     */
    public function download(DownloadRequest $downloadRequest): PromiseInterface
    {
        return $this->downloadInternal($downloadRequest, $this->s3Client);
    }

    /**
     * @param DownloadRequest $downloadRequest
     * @param S3ClientInterface $s3Client
     *
     * @return PromiseInterface
     */
    private function downloadInternal(
        DownloadRequest $downloadRequest,
        S3ClientInterface $s3Client
    ): PromiseInterface {
        $sourceArgs = $downloadRequest->normalizeSourceAsArray();
        $getObjectRequestArgs = $downloadRequest->getObjectRequestArgs();

        $downloadRequest->updateConfigWithDefaults($this->config->toArray());

        $downloadRequest->validateConfig();

        $config = $downloadRequest->getConfig();

        $progressTracker = $downloadRequest->getProgressTracker();
        if ($progressTracker === null && $config['track_progress']) {
            $progressTracker = new SingleProgressTracker();
        }

        $listeners = $downloadRequest->getListeners();
        if ($progressTracker !== null) {
            $listeners[] = $progressTracker;
        }

        $listenerNotifier = new TransferListenerNotifier($listeners);

        foreach ($sourceArgs as $key => $value) {
            $getObjectRequestArgs[$key] = $value;
        }

        return $this->tryMultipartDownload(
            $getObjectRequestArgs,
            $config,
            $downloadRequest->getDownloadHandler(),
            $listenerNotifier,
            $s3Client,
        );
    }

    /**
     * @param ResumeDownloadRequest $resumeDownloadRequest
     *
     * @return PromiseInterface
     */
    public function resumeDownload(
        ResumeDownloadRequest $resumeDownloadRequest
    ): PromiseInterface
    {
        $resumableDownload = $resumeDownloadRequest->getResumableDownload();
        if (is_string($resumableDownload)) {
            if (!AbstractResumableTransfer::isResumeFile($resumableDownload)) {
                throw new S3TransferException(
                    "Resume file `$resumableDownload` is not a valid resumable file."
                );
            }

            $resumableDownload = ResumableDownload::fromFile($resumableDownload);
        }

        // Verify that temporary file still exists
        if (!file_exists($resumableDownload->getTemporaryFile())) {
            throw new S3TransferException(
                "Cannot resume download: temporary file does not exist: "
                . $resumableDownload->getTemporaryFile()
            );
        }

        // Verify object ETag hasn't changed
        $headResult = $this->s3Client->headObject([
            'Bucket' => $resumableDownload->getBucket(),
            'Key' => $resumableDownload->getKey(),
        ]);

        $currentETag = $headResult['ETag'] ?? null;
        $resumeETag = $resumableDownload->getETag();
        if (empty($currentETag) || empty($resumeETag)) {
            throw new S3TransferException(
                "Cannot resume download: missing eTag in resumable download"
            );
        }

        if ($currentETag !== $resumableDownload->getETag()) {
            throw new S3TransferException(
                "Cannot resume download: S3 object has changed (ETag mismatch). "
                . "Expected: {$resumableDownload->getETag()}, "
                . "Current: {$currentETag}"
            );
        }

        // Make sure it uses a supported file download handler
        $downloadHandlerClass = $resumeDownloadRequest->getDownloadHandlerClass();
        if (!class_exists($downloadHandlerClass)) {
            throw new S3TransferException(
                "Download handler class `$downloadHandlerClass` does not exist"
            );
        }

        if ($downloadHandlerClass !== FileDownloadHandler::class
            && !is_subclass_of($downloadHandlerClass, FileDownloadHandler::class)) {
            throw new S3TransferException(
                "Download handler class `$downloadHandlerClass` must extend `FileDownloadHandler`"
            );
        }

        $config = $resumableDownload->getConfig();
        $downloadHandler = new $downloadHandlerClass(
            $resumableDownload->getDestination(),
            $config['fails_when_destination_exists'] ?? false,
            $config['resume_enabled'] ?? false,
            $resumableDownload->getTemporaryFile(),
            $resumableDownload->getFixedPartSize()
        );

        $progressTracker = $resumeDownloadRequest->getProgressTracker();
        $listeners = $resumeDownloadRequest->getListeners();

        if ($progressTracker === null
            && ($resumableDownload->getConfig()['track_progress']
                ?? $this->config->isTrackProgress())) {
            $progressTracker = new SingleProgressTracker();
            $listeners[] = $progressTracker;
        }

        $listenerNotifier = new TransferListenerNotifier(
            $listeners,
        );

        return $this->tryMultipartDownload(
            $resumableDownload->getRequestArgs(),
            $resumableDownload->getConfig(),
            $downloadHandler,
            $listenerNotifier,
            null,
            $resumableDownload,
        );
    }

    /**
     * @param ResumeUploadRequest $resumeUploadRequest
     *
     * @return PromiseInterface
     */
    public function resumeUpload(
        ResumeUploadRequest $resumeUploadRequest
    ): PromiseInterface
    {
        $resumableUpload = $resumeUploadRequest->getResumableUpload();
        if (is_string($resumableUpload)) {
            if (!AbstractResumableTransfer::isResumeFile($resumableUpload)) {
                throw new S3TransferException(
                    "Resume file `$resumableUpload` is not a valid resumable file."
                );
            }

            $resumableUpload = ResumableUpload::fromFile($resumableUpload);
        }

        // Verify that source file still exists
        if (!file_exists($resumableUpload->getSource())) {
            throw new S3TransferException(
                "Cannot resume upload: source file does not exist: "
                . $resumableUpload->getSource()
            );
        }

        // Verify if source still matches the same size
        $objectSizeAtFailure = $resumableUpload->getObjectSize();
        $currentObjectSize = filesize($resumableUpload->getSource());
        if ($objectSizeAtFailure !== $currentObjectSize) {
            throw new S3TransferException(
                "Cannot resume upload: source file size has changed since the upload failed. "
                . "Size at failure: {$objectSizeAtFailure}, current size: {$currentObjectSize}."
            );
        }

        // Verify upload still exists in S3 by checking uploadId
        $uploads = $this->s3Client->getPaginator(
            'ListMultipartUploads',
            [
                'Bucket' => $resumableUpload->getBucket(),
                'Prefix' => $resumableUpload->getKey(),
            ]
        )->search('Uploads[]');
        $uploadExists = false;
        foreach ($uploads as $upload) {
            if ($upload['UploadId'] === $resumableUpload->getUploadId()
                && $upload['Key'] === $resumableUpload->getKey()) {
                $uploadExists = true;
                break;
            }
        }

        if (!$uploadExists) {
            throw new S3TransferException(
                "Cannot resume upload: multipart upload no longer exists (UploadId: "
                . $resumableUpload->getUploadId() . ")"
            );
        }

        $config = $resumableUpload->getConfig();
        $progressTracker = $resumeUploadRequest->getProgressTracker();
        $listeners = $resumeUploadRequest->getListeners();

        if ($progressTracker === null
            && ($config['track_progress'] ?? $this->config->isTrackProgress())) {
            $progressTracker = new SingleProgressTracker();
            $listeners[] = $progressTracker;
        }

        $listenerNotifier = new TransferListenerNotifier($listeners);

        return (new MultipartUploader(
            $this->s3Client,
            $resumableUpload->getRequestArgs(),
            $resumableUpload->getSource(),
            $config,
            listenerNotifier: $listenerNotifier,
            resumableUpload: $resumableUpload,
        ))->promise();
    }

    /**
     * @param DownloadFileRequest $downloadFileRequest
     * @param S3ClientInterface|null $s3Client
     *
     * @return PromiseInterface
     */
    public function downloadFile(
        DownloadFileRequest $downloadFileRequest,
        ?S3ClientInterface $s3Client = null
    ): PromiseInterface
    {
        $client = $s3Client ?? $this->s3Client;
        return $this->downloadInternal($downloadFileRequest->getDownloadRequest(), $client);
    }

    /**
     * @param DownloadDirectoryRequest $downloadDirectoryRequest
     *
     * @return PromiseInterface
     */
    public function downloadDirectory(
        DownloadDirectoryRequest $downloadDirectoryRequest
    ): PromiseInterface
    {
        return (new DirectoryDownloader(
            $this->s3Client,
            $this->config->toArray(),
            fn(S3ClientInterface $client, DownloadFileRequest $request): PromiseInterface => $this->downloadFile($request, $client),
            $downloadDirectoryRequest,
        ))->promise();
    }

    /**
     * Tries an object multipart download.
     *
     * @param array $getObjectRequestArgs
     * @param array $config
     * @param AbstractDownloadHandler $downloadHandler
     * @param TransferListenerNotifier|null $listenerNotifier
     * @param S3ClientInterface|null $s3Client
     * @param ResumableDownload|null $resumableDownload
     * @return PromiseInterface
     */
    private function tryMultipartDownload(
        array                     $getObjectRequestArgs,
        array                     $config,
        AbstractDownloadHandler   $downloadHandler,
        ?TransferListenerNotifier $listenerNotifier = null,
        ?S3ClientInterface $s3Client = null,
        ?ResumableDownload $resumableDownload = null,
    ): PromiseInterface
    {
        $client = $s3Client ?? $this->s3Client;
        $downloaderClassName = AbstractMultipartDownloader::chooseDownloaderClass(
            strtolower($config['multipart_download_type'])
        );
        $multipartDownloader = new $downloaderClassName(
            $client,
            $getObjectRequestArgs,
            $config,
            $downloadHandler,
            listenerNotifier: $listenerNotifier,
            resumableDownload: $resumableDownload,
        );

        return $multipartDownloader->promise();
    }

    /**
     * @param string|StreamInterface $source
     * @param array $requestArgs
     * @param TransferListenerNotifier|null $listenerNotifier
     * @param S3ClientInterface|null $s3Client
     *
     * @return PromiseInterface
     */
    private function trySingleUpload(
        string|StreamInterface $source,
        array $requestArgs,
        ?TransferListenerNotifier $listenerNotifier = null,
        ?S3ClientInterface $s3Client = null
    ): PromiseInterface
    {
        $client = $s3Client ?? $this->s3Client;
        if (is_string($source) && is_readable($source)) {
            $requestArgs['SourceFile'] = $source;
            $objectSize = filesize($source);
        } elseif ($source instanceof StreamInterface && $source->isSeekable()) {
            $requestArgs['Body'] = $source;
            $objectSize = $source->getSize();
        } else {
            throw new S3TransferException(
                "Unable to process upload request due to the type of the source"
            );
        }

        if (!empty($listenerNotifier)) {
            $listenerNotifier->transferInitiated(
                [
                    AbstractTransferListener::REQUEST_ARGS_KEY => $requestArgs,
                    AbstractTransferListener::PROGRESS_SNAPSHOT_KEY => new TransferProgressSnapshot(
                        $requestArgs['Key'],
                        0,
                        $objectSize,
                    ),
                ]
            );

            $command = $client->getCommand('PutObject', $requestArgs);
            return $client->executeAsync($command)->then(
                function (ResultInterface $result)
                use ($objectSize, $listenerNotifier, $requestArgs) {
                    $listenerNotifier->bytesTransferred(
                        [
                            AbstractTransferListener::REQUEST_ARGS_KEY => $requestArgs,
                            AbstractTransferListener::PROGRESS_SNAPSHOT_KEY => new TransferProgressSnapshot(
                                $requestArgs['Key'],
                                $objectSize,
                                $objectSize,
                            ),
                        ]
                    );

                    $listenerNotifier->transferComplete(
                        [
                            AbstractTransferListener::REQUEST_ARGS_KEY => $requestArgs,
                            AbstractTransferListener::PROGRESS_SNAPSHOT_KEY => new TransferProgressSnapshot(
                                $requestArgs['Key'],
                                $objectSize,
                                $objectSize,
                                $result->toArray()
                            ),
                        ]
                    );

                    return new UploadResult(
                        $result->toArray()
                    );
                }
            )->otherwise(function (Throwable $reason)
            use ($objectSize, $requestArgs, $listenerNotifier) {
                $listenerNotifier->transferFail(
                    [
                        AbstractTransferListener::REQUEST_ARGS_KEY => $requestArgs,
                        AbstractTransferListener::PROGRESS_SNAPSHOT_KEY => new TransferProgressSnapshot(
                            $requestArgs['Key'],
                            0,
                            $objectSize,
                        ),
                        'reason' => $reason,
                    ]
                );

                throw $reason;
            });
        }

        $command = $client->getCommand('PutObject', $requestArgs);

        return $client->executeAsync($command)
            ->then(function (ResultInterface $result) {
                return new UploadResult($result->toArray());
            });
    }

    /**
     * @param UploadRequest $uploadRequest
     * @param S3ClientInterface|null $s3Client
     * @param TransferListenerNotifier|null $listenerNotifier
     *
     * @return PromiseInterface
     */
    private function tryMultipartUpload(
        UploadRequest $uploadRequest,
        ?S3ClientInterface $s3Client = null,
        ?TransferListenerNotifier $listenerNotifier = null,
    ): PromiseInterface
    {
        $client = $s3Client ?? $this->s3Client;
        return (new MultipartUploader(
            $client,
            $uploadRequest->getUploadRequestArgs(),
            $uploadRequest->getSource(),
            $uploadRequest->getConfig(),
            listenerNotifier: $listenerNotifier,
        ))->promise();
    }

    /**
     * @param string|StreamInterface $source
     * @param int $mupThreshold
     *
     * @return bool
     */
    private function requiresMultipartUpload(
        string|StreamInterface $source,
        int $mupThreshold
    ): bool
    {
        if (is_string($source) && is_readable($source)) {
            return filesize($source) >= $mupThreshold;
        } elseif ($source instanceof StreamInterface) {
            // When the stream's size is unknown then we could try a multipart upload.
            if (empty($source->getSize())) {
                return true;
            }

            return $source->getSize() >= $mupThreshold;
        }

        throw new S3TransferException(
            "Unable to determine if a multipart is required"
        );
    }

    /**
     * Returns a default instance of S3Client.
     *
     * @return S3Client
     */
    private function defaultS3Client(): S3ClientInterface
    {
        $defaultRegion = $this->config->getDefaultRegion();
        if (empty($defaultRegion)) {
            throw new S3TransferException(
                "When using the default S3 Client you must define a default region."
                . "\nThe config parameter is `default_region`.`"
            );
        }

        return new S3Client([
            'region' => $defaultRegion,
        ]);
    }

    /**
     * Validates a string value is a valid S3 URI.
     * Valid S3 URI Example: S3://mybucket.dev/myobject.txt
     *
     * @param string $uri
     *
     * @return bool
     */
    public static function isValidS3URI(string $uri): bool
    {
        // in the expression `substr($uri, 5)))` the 5 belongs to the size of `s3://`.
        return str_starts_with(strtolower($uri), 's3://')
            && count(explode('/', substr($uri, 5))) > 1;
    }

    /**
     * Converts a S3 URI into an array with a Bucket and Key
     * properties set.
     *
     * @param string $uri: The S3 URI.
     *
     * @return array
     */
    public static function s3UriAsBucketAndKey(string $uri): array
    {
        $errorMessage = "Invalid URI: `$uri` provided. \nA valid S3 URI looks as `s3://bucket/key`";
        if (!self::isValidS3URI($uri)) {
            throw new InvalidArgumentException($errorMessage);
        }

        $path = substr($uri, 5); // without s3://
        $parts = explode('/', $path, 2);

        if (count($parts) < 2) {
            throw new InvalidArgumentException($errorMessage);
        }

        return [
            'Bucket' => $parts[0],
            'Key' => $parts[1],
        ];
    }

}
