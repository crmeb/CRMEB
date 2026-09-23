<?php

namespace Aws\S3\S3Transfer\Progress;

final class ColoredTransferProgressBarFormat extends AbstractProgressBarFormat
{
    public const BLACK_COLOR_CODE = '[30m';
    public const BLUE_COLOR_CODE = '[34m';
    public const GREEN_COLOR_CODE = '[32m';
    public const RED_COLOR_CODE = '[31m';
    public const FORMAT_TEMPLATE = "|object_name|:\n"
    ."\033|color_code|[|progress_bar|] |percent|% "
    ."|transferred|/|to_be_transferred| |unit| |message|\033[0m";
    public const FORMAT_PARAMETERS = [
        'progress_bar',
        'percent',
        'transferred',
        'to_be_transferred',
        'unit',
        'color_code',
        'message',
        'object_name'
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
        return [
            'color_code' => ColoredTransferProgressBarFormat::BLACK_COLOR_CODE,
        ];
    }
}
