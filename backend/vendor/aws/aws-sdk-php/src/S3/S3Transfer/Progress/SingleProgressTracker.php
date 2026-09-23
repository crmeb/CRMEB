<?php

namespace Aws\S3\S3Transfer\Progress;

use Aws\S3\S3Transfer\Exception\ProgressTrackerException;

/**
 * To track single object transfers.
 */
final class SingleProgressTracker extends AbstractTransferListener
    implements ProgressTrackerInterface
{
    /** @var ProgressBarInterface */
    private ProgressBarInterface $progressBar;

    /** @var resource */
    private mixed $output;

    /** @var bool */
    private bool $clear;

    /** @var TransferProgressSnapshot|null */
    private ?TransferProgressSnapshot $currentSnapshot;

    /** @var bool */
    private bool $showProgressOnUpdate;

    /**
     * @param ProgressBarInterface $progressBar
     * @param mixed|false|resource $output
     * @param bool $clear
     * @param TransferProgressSnapshot|null $currentSnapshot
     * @param bool $showProgressOnUpdate
     */
    public function __construct(
        ProgressBarInterface $progressBar = new ConsoleProgressBar(),
        mixed $output = STDOUT,
        bool $clear = true,
        ?TransferProgressSnapshot $currentSnapshot = null,
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

    /**
     * @return ProgressBarInterface
     */
    public function getProgressBar(): ProgressBarInterface
    {
        return $this->progressBar;
    }

    /**
     * @return mixed
     */
    public function getOutput(): mixed
    {
        return $this->output;
    }

    /**
     * @return bool
     */
    public function isClear(): bool
    {
        return $this->clear;
    }

    /**
     * @return TransferProgressSnapshot|null
     */
    public function getCurrentSnapshot(): ?TransferProgressSnapshot
    {
        return $this->currentSnapshot;
    }

    /**
     * @return bool
     */
    public function isShowProgressOnUpdate(): bool
    {
        return $this->showProgressOnUpdate;
    }

    /**
     * @inheritDoc
     *
     * @return void
     */
    public function transferInitiated(array $context): void
    {
        $this->currentSnapshot = $context[AbstractTransferListener::PROGRESS_SNAPSHOT_KEY];
        $progressFormat = $this->progressBar->getProgressBarFormat();
        // Probably a common argument
        $progressFormat->setArg(
            'object_name',
            $this->currentSnapshot->getIdentifier()
        );

        $this->updateProgressBar();
    }

    /**
     * @inheritDoc
     */
    public function bytesTransferred(array $context): bool
    {
        $this->currentSnapshot = $context[AbstractTransferListener::PROGRESS_SNAPSHOT_KEY];
        $progressFormat = $this->progressBar->getProgressBarFormat();
        if ($progressFormat instanceof ColoredTransferProgressBarFormat) {
            $progressFormat->setArg(
                'color_code',
                ColoredTransferProgressBarFormat::BLUE_COLOR_CODE
            );
        }

        $this->updateProgressBar();

        return true;
    }

    /**
     * @inheritDoc
     *
     * @return void
     */
    public function transferComplete(array $context): void
    {
        $this->currentSnapshot = $context[AbstractTransferListener::PROGRESS_SNAPSHOT_KEY];
        $progressFormat = $this->progressBar->getProgressBarFormat();
        if ($progressFormat instanceof ColoredTransferProgressBarFormat) {
            $progressFormat->setArg(
                'color_code',
                ColoredTransferProgressBarFormat::GREEN_COLOR_CODE
            );
        }

        $this->updateProgressBar(
            $this->currentSnapshot->getTotalBytes() === 0
        );
    }

    /**
     * @inheritDoc
     *
     * @return void
     */
    public function transferFail(array $context): void
    {
        $this->currentSnapshot = $context[AbstractTransferListener::PROGRESS_SNAPSHOT_KEY];
        $progressFormat = $this->progressBar->getProgressBarFormat();
        if ($progressFormat instanceof ColoredTransferProgressBarFormat) {
            $progressFormat->setArg(
                'color_code',
                ColoredTransferProgressBarFormat::RED_COLOR_CODE
            );
            $progressFormat->setArg(
                'message',
                $context[AbstractTransferListener::REASON_KEY]
            );
        }

        $this->updateProgressBar();
    }

    /**
     * Updates the progress bar with the transfer snapshot
     * and also call showProgress.
     *
     * @param bool $forceCompletion To force the progress bar to be
     * completed. This is useful for files where its size is zero,
     * for which a ratio will return zero, and hence the percent
     * will be zero.
     *
     * @return void
     */
    private function updateProgressBar(
        bool $forceCompletion = false
    ): void
    {
        if (!$forceCompletion) {
            $this->progressBar->setPercentCompleted(
                ((int)floor($this->currentSnapshot->ratioTransferred() * 100))
            );
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
        // Display progress
        if ($this->showProgressOnUpdate) {
            $this->showProgress();
        }
    }

    /**
     * @inheritDoc
     *
     * @return void
     */
    public function showProgress(): void
    {
        if ($this->currentSnapshot === null) {
            throw new ProgressTrackerException(
                "There is not snapshot to show progress for."
            );
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
}
