<?php

namespace Aws\S3\S3Transfer\Models;

use Aws\S3\S3Transfer\Exception\S3TransferException;
use Aws\S3\S3Transfer\Progress\AbstractTransferListener;
use Aws\S3\S3Transfer\S3TransferManager;
use Aws\S3\S3Transfer\Utils\AbstractDownloadHandler;
use Aws\S3\S3Transfer\Utils\FileDownloadHandler;
use Aws\S3\S3Transfer\Utils\StreamDownloadHandler;

final class DownloadRequest extends AbstractTransferRequest
{
    public static array $configKeys = [
        'response_checksum_validation' => 'string',
        'multipart_download_type' => 'string',
        'track_progress' => 'bool',
        'concurrency' => 'int',
        'resume_enabled' => 'bool',
        'resume_file_path' => 'string',
        'target_part_size_bytes' => 'int',
    ];

    /** @var string|array|null */
    private string|array|null $source;

    /** @var array */
    private array $downloadRequestArgs;

    /** @var AbstractDownloadHandler|null */
    private ?AbstractDownloadHandler $downloadHandler;

    /**
     * @param string|array|null $source The object to be downloaded from S3.
     * It can be either a string with a S3 URI or an array with a Bucket and Key
     * properties set.
     * @param array $downloadRequestArgs
     * @param array $config The configuration to be used for this operation:
     *  - multipart_download_type: (string, optional)
     *    Overrides the resolved value from the transfer manager config.
     *  - response_checksum_validation: (string, optional) Overrides the resolved
     *    value from transfer manager config for whether checksum validation
     *    should be done. This option will be considered just if ChecksumMode
     *    is not present in the request args.
     *  - track_progress: (bool) Overrides the config option set in the transfer
     *    manager instantiation to decide whether transfer progress should be
     *    tracked.
     *  - target_part_size_bytes: (int) The part size in bytes to be used
     *    in a range multipart download. If this parameter is not provided
     *    then it fallbacks to the transfer manager `target_part_size_bytes`
     *    config value.
     *  - resume_enabled: (bool): To enable resuming a multipart download when a
     *    failure occurs.
     *  - resume_file_path (string, optional): To override the default resume file
     *    location to be generated. If specified the file name must end in `.resume`
     *     otherwise it will be added automatically.
     * @param AbstractDownloadHandler|null $downloadHandler
     * @param AbstractTransferListener[]|null $listeners
     * @param AbstractTransferListener|null $progressTracker
     */
    public function __construct(
        string|array|null $source,
        array $downloadRequestArgs = [],
        array $config = [],
        ?AbstractDownloadHandler $downloadHandler = null,
        array $listeners = [],
        ?AbstractTransferListener $progressTracker = null
    ) {
        parent::__construct($listeners, $progressTracker, $config);
        $this->source = $source;
        $this->downloadRequestArgs = $downloadRequestArgs;
        $this->config = $config;
        if ($downloadHandler === null) {
            $downloadHandler = new StreamDownloadHandler();
        }
        $this->downloadHandler = $downloadHandler;
    }

    /**
     * @param DownloadRequest $downloadRequest
     * @param FileDownloadHandler $downloadHandler
     *
     * @return self
     */
    public static function fromDownloadRequestAndDownloadHandler(
        DownloadRequest $downloadRequest,
        FileDownloadHandler $downloadHandler
    ): self
    {
        return new self(
            $downloadRequest->getSource(),
            $downloadRequest->getObjectRequestArgs(),
            $downloadRequest->getConfig(),
            $downloadHandler,
            $downloadRequest->getListeners(),
            $downloadRequest->getProgressTracker()
        );
    }

    /**
     * @return array|string|null
     */
    public function getSource(): array|string|null
    {
        return $this->source;
    }

    /**
     * @return array
     */
    public function getObjectRequestArgs(): array
    {
        return $this->downloadRequestArgs;
    }

    /**
     * @return AbstractDownloadHandler
     */
    public function getDownloadHandler(): AbstractDownloadHandler
    {
        return $this->downloadHandler;
    }

    /**
     * Helper method to normalize the source as an array with:
     *  - Bucket
     *  - Key
     *
     * @return array
     */
    public function normalizeSourceAsArray(): array
    {
        // If source is null then fall back to getObjectRequest.
        $source = $this->getSource() ?? [
            'Bucket' => $this->downloadRequestArgs['Bucket'] ?? null,
            'Key'    => $this->downloadRequestArgs['Key'] ?? null,
        ];
        if (is_string($source)) {
            $sourceAsArray = S3TransferManager::s3UriAsBucketAndKey($source);
        } elseif (is_array($source)) {
            $sourceAsArray = $source;
        } else {
            throw new S3TransferException(
                "Unsupported source type `" . gettype($source) . "`"
            );
        }

        foreach (['Bucket', 'Key'] as $reqKey) {
            if (empty($sourceAsArray[$reqKey])) {
                throw new \InvalidArgumentException(
                    "`$reqKey` is required but not provided in "
                    . implode(', ', array_keys($sourceAsArray)) . "."
                );
            }
        }

        return $sourceAsArray;
    }
}
