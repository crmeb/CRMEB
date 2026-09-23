<?php

namespace Aws\S3\S3Transfer;

use Aws\CommandInterface;
use Aws\CommandPool;
use Aws\ResultInterface;
use Aws\S3\S3ClientInterface;
use Aws\S3\S3Transfer\Models\S3TransferManagerConfig;
use Aws\S3\S3Transfer\Progress\AbstractTransferListener;
use Aws\S3\S3Transfer\Progress\TransferListenerNotifier;
use Aws\S3\S3Transfer\Progress\TransferProgressSnapshot;
use GuzzleHttp\Promise\Coroutine;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\PromisorInterface;
use Throwable;

/**
 * Abstract base class for multipart operations
 */
abstract class AbstractMultipartUploader implements PromisorInterface
{
    public const PART_MIN_SIZE = 5 * 1024 * 1024; // 5 MiB
    public const PART_MAX_SIZE = 5 * 1024 * 1024 * 1024; // 5 GiB
    public const PART_MAX_NUM = 10000;

    /** @var S3ClientInterface */
    protected readonly S3ClientInterface $s3Client;

    /** @var array */
    protected readonly array $requestArgs;

    /** @var array */
    protected readonly array $config;

    /** @var string|null */
    protected string|null $uploadId;

    /** @var array */
    protected array $partsCompleted;

    /** @var array */
    protected array $onCompletionCallbacks = [];

    /** @var TransferListenerNotifier|null */
    protected ?TransferListenerNotifier $listenerNotifier;

    /** Tracking Members */
    /** @var TransferProgressSnapshot|null */
    protected ?TransferProgressSnapshot $currentSnapshot;

    /**
     * @param S3ClientInterface $s3Client
     * @param array $requestArgs
     * @param array $config
     * - target_part_size_bytes: (int, optional)
     * - concurrency: (int, optional)
     * @param string|null $uploadId
     * @param array $partsCompleted
     * @param TransferProgressSnapshot|null $currentSnapshot
     * @param TransferListenerNotifier|null $listenerNotifier
     */
    public function __construct(
        S3ClientInterface $s3Client,
        array $requestArgs,
        array $config = [],
        ?string $uploadId = null,
        array $partsCompleted = [],
        ?TransferProgressSnapshot $currentSnapshot = null,
        ?TransferListenerNotifier $listenerNotifier = null,
    ) {
        $this->s3Client = $s3Client;
        $this->requestArgs = $requestArgs;
        $this->validateConfig($config);
        $this->config = $config;
        $this->uploadId = $uploadId;
        $this->partsCompleted = $partsCompleted;
        $this->currentSnapshot = $currentSnapshot;
        $this->listenerNotifier = $listenerNotifier;
    }

    /**
     * @return PromiseInterface
     */
    abstract protected function createMultipartOperation(): PromiseInterface;

    /**
     * @return PromiseInterface
     */
    abstract protected function completeMultipartOperation(): PromiseInterface;

    /**
     * @return PromiseInterface
     */
    abstract protected function processMultipartOperation(): PromiseInterface;

    /**
     * @param int $partSize
     * @param array $requestArgs
     * @param array $partData
     *
     * @return void
     */
    abstract protected function partCompleted(
        int $partSize,
        array $requestArgs,
        array $partData
    ): void;

    /**
     * @return PromiseInterface
     */
    abstract protected function abortMultipartOperation(): PromiseInterface;

    /**
     * @return int
     */
    abstract protected function getTotalSize(): int;

    /**
     * @param ResultInterface $result
     *
     * @return mixed
     */
    abstract protected function createResponse(ResultInterface $result): mixed;

    /**
     * @param array $config
     *
     * @return void
     */
    protected function validateConfig(array &$config): void
    {
        if (!isset($config['target_part_size_bytes'])) {
            $config['target_part_size_bytes'] = S3TransferManagerConfig::DEFAULT_TARGET_PART_SIZE_BYTES;
        }

        if (!isset($config['concurrency'])) {
            $config['concurrency'] = S3TransferManagerConfig::DEFAULT_CONCURRENCY;
        }

        $partSize = $config['target_part_size_bytes'];
        if ($partSize < self::PART_MIN_SIZE || $partSize > self::PART_MAX_SIZE) {
            throw new \InvalidArgumentException(
                "Part size config must be between " . self::PART_MIN_SIZE
                ." and " . self::PART_MAX_SIZE . " bytes "
                ."but it is configured to $partSize"
            );
        }
    }

    /**
     * @return string|null
     */
    public function getUploadId(): ?string
    {
        return $this->uploadId;
    }

    /**
     * @return array
     */
    public function getPartsCompleted(): array
    {
        return $this->partsCompleted;
    }

    /**
     * Get the current progress snapshot.
     * @return TransferProgressSnapshot|null
     */
    public function getCurrentSnapshot(): ?TransferProgressSnapshot
    {
        return $this->currentSnapshot;
    }

    /**
     * @return PromiseInterface
     */
    public function promise(): PromiseInterface
    {
        return Coroutine::of(function () {
            try {
                yield $this->createMultipartOperation();
                yield $this->processMultipartOperation();
                yield $this->completeMultipartOperation();
            } finally {
                $this->callOnCompletionCallbacks();
            }
        })->then(function (ResultInterface $result) {
            return $this->createResponse($result);
        })->otherwise(function (Throwable $e) {
            $this->operationFailed($e);

            throw $e;
        });
    }

    /**
     * @return void
     */
    protected function sortParts(): void
    {
        usort($this->partsCompleted, function ($partOne, $partTwo) {
            return $partOne['PartNumber']
                <=> $partTwo['PartNumber'];
        });
    }

    /**
     * @param ResultInterface $result
     * @param CommandInterface $command
     *
     * @return array
     */
    protected function collectPart(
        ResultInterface $result,
        CommandInterface $command
    ): array
    {
        $checksumResult = match($command->getName()) {
            'UploadPart' => $result,
            'UploadPartCopy' => $result['CopyPartResult'],
            default => $result[$command->getName() . 'Result']
        };

        $partNumber = $command['PartNumber'];
        $partData = [
            'PartNumber' => $partNumber,
            'ETag' => $checksumResult['ETag'],
        ];

        if (isset($command['ChecksumAlgorithm'])) {
            $checksumMemberName = 'Checksum' . strtoupper($command['ChecksumAlgorithm']);
            $partData[$checksumMemberName] = $checksumResult[$checksumMemberName] ?? null;
        }

        $this->partsCompleted[$partNumber] = $partData;

        return $partData;
    }

    /**
     * @param \Iterator $commands
     * @param callable $fulfilledCallback
     * @param callable $rejectedCallback
     * @return PromiseInterface
     */
    protected function createCommandPool(
        \Iterator $commands,
        callable $fulfilledCallback,
        callable $rejectedCallback
    ): PromiseInterface
    {
        return (new CommandPool(
            $this->s3Client,
            $commands,
            [
                'concurrency' => $this->config['concurrency'],
                'fulfilled' => $fulfilledCallback,
                'rejected' => $rejectedCallback
            ]
        ))->promise();
    }

    /**
     * @param array $requestArgs
     * @return void
     */
    protected function operationInitiated(array $requestArgs): void
    {
        if ($this->currentSnapshot === null) {
            $this->currentSnapshot = new TransferProgressSnapshot(
                $requestArgs['Key'],
                0,
                $this->getTotalSize()
            );
        }

        $this->listenerNotifier?->transferInitiated([
            AbstractTransferListener::REQUEST_ARGS_KEY => $requestArgs,
            AbstractTransferListener::PROGRESS_SNAPSHOT_KEY => $this->currentSnapshot
        ]);
    }

    /**
     * @param ResultInterface $result
     * @return void
     */
    protected function operationCompleted(ResultInterface $result): void
    {
        $newSnapshot = new TransferProgressSnapshot(
            $this->currentSnapshot->getIdentifier(),
            $this->currentSnapshot->getTransferredBytes(),
            $this->currentSnapshot->getTotalBytes(),
            $result->toArray(),
            $this->currentSnapshot->getReason(),
        );

        $this->currentSnapshot = $newSnapshot;

        $this->listenerNotifier?->transferComplete([
            AbstractTransferListener::REQUEST_ARGS_KEY =>
                $this->requestArgs,
            AbstractTransferListener::PROGRESS_SNAPSHOT_KEY => $this->currentSnapshot
        ]);
    }

    /**
     * @param Throwable $reason
     * @return void
     *
     */
    protected function operationFailed(Throwable $reason): void
    {
        // Event already propagated
        if ($this->currentSnapshot?->getReason() !== null) {
            return;
        }

        if ($this->currentSnapshot === null) {
            $this->currentSnapshot = new TransferProgressSnapshot(
                'Unknown',
                0,
                0,
            );
        }

        $this->currentSnapshot = new TransferProgressSnapshot(
            $this->currentSnapshot->getIdentifier(),
            $this->currentSnapshot->getTransferredBytes(),
            $this->currentSnapshot->getTotalBytes(),
            $this->currentSnapshot->getResponse(),
            $reason
        );

        if (!empty($this->uploadId)) {
            trigger_error(
                "Multipart Upload with id: " . $this->uploadId . " failed",
            );
            $this->abortMultipartOperation()->wait();
        }

        $this->listenerNotifier?->transferFail([
            AbstractTransferListener::REQUEST_ARGS_KEY =>
                $this->requestArgs,
            AbstractTransferListener::PROGRESS_SNAPSHOT_KEY => $this->currentSnapshot,
            'reason' => $reason,
        ]);
    }

    /**
     * @return void
     */
    protected function callOnCompletionCallbacks(): void
    {
        foreach ($this->onCompletionCallbacks as $fn) {
            if (is_callable($fn)) {
                call_user_func($fn);
            }
        }

        $this->onCompletionCallbacks = [];
    }

    /**
     * @param Throwable $reason
     * @return void
     */
    protected function partFailed(Throwable $reason): void
    {
        $this->operationFailed($reason);
    }

    /**
     * @return int
     */
    protected function calculatePartSize(): int
    {
        return max(
            $this->getTotalSize() / self::PART_MAX_NUM,
            $this->config['target_part_size_bytes']
        );
    }
}
