<?php

namespace Aws\S3\S3Transfer\Progress;

interface ProgressTrackerInterface
{
    /**
     * To show the progress being tracked.
     *
     * @return void
     */
    public function showProgress(): void;
}
