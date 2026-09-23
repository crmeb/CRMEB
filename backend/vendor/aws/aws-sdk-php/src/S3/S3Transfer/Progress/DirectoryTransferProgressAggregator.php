<?php

namespace Aws\S3\S3Transfer\Progress;

use Aws\S3\S3Transfer\Progress\TransferProgressSnapshot;
use Aws\S3\S3Transfer\Progress\TransferListenerNotifier;
use Throwable;

/**
 * Aggregates per-object progress snapshots into a directory-level snapshot.
 * Acts as an object-level listener and emits directory-level events through an
 * internal notifier to the provided directory listeners.
 */
final class DirectoryTransferProgressAggregator extends AbstractTransferListener
{
    /** @var string */
    private string $identifier;

    /** @var int */
    private int $totalBytes;

    /** @var int */
    private int $totalFiles;

    /** @var int */
    private int $transferredBytes = 0;

    /** @var int */
    private int $transferredFiles = 0;

    /** @var array<string, int> */
    private array $objectBytes = [];

    /** @var array<string, bool> */
    private array $objectTerminal = [];

    /** @var TransferListenerNotifier */
    private TransferListenerNotifier $directoryNotifier;

    public function __construct(
        string $identifier,
        int $totalBytes,
        int $totalFiles,
        array $directoryListeners = [],
        ?AbstractTransferListener $directoryProgressTracker = null
    ) {
        if ($directoryProgressTracker !== null) {
            $directoryListeners[] = $directoryProgressTracker;
        }

        $this->identifier = $identifier;
        $this->totalBytes = $totalBytes;
        $this->totalFiles = $totalFiles;
        $this->directoryNotifier = new TransferListenerNotifier($directoryListeners);
    }

    /**
    * Notify directory listeners that the directory transfer has been initiated.
    *
    * @param array $requestArgs
    *
    * @return void
    */
    public function notifyDirectoryInitiated(array $requestArgs): void
    {
        $this->directoryNotifier->transferInitiated([
            self::REQUEST_ARGS_KEY => $requestArgs,
            self::PROGRESS_SNAPSHOT_KEY => $this->getSnapshot(),
        ]);
    }

    /**
     * Notify directory listeners that the directory transfer completed.
     *
     * @param array|null $response
     *
     * @return void
     */
    public function notifyDirectoryComplete(?array $response = null): void
    {
        $snapshot = $this->getSnapshot();
        if ($response !== null) {
            $snapshot = $snapshot->withResponse($response);
        }

        $this->directoryNotifier->transferComplete([
            self::REQUEST_ARGS_KEY => [],
            self::PROGRESS_SNAPSHOT_KEY => $snapshot,
        ]);
    }

    /**
     * Notify directory listeners that the directory transfer failed.
     *
     * @param Throwable|string $reason
     *
     * @return void
     */
    public function notifyDirectoryFail(Throwable|string $reason): void
    {
        $snapshot = $this->getSnapshot();
        $this->directoryNotifier->transferFail([
            self::REQUEST_ARGS_KEY => [],
            self::PROGRESS_SNAPSHOT_KEY => $snapshot,
            self::REASON_KEY => $reason,
        ]);
    }

    /**
     * Update totals, useful when object list is streamed.
     *
     * @param int $bytes
     * @param int $files
     *
     * @return void
     */
    public function incrementTotals(int $bytes, int $files = 1): void
    {
        $this->totalBytes += $bytes;
        $this->totalFiles += $files;
    }

    /**
     * @inheritDoc
     */
    public function bytesTransferred(array $context): bool
    {
        /** @var TransferProgressSnapshot $snapshot */
        $snapshot = $context[self::PROGRESS_SNAPSHOT_KEY];
        $this->updateObjectProgress($snapshot);
        $this->directoryNotifier->bytesTransferred([
            self::REQUEST_ARGS_KEY => $context[self::REQUEST_ARGS_KEY] ?? [],
            self::PROGRESS_SNAPSHOT_KEY => $this->getSnapshot(),
        ]);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function transferComplete(array $context): void
    {
        /** @var TransferProgressSnapshot $snapshot */
        $snapshot = $context[self::PROGRESS_SNAPSHOT_KEY];
        $this->markObjectTerminal($snapshot);
        $this->directoryNotifier->bytesTransferred([
            self::REQUEST_ARGS_KEY => $context[self::REQUEST_ARGS_KEY] ?? [],
            self::PROGRESS_SNAPSHOT_KEY => $this->getSnapshot(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function transferFail(array $context): void
    {
        /** @var TransferProgressSnapshot $snapshot */
        $snapshot = $context[self::PROGRESS_SNAPSHOT_KEY];
        $this->markObjectTerminal($snapshot);
        $this->directoryNotifier->bytesTransferred([
            self::REQUEST_ARGS_KEY => $context[self::REQUEST_ARGS_KEY] ?? [],
            self::PROGRESS_SNAPSHOT_KEY => $this->getSnapshot(),
            self::REASON_KEY => $context[self::REASON_KEY] ?? null,
        ]);
    }

    /**
     * @return DirectoryTransferProgressSnapshot
     */
    public function getSnapshot(): DirectoryTransferProgressSnapshot
    {
        return new DirectoryTransferProgressSnapshot(
            $this->identifier,
            $this->transferredBytes,
            $this->totalBytes,
            $this->transferredFiles,
            $this->totalFiles,
        );
    }

    /**
     * @param TransferProgressSnapshot $snapshot
     *
     * @return void
     */
    private function updateObjectProgress(TransferProgressSnapshot $snapshot): void
    {
        $identifier = $snapshot->getIdentifier();
        $previous = $this->objectBytes[$identifier] ?? 0;
        $current = $snapshot->getTransferredBytes();
        // Avoid double counting when updates decrease (should not happen, but guard)
        $delta = $current - $previous;
        if ($delta < 0) {
            $delta = 0;
        }

        $this->objectBytes[$identifier] = $current;
        $this->transferredBytes += $delta;
    }

    /**
     * @param TransferProgressSnapshot $snapshot
     *
     * @return void
     */
    private function markObjectTerminal(TransferProgressSnapshot $snapshot): void
    {
        $this->updateObjectProgress($snapshot);
        $identifier = $snapshot->getIdentifier();
        if (!($this->objectTerminal[$identifier] ?? false)) {
            $this->objectTerminal[$identifier] = true;
            $this->transferredFiles++;
        }
    }
}
