<?php

namespace Aws\S3\S3Transfer\Models;

use Aws\Arn\ArnParser;
use Aws\S3\S3Transfer\Progress\AbstractTransferListener;
use InvalidArgumentException;

final class DownloadDirectoryRequest extends AbstractTransferRequest
{
    public static array $configKeys = [
        's3_prefix' => 'string',
        'filter' => 'callable',
        'download_object_request_modifier' => 'callable',
        'failure_policy' => 'callable',
        'max_concurrency' => 'int',
        'track_progress' => 'bool',
        'target_part_size_bytes' => 'int',
        'list_objects_v2_args' => 'array',
        'fails_when_destination_exists' => 'bool',
    ];
    public const DEFAULT_MAX_CONCURRENCY = 100;

    /** @var string */
    private string $sourceBucket;

    /** @var string */
    private string $destinationDirectory;

    /** @var array  */
    private readonly array $downloadRequestArgs;

    /**
     * @param string $sourceBucket The bucket from where the files are going to be
     * downloaded from.
     * @param string $destinationDirectory The destination path where the downloaded
     * files will be placed in.
     * @param array $downloadRequestArgs
     * @param array $config The config options for this download directory operation.
     *  - s3_prefix: (string, optional) This parameter will be considered just if
     *    not provided as part of the list_objects_v2_args config option.
     *  - filter: (Closure, optional) A callable which will receive an object key as
     *    parameter and should return true or false in order to determine
     *    whether the object should be downloaded.
     *  - download_object_request_modifier: (Closure, optional) A function that will
     *    be invoked right before the download request is performed and that will
     *    receive as parameter the request arguments for each request.
     *  - failure_policy: (Closure, optional) A function that will be invoked
     *    on a download failure and that will receive as parameters:
     *    - $requestArgs: (array) The arguments for the request that originated
     *      the failure.
     *    - $downloadDirectoryRequestArgs: (array) The arguments for the download
     *      directory request.
     *    - $reason: (Throwable) The exception that originated the request failure.
     *    - $downloadDirectoryResponse: (DownloadDirectoryResult) The download response
     *      to that point in the upload process.
     *  - track_progress: (bool, optional) Overrides the config option set
     *    in the transfer manager instantiation to decide whether transfer
     *    progress should be tracked.
     *  - target_part_size_bytes: (int, optional) The part size in bytes
     *    to be used in a range multipart download.
     *  - fails_when_destination_exists: (bool) Whether to fail when a destination
     *       file exists.
     *  - max_concurrency: (int, optional) The max number of concurrent downloads.
     *  - list_objects_v2_args: (array, optional) The arguments to be included
     *    as part of the listObjectV2 request in order to fetch the objects to
     *    be downloaded. The most common arguments would be:
     *    - MaxKeys: (int) Sets the maximum number of keys returned in the response.
     *    - Prefix: (string) To limit the response to keys that begin with the
     *      specified prefix.
     * @param AbstractTransferListener[] $listeners Directory-level listeners that receive directory snapshots.
     * @param AbstractTransferListener|null $progressTracker Directory-level progress tracker.
     * @param array $singleObjectListeners Per-object listeners that receive single-object snapshots.
     */
    public function __construct(
        string $sourceBucket,
        string $destinationDirectory,
        array $downloadRequestArgs = [],
        array $config = [],
        array $listeners = [],
        ?AbstractTransferListener $progressTracker = null,
        array $singleObjectListeners = []
    ) {
        parent::__construct(
            $listeners,
            $progressTracker,
            $config,
            $singleObjectListeners
        );
        if (ArnParser::isArn($sourceBucket)) {
            $sourceBucket = ArnParser::parse($sourceBucket)->getResource();
        }

        $this->sourceBucket = $sourceBucket;
        $this->destinationDirectory = $destinationDirectory;
        $this->downloadRequestArgs = $downloadRequestArgs;
    }

    /**
     * @return string
     */
    public function getSourceBucket(): string
    {
        return $this->sourceBucket;
    }

    /**
     * @return string
     */
    public function getDestinationDirectory(): string
    {
        return $this->destinationDirectory;
    }

    /**
     * @return array
     */
    public function getDownloadRequestArgs(): array
    {
        return $this->downloadRequestArgs;
    }

    /**
     * Helper method to validate the destination directory exists.
     *
     * @return void
     */
    public function validateDestinationDirectory(): void
    {
        if (!file_exists($this->destinationDirectory)) {
            mkdir($this->destinationDirectory, 0755, true);
        }

        if (!is_dir($this->destinationDirectory)) {
            throw new InvalidArgumentException(
                "Destination directory `$this->destinationDirectory` is not a directory."
            );
        }
    }
}
