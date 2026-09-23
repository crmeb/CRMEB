<?php

namespace Aws\S3\S3Transfer\Progress;

final class TransferProgressBarFormat extends AbstractProgressBarFormat
{
    public const FORMAT_TEMPLATE = "|object_name|:\n[|progress_bar|]"
    ." |percent|% |transferred|/|to_be_transferred| |unit|";
    public const FORMAT_PARAMETERS = [
        'object_name',
        'progress_bar',
        'percent',
        'transferred',
        'to_be_transferred',
        'unit'
    ];

    /**
     * @inheritDoc
     */
    public function getFormatTemplate(): string
    {
        return self::FORMAT_TEMPLATE;
    }

    /**
     * @inheritDoc
     */
    public function getFormatParameters(): array
    {
        return self::FORMAT_PARAMETERS;
    }

    protected function getFormatDefaultParameterValues(): array
    {
        return [];
    }
}
