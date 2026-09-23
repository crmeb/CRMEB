<?php

namespace Aws\S3\S3Transfer\Progress;

use Aws\S3\S3Transfer\Exception\ProgressTrackerException;
use Aws\S3\S3Transfer\Progress\ConsoleProgressBar;
use Aws\S3\S3Transfer\Progress\DirectoryTransferProgressSnapshot;
use Aws\S3\S3Transfer\Progress\ProgressBarInterface;

/**
 * Progress tracker for directory-level transfers using directory snapshots.
 */
final class DirectoryProgressTracker extends AbstractTransferListener implements ProgressTrackerInterface
{
    /** @var ProgressBarInterface */
    private ProgressBarInterface $progressBar;

    /** @var mixed */
    private mixed $output;

    /** @var bool */
    private bool $clear;

    /** @var DirectoryTransferProgressSnapshot|null */
    private ?DirectoryTransferProgressSnapshot $currentSnapshot;

    /** @var bool */
    private bool $showProgressOnUpdate;

    public function __construct(
        ProgressBarInterface $progressBar = new ConsoleProgressBar(),
        mixed $output = STDOUT,
        bool $clear = true,
        ?DirectoryTransferProgressSnapshot $currentSnapshot = null,
        bool $showProgressOnUpdate = true
    ) {
        $this->progressBar = $progressBar;
        if (get_resource_type($output) !== 'stream') {
            throw new \InvalidArgumentException("The type for $output must be a stream");
        }
        $this->output = $output;
        $this->clear = $clear;
        $this->currentSnapshot = $currentSnapshot;
        $this->showProgressOnUpdate = $showProgressOnUpdate;
    }

    public function getProgressBar(): ProgressBarInterface
    {
        return $this->progressBar;
    }

    public function transferInitiated(array $context): void
    {
        $this->currentSnapshot = $context[self::PROGRESS_SNAPSHOT_KEY];
        $progressFormat = $this->progressBar->getProgressBarFormat();
        // Probably a common argument
        $progressFormat->setArg(
            'object_name',
            $this->currentSnapshot->getIdentifier()
        );
        $this->updateProgressBar();
    }

    public function bytesTransferred(array $context): bool
    {
        $this->currentSnapshot = $context[self::PROGRESS_SNAPSHOT_KEY];
        $this->updateProgressBar();

        return true;
    }

    public function transferComplete(array $context): void
    {
        $this->currentSnapshot = $context[self::PROGRESS_SNAPSHOT_KEY];
        $this->updateProgressBar(true);
    }

    public function transferFail(array $context): void
    {
        $this->currentSnapshot = $context[self::PROGRESS_SNAPSHOT_KEY];
        $this->updateProgressBar();
    }

    public function showProgress(): void
    {
        if ($this->currentSnapshot === null) {
            throw new ProgressTrackerException("There is not snapshot to show progress for.");
        }

        if ($this->clear) {
            fwrite($this->output, "\033[2J\033[H");
        }

        fwrite($this->output, sprintf(
            "\r\n%s",
            $this->progressBar->render()
        ));
        fflush($this->output);
    }

    private function updateProgressBar(bool $forceCompletion = false): void
    {
        if ($this->currentSnapshot === null) {
            return;
        }

        if (!$forceCompletion) {
            $percent = (int) floor($this->currentSnapshot->ratioTransferred() * 100);
            $this->progressBar->setPercentCompleted($percent);
        } else {
            $this->progressBar->setPercentCompleted(100);
        }

        $this->progressBar->getProgressBarFormat()->setArgs([
            'transferred' => min(
                $this->currentSnapshot->getTransferredBytes(),
                $this->currentSnapshot->getTotalBytes()
            ),
            'to_be_transferred' => $this->currentSnapshot->getTotalBytes(),
            'unit' => 'B',
        ]);

        if ($this->showProgressOnUpdate) {
            $this->showProgress();
        }
    }
}
