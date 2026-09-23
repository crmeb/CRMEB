<?php

namespace Aws\S3\S3Transfer;

use Aws\MetricsBuilder;
use Aws\S3\S3Transfer\Exception\S3TransferException;
use Aws\S3\S3Transfer\Models\UploadDirectoryRequest;
use Aws\S3\S3Transfer\Models\UploadDirectoryResult;
use Aws\S3\S3Transfer\Models\UploadRequest;
use Aws\S3\S3Transfer\Models\UploadResult;
use Aws\S3\S3Transfer\Progress\DirectoryProgressTracker;
use Aws\S3\S3Transfer\Progress\DirectoryTransferProgressAggregator;
use Aws\S3\S3ClientInterface;
use Closure;
use FilesystemIterator;
use GuzzleHttp\Promise\Each;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\PromisorInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;
use function Aws\filter;

final class DirectoryUploader implements PromisorInterface
{
    /** @var array */
    private array $config;

    /** @var S3ClientInterface */
    private S3ClientInterface $s3Client;

    /** @var Closure */
    private Closure $uploadObject;

    /** @var int */
    private int $objectsUploaded = 0;

    /** @var int */
    private int $objectsFailed = 0;

    /** @var UploadDirectoryRequest */
    private UploadDirectoryRequest $uploadDirectoryRequest;

    /**
     * @param S3ClientInterface $s3Client
     * @param array $config
     * @param Closure $uploadObject A closure that receives
     *  (S3ClientInterface, UploadRequest) and returns PromiseInterface
     * @param UploadDirectoryRequest $uploadDirectoryRequest
     */
    public function __construct(
        S3ClientInterface $s3Client,
        array $config,
        Closure $uploadObject,
        UploadDirectoryRequest $uploadDirectoryRequest
    ) {
        $this->s3Client = $s3Client;
        $this->config = $config;
        $this->uploadObject = $uploadObject;
        $this->uploadDirectoryRequest = $uploadDirectoryRequest;

        // Validations
        $this->uploadDirectoryRequest->updateConfigWithDefaults(
            $this->config
        );
        $this->uploadDirectoryRequest->validateSourceDirectory();
        $this->uploadDirectoryRequest->validateConfig();

        MetricsBuilder::appendMetricsCaptureMiddleware(
            $this->s3Client->getHandlerList(),
            MetricsBuilder::S3_TRANSFER_UPLOAD_DIRECTORY
        );
    }

    /**
     * @return PromiseInterface
     *
     * @throws Throwable
     */
    public function promise(): PromiseInterface
    {
        $this->objectsUploaded = 0;
        $this->objectsFailed = 0;

        $config = $this->uploadDirectoryRequest->getConfig();

        $filter = $config['filter'] ?? null;
        $uploadObjectRequestModifier = $config['upload_object_request_modifier']
            ?? null;
        $failurePolicyCallback = $config['failure_policy'] ?? null;

        $sourceDirectory = $this->uploadDirectoryRequest->getSourceDirectory();
        $files = $this->iterateSourceFiles(
            $sourceDirectory,
            $config,
            $filter
        );

        $baseDir = rtrim($sourceDirectory, '/') . DIRECTORY_SEPARATOR;
        $delimiter = $config['s3_delimiter'] ?? '/';
        $s3Prefix = $config['s3_prefix'] ?? '';
        if ($s3Prefix !== '' && !str_ends_with($s3Prefix, '/')) {
            $s3Prefix .= '/';
        }

        $targetBucket = $this->uploadDirectoryRequest->getTargetBucket();

        $directoryProgressTracker = $this->uploadDirectoryRequest->getProgressTracker();
        if ($directoryProgressTracker === null
            && ($config['track_progress']
                ?? ($this->config['track_progress'] ?? false))) {
            $directoryProgressTracker = new DirectoryProgressTracker();
        }

        $directoryListeners = $this->uploadDirectoryRequest->getListeners();
        $singleObjectListeners = $this->uploadDirectoryRequest->getSingleObjectListeners();
        $aggregator = new DirectoryTransferProgressAggregator(
            identifier: $this->buildDirectoryIdentifier(
                $sourceDirectory,
                $targetBucket,
                $s3Prefix
            ),
            totalBytes: 0,
            totalFiles: 0,
            directoryListeners: $directoryListeners,
            directoryProgressTracker: $directoryProgressTracker,
        );

        $maxConcurrency = $config['max_concurrency']
            ?? UploadDirectoryRequest::DEFAULT_MAX_CONCURRENCY;

        $aggregator->notifyDirectoryInitiated([
            'source_directory' => $sourceDirectory,
            'bucket' => $targetBucket,
            's3_prefix' => $s3Prefix,
        ]);

        return Each::ofLimitAll(
            $this->createUploadPromises(
                $files,
                $config,
                $uploadObjectRequestModifier,
                $failurePolicyCallback,
                $sourceDirectory,
                $targetBucket,
                $baseDir,
                $delimiter,
                $s3Prefix,
                $aggregator,
                $singleObjectListeners
            ),
            $maxConcurrency
        )->then(function () use ($aggregator) {
            $aggregator->notifyDirectoryComplete([
                'objects_uploaded' => $this->objectsUploaded,
                'objects_failed' => $this->objectsFailed,
            ]);
            return new UploadDirectoryResult(
                $this->objectsUploaded,
                $this->objectsFailed
            );
        })->otherwise(function (Throwable $reason) use ($aggregator) {
            $aggregator->notifyDirectoryFail($reason);
            return new UploadDirectoryResult(
                $this->objectsUploaded,
                $this->objectsFailed,
                $reason
            );
        });
    }

    /**
     * @param iterable $files
     * @param array $config
     * @param callable|null $uploadObjectRequestModifier
     * @param callable|null $failurePolicyCallback
     * @param string $sourceDirectory
     * @param string $targetBucket
     * @param string $baseDir
     * @param string $delimiter
     * @param string $s3Prefix
     * @param DirectoryTransferProgressAggregator $aggregator
     * @param array $singleObjectListeners
     *
     * @return \Generator
     * @throws Throwable
     */
    private function createUploadPromises(
        iterable $files,
        array $config,
        ?callable $uploadObjectRequestModifier,
        ?callable $failurePolicyCallback,
        string $sourceDirectory,
        string $targetBucket,
        string $baseDir,
        string $delimiter,
        string $s3Prefix,
        DirectoryTransferProgressAggregator $aggregator,
        array $singleObjectListeners
    ): \Generator
    {
        foreach ($files as $file) {
            $fileSize = filesize($file);
            $aggregator->incrementTotals(
                $fileSize !== false ? $fileSize : 0
            );

            $relativePath = substr($file, strlen($baseDir));
            if (str_contains($relativePath, $delimiter) && $delimiter !== '/') {
                throw new S3TransferException(
                    "The filename `$relativePath` must not contain the provided delimiter `$delimiter`"
                );
            }

            $objectKey = $s3Prefix.$relativePath;
            $objectKey = str_replace(
                DIRECTORY_SEPARATOR,
                $delimiter,
                $objectKey
            );
            $uploadRequestArgs = $this->uploadDirectoryRequest->getUploadRequestArgs();
            $uploadRequestArgs['Bucket'] = $targetBucket;
            $uploadRequestArgs['Key'] = $objectKey;

            if ($uploadObjectRequestModifier !== null) {
                $uploadObjectRequestModifier($uploadRequestArgs);
            }

            $uploadObject = $this->uploadObject;
            $uploadConfig = $config;
            $uploadConfig['track_progress'] = false;
            yield $uploadObject(
                $this->s3Client,
                new UploadRequest(
                    $file,
                    $uploadRequestArgs,
                    $uploadConfig,
                    listeners: array_merge(
                        [$aggregator],
                        array_map(
                            fn($listener) => clone $listener,
                            $singleObjectListeners
                        )
                    ),
                    progressTracker: null
                )
            )->then(function (UploadResult $response) {
                $this->objectsUploaded++;

                return $response;
            })->otherwise(function (Throwable $reason) use (
                $targetBucket,
                $sourceDirectory,
                $failurePolicyCallback,
                $uploadRequestArgs
            ) {
                $this->objectsFailed++;
                if($failurePolicyCallback !== null) {
                    call_user_func(
                        $failurePolicyCallback,
                        $uploadRequestArgs,
                        [
                            "source_directory" => $sourceDirectory,
                            "bucket_to" => $targetBucket,
                        ],
                        $reason,
                        new UploadDirectoryResult(
                            $this->objectsUploaded,
                            $this->objectsFailed
                        )
                    );

                    return;
                }

                throw $reason;
            });
        }
    }

    /**
     * Iterate source files applying traversal config and filter.
     *
     * @param string $sourceDirectory
     * @param array $config
     * @param callable|null $filter
     *
     * @return \Generator
     */
    private function iterateSourceFiles(
        string $sourceDirectory,
        array $config,
        ?callable $filter
    ): \Generator {
        $dirIterator = new RecursiveDirectoryIterator($sourceDirectory);

        $flags = FilesystemIterator::SKIP_DOTS;
        if ($config['follow_symbolic_links'] ?? false) {
            $flags |= FilesystemIterator::FOLLOW_SYMLINKS;
        }

        $dirIterator->setFlags($flags);

        if ($config['recursive'] ?? false) {
            $dirIterator = new RecursiveIteratorIterator(
                $dirIterator,
                RecursiveIteratorIterator::SELF_FIRST
            );
            if (isset($config['max_depth'])) {
                $dirIterator->setMaxDepth($config['max_depth']);
            }
        }

        $dirVisited = [];
        $files = filter(
            $dirIterator,
            function ($file) use ($filter, &$dirVisited) {
                if (is_dir($file)) {
                    // To avoid circular symbolic links traversal
                    $dirRealPath = realpath($file);
                    if ($dirRealPath !== false) {
                        if ($dirVisited[$dirRealPath] ?? false) {
                            throw new S3TransferException(
                                "A circular symbolic link traversal has been detected at $file -> $dirRealPath"
                            );
                        }

                        $dirVisited[$dirRealPath] = true;
                    }
                }

                if ($filter !== null) {
                    return !is_dir($file) && $filter($file);
                }

                return !is_dir($file);
            }
        );

        foreach ($files as $file) {
            yield $file;
        }
    }

    /**
     * @param string $sourceDirectory
     * @param string $bucket
     * @param string $s3Prefix
     *
     * @return string
     */
    private function buildDirectoryIdentifier(
        string $sourceDirectory,
        string $bucket,
        string $s3Prefix
    ): string {
        return sprintf(
            'upload:%s->%s/%s',
            rtrim($sourceDirectory, DIRECTORY_SEPARATOR),
            $bucket,
            $s3Prefix
        );
    }
}
