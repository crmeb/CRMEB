<?php

namespace Aws\S3\S3Transfer\Models;

use Aws\S3\S3Transfer\Progress\AbstractTransferListener;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;

final class UploadRequest extends AbstractTransferRequest
{
    public static array $configKeys = [
        'multipart_upload_threshold_bytes' => 'int',
        'target_part_size_bytes' => 'int',
        'track_progress' => 'bool',
        'concurrency' => 'int',
        'request_checksum_calculation' => 'string',
        'resume_enabled' => 'bool',
        'resume_file_path' => 'string',
    ];

    /** @var StreamInterface|string  */
    private StreamInterface|string $source;

    /** @var array  */
    private array $uploadRequestArgs;

    /**
     * @param string|StreamInterface $source
     * @param array $uploadRequestArgs The putObject request arguments.
     * Required parameters would be:
     * - Bucket: (string, required)
     * - Key: (string, required)
     * @param array $config The config options for this upload operation.
     * - multipart_upload_threshold_bytes: (int, optional)
     *   To override the default threshold for when to use multipart upload.
     * - target_part_size_bytes: (int, optional) To override the default
     *   target part size in bytes.
     * - track_progress: (bool, optional) To override the default option for
     *   enabling progress tracking. If this option is resolved as true and
     *   a progressTracker parameter is not provided then, a default implementation
     *   will be resolved. This option is intended to make the operation to use
     *   a default progress tracker implementation when $progressTracker is null.
     * - concurrency: (int, optional) To override default value for concurrency.
     * - request_checksum_calculation: (string, optional, defaulted to `when_supported`)
     * - resume_enabled: (bool): To enable resuming a multipart download when a
     *     failure occurs.
     * - resume_file_path (string, optional): To override the default resume file
     *     location to be generated. If specified the file name must end in `.resume`
     *      otherwise it will be added automatically.
     * @param AbstractTransferListener[]|null $listeners
     * @param AbstractTransferListener|null $progressTracker
     *
     */
    public function __construct(
        StreamInterface|string $source,
        array $uploadRequestArgs,
        array $config = [],
        array $listeners = [],
        ?AbstractTransferListener $progressTracker  = null
    ) {
        parent::__construct($listeners, $progressTracker, $config);
        $this->source = $source;
        $this->uploadRequestArgs = $uploadRequestArgs;
    }

    /**
     * Get the source.
     *
     * @return StreamInterface|string
     */
    public function getSource(): StreamInterface|string
    {
        return $this->source;
    }

    /**
     * Get the put object request.
     *
     * @return array
     */
    public function getUploadRequestArgs(): array
    {
        return $this->uploadRequestArgs;
    }

    /**
     * Helper method for validating the given source.
     *
     * @return void
     */
    public function validateSource(): void
    {
        if (is_string($this->getSource()) && !is_readable($this->getSource())) {
            throw new InvalidArgumentException(
                "Invalid source `". $this->getSource() . "` provided. \n".
                "Please provide a valid readable file path or a valid stream as source."
            );
        }
    }

    /**
     * Helper method for validating required parameters.
     *
     * @param string|null $customMessage
     * @return void
     */
    public function validateRequiredParameters(
        ?string $customMessage = null
    ): void
    {
        $requiredParametersWithArgs = [
            'Bucket' => $this->uploadRequestArgs['Bucket'] ?? null,
            'Key' => $this->uploadRequestArgs['Key'] ?? null,
        ];
        foreach ($requiredParametersWithArgs as $key => $value) {
            if (empty($value)) {
                if ($customMessage !== null) {
                    throw new InvalidArgumentException($customMessage);
                }

                // Fallback to default error message
                throw new InvalidArgumentException(
                    "The `$key` parameter must be provided as part of the request arguments."
                );
            }
        }
    }
}
