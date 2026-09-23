<?php

namespace Aws\S3\S3Transfer;

use Aws\CommandInterface;
use Aws\Result;
use Aws\ResultInterface;

final class RangeGetMultipartDownloader extends AbstractMultipartDownloader
{
    /**
     * @inheritDoc
     */
    protected function getFetchCommandArgs(): array
    {
        $nextCommandArgs = $this->downloadRequestArgs;
        $partSize = $this->config['target_part_size_bytes'];
        $from = ($this->currentPartNo - 1) * $partSize;
        $to = ($this->currentPartNo * $partSize) - 1;

        if ($this->objectSizeInBytes !== 0) {
            $to = min($this->objectSizeInBytes, $to);
        }

        $nextCommandArgs['Range'] = "bytes=$from-$to";

        return $nextCommandArgs;
    }

    /**
     * @inheritDoc
     *
     * @param Result $result
     *
     * @return void
     */
    protected function computeObjectDimensions(ResultInterface $result): void
    {
        // Assign object size just if needed.
        if ($this->objectSizeInBytes === 0) {
            $this->objectSizeInBytes = self::computeObjectSizeFromContentRange(
                $result['ContentRange'] ?? ""
            );
        }

        $partSize = $this->config['target_part_size_bytes'];
        if ($this->objectSizeInBytes > $partSize) {
            $this->objectPartsCount = intval(
                ceil($this->objectSizeInBytes / $partSize)
            );
        } else {
            // Single download since partSize will be set to full object size.
            $this->objectPartsCount = 1;
            $this->currentPartNo = 1;
        }
    }
}
