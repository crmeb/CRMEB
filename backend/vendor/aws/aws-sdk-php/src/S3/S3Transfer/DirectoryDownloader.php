<?php

namespace Aws\S3\S3Transfer;

use Aws\MetricsBuilder;
use Aws\S3\S3ClientInterface;
use Aws\S3\S3Transfer\Exception\S3TransferException;
use Aws\S3\S3Transfer\Models\DownloadDirectoryRequest;
use Aws\S3\S3Transfer\Models\DownloadDirectoryResult;
use Aws\S3\S3Transfer\Models\DownloadFileRequest;
use Aws\S3\S3Transfer\Models\DownloadRequest;
use Aws\S3\S3Transfer\Progress\DirectoryProgressTracker;
use Aws\S3\S3Transfer\Progress\DirectoryTransferProgressAggregator;
use Closure;
use GuzzleHttp\Promise\Each;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\PromisorInterface;
use Throwable;
use function Aws\filter;
use function Aws\map;

final class DirectoryDownloader implements PromisorInterface
{
    /** @var S3ClientInterface */
    private S3ClientInterface $s3Client;

    /** @var array */
    private array $config;

    /** @var Closure */
    private Closure $downloadFile;

    /** @var int */
    private int $objectsDownloaded = 0;

    /** @var int */
    private int $objectsFailed = 0;

    /** @var DownloadDirectoryRequest */
    private DownloadDirectoryRequest $downloadDirectoryRequest;

    /**
     * @param S3ClientInterface $s3Client
     * @param array $config
     * @param Closure $downloadFile A closure that receives (S3ClientInterface, DownloadFileRequest) and returns PromiseInterface
     * @param DownloadDirectoryRequest $downloadDirectoryRequest
     */
    public function __construct(
        S3ClientInterface $s3Client,
        array $config,
        Closure $downloadFile,
        DownloadDirectoryRequest $downloadDirectoryRequest
    ) {
        $this->s3Client = $s3Client;
        $this->config = $config;
        $this->downloadFile = $downloadFile;
        $this->downloadDirectoryRequest = $downloadDirectoryRequest;

        // Validations
        $this->downloadDirectoryRequest->updateConfigWithDefaults(
            $this->config
        );
        $this->downloadDirectoryRequest->validateConfig();
        $this->downloadDirectoryRequest->validateDestinationDirectory();

        MetricsBuilder::appendMetricsCaptureMiddleware(
            $this->s3Client->getHandlerList(),
            MetricsBuilder::S3_TRANSFER_DOWNLOAD_DIRECTORY
        );
    }

    /**
     * @return PromiseInterface
     *
     * @throws Throwable
     */
    public function promise(): PromiseInterface
    {
        $this->objectsDownloaded = 0;
        $this->objectsFailed = 0;

        $destinationDirectory = $this->downloadDirectoryRequest->getDestinationDirectory();
        $sourceBucket = $this->downloadDirectoryRequest->getSourceBucket();
        $progressTracker = $this->downloadDirectoryRequest->getProgressTracker();

        $config = $this->downloadDirectoryRequest->getConfig();
        if ($progressTracker === null && $config['track_progress']) {
            $progressTracker = new DirectoryProgressTracker();
        }

        $listArgs = [
                'Bucket' => $sourceBucket,
            ]  + ($config['list_objects_v2_args'] ?? []);

        $s3Prefix = $config['s3_prefix'] ?? null;
        if (empty($listArgs['Prefix']) && $s3Prefix !== null) {
            $listArgs['Prefix'] = $s3Prefix;
        }

        // MUST BE NULL
        $listArgs['Delimiter'] = null;

        $objects = $this->s3Client
            ->getPaginator('ListObjectsV2', $listArgs)
            ->search('Contents[]');

        $filter = $config['filter'] ?? null;
        $objects = filter($objects, function (array $object) use ($filter) {
            $key = $object['Key'] ?? '';
            if ($filter !== null) {
                return call_user_func($filter, $key) && !str_ends_with($key, "/");
            }

            return !str_ends_with($key, "/");
        });
        $objects = map($objects, function (array $object) use ($sourceBucket) {
            return  [
                'uri' => self::formatAsS3URI($sourceBucket, $object['Key']),
                'size' => $object['Size'] ?? 0,
            ];
        });

        $downloadObjectRequestModifier = $config['download_object_request_modifier']
            ?? null;
        $failurePolicyCallback = $config['failure_policy'] ?? null;

        $directoryListeners = $this->downloadDirectoryRequest->getListeners();
        $singleObjectListeners = $this->downloadDirectoryRequest->getSingleObjectListeners();
        $aggregator = new DirectoryTransferProgressAggregator(
            identifier: $this->buildDirectoryIdentifier(
                $sourceBucket,
                $destinationDirectory,
                $s3Prefix
            ),
            totalBytes: 0,
            totalFiles: 0,
            directoryListeners: $directoryListeners,
            directoryProgressTracker: $progressTracker
        );

        $maxConcurrency = $config['max_concurrency']
            ?? DownloadDirectoryRequest::DEFAULT_MAX_CONCURRENCY;

        $aggregator->notifyDirectoryInitiated([
            'bucket' => $sourceBucket,
            'destination_directory' => $destinationDirectory,
            's3_prefix' => $s3Prefix,
        ]);

        return Each::ofLimitAll(
            $this->createDownloadPromises(
                $objects,
                $config,
                $destinationDirectory,
                $sourceBucket,
                $s3Prefix,
                $downloadObjectRequestModifier,
                $failurePolicyCallback,
                $aggregator,
                $singleObjectListeners
            ),
            $maxConcurrency
        )->then(function () use ($aggregator) {
            $aggregator->notifyDirectoryComplete([
                'objects_downloaded' => $this->objectsDownloaded,
                'objects_failed' => $this->objectsFailed,
            ]);
            return new DownloadDirectoryResult(
                $this->objectsDownloaded,
                $this->objectsFailed
            );
        })->otherwise(function (Throwable $reason) use ($aggregator) {
            $aggregator->notifyDirectoryFail($reason);
            return new DownloadDirectoryResult(
                $this->objectsDownloaded,
                $this->objectsFailed,
                $reason
            );
        });
    }

    /**
     * @param iterable $objects
     * @param array $config
     * @param string $destinationDirectory
     * @param string $sourceBucket
     * @param string|null $s3Prefix
     * @param callable|null $downloadObjectRequestModifier
     * @param callable|null $failurePolicyCallback
     * @param DirectoryTransferProgressAggregator $aggregator
     * @param array $singleObjectListeners
     *
     * @return \Generator
     * @throws Throwable
     */
    private function createDownloadPromises(
        iterable $objects,
        array $config,
        string $destinationDirectory,
        string $sourceBucket,
        ?string $s3Prefix,
        ?callable $downloadObjectRequestModifier,
        ?callable $failurePolicyCallback,
        DirectoryTransferProgressAggregator $aggregator,
        array $singleObjectListeners
    ): \Generator
    {
        $s3Delimiter = '/';
        foreach ($objects as $object) {
            $aggregator->incrementTotals($object['size'] ?? 0);
            $bucketAndKeyArray = S3TransferManager::s3UriAsBucketAndKey($object['uri']);
            $objectKey = $bucketAndKeyArray['Key'];
            if ($s3Prefix !== null && str_contains($objectKey, $s3Delimiter)) {
                $prefixToStrip = str_ends_with($s3Prefix, $s3Delimiter)
                    ? $s3Prefix
                    : $s3Prefix . $s3Delimiter;
                $objectKey = substr($objectKey, strlen($prefixToStrip));
            }

            // CONVERT THE KEY DIR SEPARATOR TO OS BASED DIR SEPARATOR
            if (DIRECTORY_SEPARATOR !== $s3Delimiter) {
                $objectKey = str_replace(
                    $s3Delimiter,
                    DIRECTORY_SEPARATOR,
                    $objectKey
                );
            }

            $destinationFile = $destinationDirectory . DIRECTORY_SEPARATOR . $objectKey;
            if ($this->resolvesOutsideTargetDirectory($destinationFile, $objectKey)) {
                throw new S3TransferException(
                    "Cannot download key $objectKey "
                    ."its relative path resolves outside the parent directory."
                );
            }

            $requestArgs = $this->downloadDirectoryRequest->getDownloadRequestArgs();
            foreach ($bucketAndKeyArray as $key => $value) {
                $requestArgs[$key] = $value;
            }
            if ($downloadObjectRequestModifier !== null) {
                call_user_func($downloadObjectRequestModifier, $requestArgs);
            }

            $downloadFile = $this->downloadFile;
            $downloadConfig = $config;
            $downloadConfig['track_progress'] = false;
            yield $downloadFile(
                $this->s3Client,
                new DownloadFileRequest(
                    destination: $destinationFile,
                    failsWhenDestinationExists: $config['fails_when_destination_exists'] ?? false,
                    downloadRequest: new DownloadRequest(
                        source: null, // Source has been provided in the request args
                        downloadRequestArgs: $requestArgs,
                        config: array_merge(
                            $downloadConfig,
                            [
                                'target_part_size_bytes' => $config['target_part_size_bytes'] ?? 0,
                            ]
                        ),
                        downloadHandler: null,
                        listeners: array_merge(
                            [$aggregator],
                            array_map(
                                fn($listener) => clone $listener,
                                $singleObjectListeners
                            )
                        ),
                        progressTracker: null
                    )
                ),
            )->then(function () {
                $this->objectsDownloaded++;
            })->otherwise(function (Throwable $reason) use (
                $sourceBucket,
                $destinationDirectory,
                $failurePolicyCallback,
                $requestArgs
            ) {
                $this->objectsFailed++;
                if ($failurePolicyCallback !== null) {
                    call_user_func(
                        $failurePolicyCallback,
                        $requestArgs,
                        [
                            "destination_directory" => $destinationDirectory,
                            "bucket" => $sourceBucket,
                        ],
                        $reason,
                        new DownloadDirectoryResult(
                            $this->objectsDownloaded,
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
     * @param string $bucket
     * @param string $key
     *
     * @return string
     */
    private static function formatAsS3URI(string $bucket, string $key): string
    {
        return "s3://$bucket/$key";
    }

    /**
     * @param string $sink
     * @param string $objectKey
     *
     * @return bool
     */
    private function resolvesOutsideTargetDirectory(
        string $sink,
        string $objectKey
    ): bool
    {
        $resolved = [];
        $sections = explode(DIRECTORY_SEPARATOR, $sink);
        $targetSectionsLength = count(explode(DIRECTORY_SEPARATOR, $objectKey));
        $targetSections = array_slice($sections, -($targetSectionsLength + 1));
        $targetDirectory = $targetSections[0];

        foreach ($targetSections as $section) {
            if ($section === '.' || $section === '') {
                continue;
            }
            if ($section === '..') {
                array_pop($resolved);
                if (empty($resolved) || $resolved[0] !== $targetDirectory) {
                    return true;
                }
            } else {
                $resolved []= $section;
            }
        }

        return false;
    }

    /**
     * @param string $bucket
     * @param string $destinationDirectory
     * @param string|null $s3Prefix
     *
     * @return string
     */
    private function buildDirectoryIdentifier(
        string $bucket,
        string $destinationDirectory,
        ?string $s3Prefix
    ): string {
        return sprintf(
            'download:%s/%s->%s',
            $bucket,
            $s3Prefix ?? '',
            rtrim($destinationDirectory, DIRECTORY_SEPARATOR)
        );
    }
}
