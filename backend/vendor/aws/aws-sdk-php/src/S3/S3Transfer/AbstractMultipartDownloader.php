<?php
namespace Aws\S3\S3Transfer;

use Aws\CommandInterface;
use Aws\ResultInterface;
use Aws\S3\S3ClientInterface;
use Aws\S3\S3Transfer\Exception\S3TransferException;
use Aws\S3\S3Transfer\Models\DownloadResult;
use Aws\S3\S3Transfer\Models\ResumableDownload;
use Aws\S3\S3Transfer\Models\S3TransferManagerConfig;
use Aws\S3\S3Transfer\Progress\AbstractTransferListener;
use Aws\S3\S3Transfer\Progress\TransferListenerNotifier;
use Aws\S3\S3Transfer\Progress\TransferProgressSnapshot;
use Aws\S3\S3Transfer\Utils\ResumableDownloadHandlerInterface;
use Aws\S3\S3Transfer\Utils\AbstractDownloadHandler;
use Aws\S3\S3Transfer\Utils\StreamDownloadHandler;
use GuzzleHttp\Promise\Coroutine;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\Each;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\PromisorInterface;
use Throwable;

abstract class AbstractMultipartDownloader implements PromisorInterface
{
    public const GET_OBJECT_COMMAND = "GetObject";
    public const PART_GET_MULTIPART_DOWNLOADER = "part";
    public const RANGED_GET_MULTIPART_DOWNLOADER = "ranged";
    private const OBJECT_SIZE_REGEX = "/\/(\d+)$/";
    private const RANGE_TO_REGEX = "/(\d+)\//";

    /** @var array */
    protected readonly array $downloadRequestArgs;

    /** @var array */
    protected readonly array $config;

    /** @var AbstractDownloadHandler */
    private AbstractDownloadHandler $downloadHandler;

    /** @var int */
    protected int $currentPartNo;

    /** @var int */
    protected int $objectPartsCount;

    /** @var int */
    protected int $objectSizeInBytes;

    /** @var string|null */
    protected ?string $eTag;

    /** @var TransferListenerNotifier|null */
    private readonly ?TransferListenerNotifier $listenerNotifier;

    /** Tracking Members */
    private ?TransferProgressSnapshot $currentSnapshot;

    /** @var array */
    private array $partsCompleted;

    /** @var ResumableDownload|null */
    private ?ResumableDownload $resumableDownload;

    /** @var bool Whether this is a resumed download */
    private readonly bool $isResuming;

    /** @var array|null Initial request response for resume state */
    private ?array $initialRequestResult = null;

    /**
     * @param S3ClientInterface $s3Client
     * @param array $downloadRequestArgs
     * @param array $config
     * @param ?AbstractDownloadHandler $downloadHandler
     * @param array $partsCompleted
     * @param int $objectPartsCount
     * @param int $objectSizeInBytes
     * @param string|null $eTag
     * @param TransferProgressSnapshot|null $currentSnapshot
     * @param TransferListenerNotifier|null $listenerNotifier
     * @param ResumableDownload|null $resumableDownload
     */
    public function __construct(
        protected readonly S3ClientInterface $s3Client,
        array $downloadRequestArgs,
        array $config = [],
        ?AbstractDownloadHandler $downloadHandler = null,
        array $partsCompleted = [],
        int $objectPartsCount = 0,
        int $objectSizeInBytes = 0,
        ?string $eTag = null,
        ?TransferProgressSnapshot $currentSnapshot = null,
        ?TransferListenerNotifier $listenerNotifier = null,
        ?ResumableDownload $resumableDownload = null
    ) {
        $this->resumableDownload = $resumableDownload;
        $this->isResuming = $resumableDownload !== null;
        // Initialize from resume state if available
        if ($this->isResuming) {
            $this->objectPartsCount = $resumableDownload->getTotalNumberOfParts();
            $this->objectSizeInBytes = $resumableDownload->getObjectSizeInBytes();
            $this->eTag = $resumableDownload->getETag();
            $this->partsCompleted = $resumableDownload->getPartsCompleted();
            $this->initialRequestResult = $this->resumableDownload->getInitialRequestResult();
            // Restore current snapshot
            $snapshotData = $resumableDownload->getCurrentSnapshot();
            if (!empty($snapshotData)) {
                $this->currentSnapshot = TransferProgressSnapshot::fromArray(
                    $snapshotData
                );
            }
        } else {
            $this->partsCompleted = $partsCompleted;
            $this->objectPartsCount = $objectPartsCount;
            $this->objectSizeInBytes = $objectSizeInBytes;
            $this->eTag = $eTag;
            $this->currentSnapshot = $currentSnapshot;
        }

        $this->downloadRequestArgs = $downloadRequestArgs;
        $this->validateConfig($config);
        $this->config = $config;
        if ($downloadHandler === null) {
            $downloadHandler = new StreamDownloadHandler();
        }
        $this->downloadHandler = $downloadHandler;
        $this->listenerNotifier  = $listenerNotifier;
        // Always starts in 1
        $this->currentPartNo = 1;
    }

    /**
     * Returns the next command args for fetching the next object part.
     *
     * @return array
     */
    abstract protected function getFetchCommandArgs(): array;

    /**
     * Compute the object dimensions, such as size and parts count.
     *
     * @param ResultInterface $result
     *
     * @return void
     */
    abstract protected function computeObjectDimensions(ResultInterface $result): void;

    private function validateConfig(array &$config): void
    {
        if (!isset($config['target_part_size_bytes'])) {
            $config['target_part_size_bytes'] = S3TransferManagerConfig::DEFAULT_TARGET_PART_SIZE_BYTES;
        }

        if (!isset($config['concurrency'])) {
            $config['concurrency'] = S3TransferManagerConfig::DEFAULT_CONCURRENCY;
        }

        if (!isset($config['response_checksum_validation'])) {
            $config['response_checksum_validation'] = S3TransferManagerConfig::DEFAULT_RESPONSE_CHECKSUM_VALIDATION;
        }

        if (!isset($config['resume_enabled'])) {
            $config['resume_enabled'] = false;
        }
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @return int
     */
    public function getCurrentPartNo(): int
    {
        return $this->currentPartNo;
    }

    /**
     * @return int
     */
    public function getObjectPartsCount(): int
    {
        return $this->objectPartsCount;
    }

    /**
     * @return int
     */
    public function getObjectSizeInBytes(): int
    {
        return $this->objectSizeInBytes;
    }

    /**
     * @return TransferProgressSnapshot
     */
    public function getCurrentSnapshot(): TransferProgressSnapshot
    {
        return $this->currentSnapshot;
    }

    /**
     * @return DownloadResult
     */
    public function download(): DownloadResult
    {
        return $this->promise()->wait();
    }

    /**
     * Returns that resolves a multipart download operation,
     * or to a rejection in case of any failures.
     *
     * @return PromiseInterface
     */
    public function promise(): PromiseInterface
    {
        return Coroutine::of(function () {
            // Skip initial request if resuming (we already have object dimensions)
            if ($this->isResuming) {
                $this->downloadInitiated($this->downloadRequestArgs);
            } else {
                yield $this->initialRequest();
            }

            $partsDownloadPromises = $this->partDownloadRequests();

            // When concurrency is not supported by the download handler
            // Then the number of concurrency will be just one.
            $concurrency = $this->downloadHandler->isConcurrencySupported()
                ? $this->config['concurrency']
                : 1;

            yield Each::ofLimitAll(
                $partsDownloadPromises,
                $concurrency,
            )->then(function () {
                // Transfer completed
                $this->downloadComplete();

                return Create::promiseFor(new DownloadResult(
                    $this->downloadHandler->getHandlerResult(),
                    $this->initialRequestResult,
                ));
            })->otherwise(function (Throwable $e) {
                $this->downloadFailed($e);

                throw $e;
            });
        });
    }

    /**
     * Perform the initial download request.
     *
     * @return PromiseInterface
     */
    protected function initialRequest(): PromiseInterface
    {
        $command = $this->getNextGetObjectCommand();
        // Notify download initiated
        $this->downloadInitiated($command->toArray());

        return $this->s3Client->executeAsync($command)
            ->then(function (ResultInterface $result) use ($command) {
                // Compute object dimensions such as parts count and object size
                $this->computeObjectDimensions($result);

                // If there are more than one part then save the ETag
                if ($this->objectPartsCount > 1) {
                    $this->eTag = $result['ETag'];
                }

                $initialRequestResult = $result->toArray();
                // Set full object size
                $initialRequestResult['ContentLength'] = $this->objectSizeInBytes;
                // Set full object content range
                $initialRequestResult['ContentRange'] = "0-"
                    . ($this->objectSizeInBytes - 1)
                    . "/"
                    . $this->objectSizeInBytes;

                // Remove unnecessary fields
                unset($initialRequestResult['Body']);
                unset($initialRequestResult['@metadata']);

                // Store initial response for resume state
                $this->initialRequestResult = $initialRequestResult;

                // Notify listeners but we pass the actual request result
                $this->partDownloadCompleted(
                    1,
                    $result->toArray(),
                    $command->toArray()
                );
            })->otherwise(function ($reason)  {
                $this->partDownloadFailed($reason);

                throw $reason;
            });
    }

    /**
     * @return \Generator
     */
    private function partDownloadRequests(): \Generator
    {
        while ($this->currentPartNo < $this->objectPartsCount) {
            $this->currentPartNo++;
            if ($this->partsCompleted[$this->currentPartNo] ?? false) {
                continue;
            }

            $partNumber = $this->currentPartNo;
            $command = $this->getNextGetObjectCommand();

            yield $this->s3Client->executeAsync($command)
                ->then(function (ResultInterface $result)
                use ($command, $partNumber) {
                    $requestArgs = $command->toArray();

                    // Remove metadata
                    unset($result['@metadata']);

                    $this->partDownloadCompleted(
                        $partNumber,
                        $result->toArray(),
                        $requestArgs
                    );
                });
        }

        if ($this->currentPartNo !== $this->objectPartsCount) {
            throw new S3TransferException(
                "Expected number of parts `$this->objectPartsCount`"
                . " to have been transferred but got `$this->currentPartNo`."
            );
        }
    }

    /**
     * @return CommandInterface
     */
    private function getNextGetObjectCommand(): CommandInterface
    {
        $nextCommandArgs = $this->getFetchCommandArgs();
        if ($this->config['response_checksum_validation'] === 'when_supported') {
            $nextCommandArgs['ChecksumMode'] = 'ENABLED';
        }

        if (!empty($this->eTag)) {
            $nextCommandArgs['IfMatch'] = $this->eTag;
        }

        return $this->s3Client->getCommand(
            self::GET_OBJECT_COMMAND,
            $nextCommandArgs
        );
    }

    /**
     * Main purpose of this method is to propagate
     * the download-initiated event to listeners, but
     * also it does some computation regarding internal states
     * that need to be maintained.
     *
     * @param array $commandArgs
     *
     * @return void
     */
    private function downloadInitiated(array $commandArgs): void
    {
        if ($this->currentSnapshot === null) {
            $this->currentSnapshot = new TransferProgressSnapshot(
                $commandArgs['Key'],
                0,
                $this->objectSizeInBytes
            );
        } else {
            $this->currentSnapshot = new TransferProgressSnapshot(
                $this->currentSnapshot->getIdentifier(),
                $this->currentSnapshot->getTransferredBytes(),
                $this->currentSnapshot->getTotalBytes(),
                $this->currentSnapshot->getResponse()
            );
        }

        // Prepare context
        $context = [
            AbstractTransferListener::REQUEST_ARGS_KEY => $commandArgs,
            AbstractTransferListener::PROGRESS_SNAPSHOT_KEY => $this->currentSnapshot,
        ];

        // Notify download handler
        $this->downloadHandler->transferInitiated($context);

        // Notify listeners
        $this->listenerNotifier?->transferInitiated($context);
    }

    /**
     * Propagates download-failed event to listeners.
     *
     * @param \Throwable $reason
     *
     * @return void
     */
    private function downloadFailed(\Throwable $reason): void
    {
        // Event already propagated.
        if ($this->currentSnapshot->getReason() !== null) {
            return;
        }

        $this->currentSnapshot = new TransferProgressSnapshot(
            $this->currentSnapshot->getIdentifier(),
            $this->currentSnapshot->getTransferredBytes(),
            $this->currentSnapshot->getTotalBytes(),
            $this->currentSnapshot->getResponse(),
            $reason
        );

        // Prepare context
        $context = [
            AbstractTransferListener::REQUEST_ARGS_KEY => $this->downloadRequestArgs,
            AbstractTransferListener::PROGRESS_SNAPSHOT_KEY => $this->currentSnapshot,
            AbstractTransferListener::REASON_KEY => $reason,
        ];

        // Notify download handler
        $this->downloadHandler->transferFail($context);

        // Notify listeners
        $this->listenerNotifier?->transferFail($context);
    }

    /**
     * Propagates part-download-completed to listeners.
     * It also does some computation in order to maintain internal states.
     *
     * @param int $partNumber
     * @param array $result
     * @param array $requestArgs
     *
     * @return void
     */
    private function partDownloadCompleted(
        int $partNumber,
        array $result,
        array $requestArgs
    ): void
    {
        $partTransferredBytes = $result['ContentLength'] ?? 0;
        // Snapshot and context for listeners
        $newSnapshot = new TransferProgressSnapshot(
            $this->currentSnapshot->getIdentifier(),
            $this->currentSnapshot->getTransferredBytes() + $partTransferredBytes,
            $this->objectSizeInBytes,
            $this->initialRequestResult
        );
        $this->currentSnapshot = $newSnapshot;

        // Notify download handler and evaluate if part was written
        $downloadHandlerSnapshot = $this->currentSnapshot->withResponse(
            $result
        );
        $wasPartWritten = $this->downloadHandler->bytesTransferred([
            AbstractTransferListener::REQUEST_ARGS_KEY => $requestArgs,
            AbstractTransferListener::PROGRESS_SNAPSHOT_KEY => $downloadHandlerSnapshot,
        ]);
        // If part was written to destination then we mark it as completed
        if ($wasPartWritten) {
            $this->partsCompleted[$partNumber] = true;

            // Persist resume state just if resume is enabled
            if ($this->config['resume_enabled'] ?? false) {
                // Update the resume state holder
                $this->resumableDownload?->updateCurrentSnapshot(
                    $this->currentSnapshot->toArray()
                );
                $this->resumableDownload?->markPartCompleted($partNumber);

                // Persist the resume state
                $this->persistResumeState();
            }
        }

        // Notify listeners
        $this->listenerNotifier?->bytesTransferred([
            AbstractTransferListener::REQUEST_ARGS_KEY => $this->downloadRequestArgs,
            AbstractTransferListener::PROGRESS_SNAPSHOT_KEY => $this->currentSnapshot,
        ]);
    }

    /**
     * Propagates part-download-failed event to listeners.
     *
     * @param \Throwable $reason
     *
     * @return void
     */
    private function partDownloadFailed(
        \Throwable $reason,
    ): void
    {
        $this->downloadFailed($reason);
    }

    /**
     * Propagates object-download-completed event to listeners.
     *
     * @return void
     */
    private function downloadComplete(): void
    {
        $newSnapshot = new TransferProgressSnapshot(
            $this->currentSnapshot->getIdentifier(),
            $this->currentSnapshot->getTransferredBytes(),
            $this->objectSizeInBytes,
            $this->currentSnapshot->getResponse()
        );
        $this->currentSnapshot = $newSnapshot;
        // Prepare context
        $context = [
            AbstractTransferListener::REQUEST_ARGS_KEY => $this->downloadRequestArgs,
            AbstractTransferListener::PROGRESS_SNAPSHOT_KEY => $this->currentSnapshot,
        ];

        // Notify download handler
        $this->downloadHandler->transferComplete($context);

        // Notify listeners
        $this->listenerNotifier?->transferComplete($context);

        // Delete resume file on successful completion
        if ($this->config['resume_enabled'] ?? false) {
            $this->resumableDownload?->deleteResumeFile();
        }
    }

    /**
     * Persist the current download state to the resume file.
     * This method is called after each part is downloaded.
     *
     * @return void
     */
    private function persistResumeState(): void
    {
        // Only persist if we have a download handler that supports resume
        if (!($this->downloadHandler instanceof ResumableDownloadHandlerInterface)) {
            return;
        }

        // Create ResumableDownload object
        if ($this->resumableDownload === null) {
            // Resume file destination
            $resumeFilePath = $this->config['resume_file_path'] ??
                $this->downloadHandler->getResumeFilePath();
            // Create snapshot data
            $snapshotData = $this->currentSnapshot->toArray();
            // Determine multipart download type
            $config = $this->config;
            $this->resumableDownload = new ResumableDownload(
                $resumeFilePath,
                $this->downloadRequestArgs,
                $config,
                $snapshotData,
                $this->initialRequestResult,
                $this->partsCompleted,
                $this->objectPartsCount,
                $this->downloadHandler->getTemporaryFilePath(),
                $this->eTag ?? '',
                $this->objectSizeInBytes,
                $this->downloadHandler->getFixedPartSize(),
                $this->downloadHandler->getDestination()
            );
        }

        try {
            $this->resumableDownload->toFile();
        } catch (\Exception $e) {
            throw new S3TransferException(
                "Unable to persist resumable download state due to: " . $e->getMessage(),
            );
        }
    }

    /**
     * Calculates the object size from content range.
     *
     * @param string $contentRange
     * @return int
     */
    public static function computeObjectSizeFromContentRange(
        string $contentRange
    ): int
    {
        if (empty($contentRange)) {
            return 0;
        }

        // For extracting the object size from the ContentRange header value.
        if (preg_match(self::OBJECT_SIZE_REGEX, $contentRange, $matches)) {
            return (int) $matches[1];
        }

        throw new S3TransferException(
            "Invalid content range \"$contentRange\""
        );
    }

    /**
     * @param string $range
     *
     * @return int
     */
    public static function getRangeTo(string $range): int
    {
        preg_match(self::RANGE_TO_REGEX, $range, $match);
        if (empty($match)) {
            return 0;
        }

        return (int) $match[1];
    }

    /**
     * @param mixed $multipartDownloadType
     *
     * @return string
     */
    public static function chooseDownloaderClass(
        string $multipartDownloadType
    ): string
    {
        return match ($multipartDownloadType) {
            AbstractMultipartDownloader::PART_GET_MULTIPART_DOWNLOADER => PartGetMultipartDownloader::class,
            AbstractMultipartDownloader::RANGED_GET_MULTIPART_DOWNLOADER => RangeGetMultipartDownloader::class,
            default => throw new \InvalidArgumentException(
                "The config value for `multipart_download_type` must be one of:\n"
                . "\t* " . AbstractMultipartDownloader::PART_GET_MULTIPART_DOWNLOADER
                ."\n"
                . "\t* " . AbstractMultipartDownloader::RANGED_GET_MULTIPART_DOWNLOADER
            )
        };
    }
}
