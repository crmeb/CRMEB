<?php

namespace Aws\S3\S3Transfer\Progress;

/**
 * Progress bar base implementation.
 */
interface ProgressBarInterface
{
    /**
     * @return string
     */
    public function render(): string;

    /**
     * @param int $percent
     *
     * @return void
     */
    public function setPercentCompleted(int $percent): void;

    /**
     * @return int
     */
    public function getPercentCompleted(): int;

    /**
     * @return AbstractProgressBarFormat
     */
    public function getProgressBarFormat(): AbstractProgressBarFormat;
}
