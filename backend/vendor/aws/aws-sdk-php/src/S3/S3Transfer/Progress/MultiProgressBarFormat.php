<?php

namespace Aws\S3\S3Transfer\Progress;

final class MultiProgressBarFormat extends AbstractProgressBarFormat
{
    public const FORMAT_TEMPLATE = "[|progress_bar|] |percent|% "
    ."Completed: |completed|/|total|, Failed: |failed|/|total|";
    public const FORMAT_PARAMETERS = [
        'completed',
        'failed',
        'total',
        'percent',
        'progress_bar'
    ];

    /**
     * @return string
     */
    public function getFormatTemplate(): string
    {
        return self::FORMAT_TEMPLATE;
    }

    /**
     * @return array
     */
    public function getFormatParameters(): array
    {
        return self::FORMAT_PARAMETERS;
    }

    /**
     * @return array
     */
    protected function getFormatDefaultParameterValues(): array
    {
        return [];
    }
}
