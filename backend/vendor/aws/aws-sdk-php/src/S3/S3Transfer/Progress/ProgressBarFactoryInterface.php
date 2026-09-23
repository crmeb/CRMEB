<?php

namespace Aws\S3\S3Transfer\Progress;

interface ProgressBarFactoryInterface
{
    public function __invoke(): ProgressBarInterface;
}
