<?php

namespace Aws\S3\S3Transfer\Progress;

use Closure;

final class MultiProgressTracker extends AbstractTransferListener implements ProgressTrackerInterface
{
    private const CLEAR_ASCII_CODE = "\033[2J\033[H";

    /** @var array */
    private array $singleProgressTrackers;

    /** @var resource */
    private mixed $output;

    /** @var int */
    private int $transferCount;

    /** @var int */
    private int $completed;

    /** @var int */
    private int $failed;

    /** @var ProgressBarFactoryInterface|Closure|null */
    private readonly ProgressBarFactoryInterface|Closure|null $progressBarFactory;

    /**
     * @param array $singleProgressTrackers
     * @param mixed|false|resource $output
     * @param int $transferCount
     * @param int $completed
     * @param int $failed
     * @param ProgressBarFactoryInterface|Closure|null $progressBarFactory
     */
    public function __construct(
        array $singleProgressTrackers = [],
        mixed $output = STDOUT,
        int $transferCount = 0,
        int $completed = 0,
        int $failed = 0,
        ProgressBarFactoryInterface|Closure|null $progressBarFactory = null
    )
    {
        $this->singleProgressTrackers = $singleProgressTrackers;
        $this->output = $output;
        $this->transferCount = $transferCount;
        $this->completed = $completed;
        $this->failed = $failed;
        $this->progressBarFactory = $progressBarFactory;
    }

    /**
     * @return array
     */
    public function getSingleProgressTrackers(): array
    {
        return $this->singleProgressTrackers;
    }

    /**
     * @return mixed
     */
    public function getOutput(): mixed
    {
        return $this->output;
    }

    /**
     * @return int
     */
    public function getTransferCount(): int
    {
        return $this->transferCount;
    }

    /**
     * @return int
     */
    public function getCompleted(): int
    {
        return $this->completed;
    }

    /**
     * @return int
     */
    public function getFailed(): int
    {
        return $this->failed;
    }

    /**
     * @return ProgressBarFactoryInterface|Closure|null
     */
    public function getProgressBarFactory(): ProgressBarFactoryInterface|Closure|null
    {
        return $this->progressBarFactory;
    }

    /**
     * @inheritDoc
     */
    public function transferInitiated(array $context): void
    {
        $this->transferCount++;
        $snapshot = $context[AbstractTransferListener::PROGRESS_SNAPSHOT_KEY];
        if (isset($this->singleProgressTrackers[$snapshot->getIdentifier()])) {
            $progressTracker = $this->singleProgressTrackers[$snapshot->getIdentifier()];
        } else {
            if ($this->progressBarFactory === null) {
                $progressTracker = new SingleProgressTracker(
                    output: $this->output,
                    clear: false,
                    showProgressOnUpdate: false,
                );
            } else {
                $progressBarFactoryFn = $this->progressBarFactory;
                $progressTracker = new SingleProgressTracker(
                    progressBar: $progressBarFactoryFn(),
                    output: $this->output,
                    clear: false,
                    showProgressOnUpdate: false,
                );
            }

            $this->singleProgressTrackers[$snapshot->getIdentifier()] = $progressTracker;
        }

        $progressTracker->transferInitiated($context);
        $this->showProgress();
    }

    /**
     * @inheritDoc
     */
    public function bytesTransferred(array $context): bool
    {
        $snapshot = $context[AbstractTransferListener::PROGRESS_SNAPSHOT_KEY];
        $progressTracker = $this->singleProgressTrackers[$snapshot->getIdentifier()];
        $progressTracker->bytesTransferred($context);
        $this->showProgress();

        return true;
    }

    /**
     * @inheritDoc
     */
    public function transferComplete(array $context): void
    {
        $this->completed++;
        $snapshot = $context[AbstractTransferListener::PROGRESS_SNAPSHOT_KEY];
        $progressTracker = $this->singleProgressTrackers[$snapshot->getIdentifier()];
        $progressTracker->transferComplete($context);
        $this->showProgress();
    }

    /**
     * @inheritDoc
     */
    public function transferFail(array $context): void
    {
        $this->failed++;
        $snapshot = $context[AbstractTransferListener::PROGRESS_SNAPSHOT_KEY];
        $progressTracker = $this->singleProgressTrackers[$snapshot->getIdentifier()];
        $progressTracker->transferFail($context);
        $this->showProgress();
    }

    /**
     * @inheritDoc
     */
    public function showProgress(): void
    {
        fwrite($this->output, self::CLEAR_ASCII_CODE);
        $percentsSum = 0;
        foreach ($this->singleProgressTrackers as $_ => $progressTracker) {
            $progressTracker->showProgress();
            $percentsSum += $progressTracker->getProgressBar()->getPercentCompleted();
        }

        $allProgressBarWidth = ConsoleProgressBar::DEFAULT_PROGRESS_BAR_WIDTH;
        if (count($this->singleProgressTrackers) !== 0) {
            $firstKey = array_key_first($this->singleProgressTrackers);
            $allProgressBarWidth = $this->singleProgressTrackers[$firstKey]
                ->getProgressBar()->getProgressBarWidth();
        }

        $percent = (int) floor($percentsSum / $this->transferCount);
        $multiProgressBarFormat = new MultiProgressBarFormat();
        $multiProgressBarFormat->setArgs([
            'completed' => $this->completed,
            'failed' => $this->failed,
            'total' => $this->transferCount,
        ]);
        $allTransferProgressBar = new ConsoleProgressBar(
            progressBarWidth: $allProgressBarWidth,
            percentCompleted: $percent,
            progressBarFormat: $multiProgressBarFormat
        );
        fwrite(
            $this->output,
            sprintf(
                "\n%s\n",
                str_repeat(
                    '-',
                    $allTransferProgressBar->getProgressBarWidth()
                )
            )
        );
        fwrite(
            $this->output,
            sprintf(
                "%s\n",
                $allTransferProgressBar->render(),
            )
        );
    }
}
