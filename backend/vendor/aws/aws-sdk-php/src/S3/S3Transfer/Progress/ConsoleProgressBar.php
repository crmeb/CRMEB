<?php

namespace Aws\S3\S3Transfer\Progress;

final class ConsoleProgressBar implements ProgressBarInterface
{
    public const DEFAULT_PROGRESS_BAR_CHAR = '#';
    public const DEFAULT_PROGRESS_BAR_WIDTH = 50;
    public const MAX_PROGRESS_BAR_WIDTH = 50;

    /** @var string */
    private string $progressBarChar;

    /** @var int */
    private int $progressBarWidth;

    /** @var int */
    private int $percentCompleted;

    /** @var AbstractProgressBarFormat */
    private AbstractProgressBarFormat $progressBarFormat;

    /**
     * @param string $progressBarChar
     * @param int $progressBarWidth
     * @param int $percentCompleted
     * @param AbstractProgressBarFormat $progressBarFormat
     */
    public function __construct(
        string                    $progressBarChar = self::DEFAULT_PROGRESS_BAR_CHAR,
        int                       $progressBarWidth = self::DEFAULT_PROGRESS_BAR_WIDTH,
        int                       $percentCompleted = 0,
        AbstractProgressBarFormat $progressBarFormat = new ColoredTransferProgressBarFormat(),
    ) {
        $this->progressBarChar = $progressBarChar;
        $this->progressBarWidth = min(
            $progressBarWidth,
            self::MAX_PROGRESS_BAR_WIDTH
        );
        $this->percentCompleted = $percentCompleted;
        $this->progressBarFormat = $progressBarFormat;
    }

    /**
     * @return string
     */
    public function getProgressBarChar(): string
    {
        return $this->progressBarChar;
    }

    /**
     * @return int
     */
    public function getProgressBarWidth(): int
    {
        return $this->progressBarWidth;
    }

    /**
     * @return int
     */
    public function getPercentCompleted(): int
    {
        return $this->percentCompleted;
    }

    /**
     * @return AbstractProgressBarFormat
     */
    public function getProgressBarFormat(): AbstractProgressBarFormat
    {
        return $this->progressBarFormat;
    }

    /**
     * Set current progress percent.
     *
     * @param int $percent
     *
     * @return void
     */
    public function setPercentCompleted(int $percent): void
    {
        $this->percentCompleted = max(0, min(100, $percent));
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        $filledWidth = (int) round(($this->progressBarWidth * $this->percentCompleted) / 100);
        $progressBar = str_repeat($this->progressBarChar, $filledWidth)
            . str_repeat(' ', $this->progressBarWidth - $filledWidth);

        // Common arguments
        $this->progressBarFormat->setArg('progress_bar', $progressBar);
        $this->progressBarFormat->setArg('percent', $this->percentCompleted);

        return $this->progressBarFormat->format();
    }
}
