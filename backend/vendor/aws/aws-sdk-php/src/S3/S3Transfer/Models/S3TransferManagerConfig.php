<?php

namespace Aws\S3\S3Transfer\Models;

final class S3TransferManagerConfig
{
    public const DEFAULT_TARGET_PART_SIZE_BYTES = 8388608; // 8MB
    public const DEFAULT_MULTIPART_UPLOAD_THRESHOLD_BYTES = 16777216; // 16MB
    public const DEFAULT_REQUEST_CHECKSUM_CALCULATION = 'when_supported';
    public const DEFAULT_RESPONSE_CHECKSUM_VALIDATION = 'when_supported';
    public const DEFAULT_MULTIPART_DOWNLOAD_TYPE = 'part';
    public const DEFAULT_CONCURRENCY = 5;
    private const DEFAULT_TRACK_PROGRESS = false;

    /** @var int  */
    private int $targetPartSizeBytes;

    /** @var int  */
    private int $multipartUploadThresholdBytes;

    /** @var string */
    private string $requestChecksumCalculation;

    /** @var string */
    private string $responseChecksumValidation;

    /** @var string */
    private string $multipartDownloadType;

    /** @var int */
    private int $concurrency;

    /** @var bool */
    private bool $trackProgress;

    /** @var string|null */
    private ?string $defaultRegion;

    /**
     * @param int $targetPartSizeBytes
     * @param int $multipartUploadThresholdBytes
     * @param string $requestChecksumCalculation
     * @param string $responseChecksumValidation
     * @param string $multipartDownloadType
     * @param int $concurrency
     * @param bool $trackProgress
     * @param string|null $defaultRegion
     */
    public function __construct(
        int $targetPartSizeBytes,
        int $multipartUploadThresholdBytes,
        string $requestChecksumCalculation,
        string $responseChecksumValidation,
        string $multipartDownloadType,
        int $concurrency,
        bool $trackProgress,
        ?string $defaultRegion
    ) {
        $this->targetPartSizeBytes = $targetPartSizeBytes;
        $this->multipartUploadThresholdBytes = $multipartUploadThresholdBytes;
        $this->requestChecksumCalculation = $requestChecksumCalculation;
        $this->responseChecksumValidation = $responseChecksumValidation;
        $this->multipartDownloadType = $multipartDownloadType;
        $this->concurrency = $concurrency;
        $this->trackProgress = $trackProgress;
        $this->defaultRegion = $defaultRegion;
    }

    /** $config:
     * - target_part_size_bytes: (int, default=(8388608 `8MB`))
     *   The minimum part size to be used in a multipart upload/download.
     * - multipart_upload_threshold_bytes: (int, default=(16777216 `16 MB`))
     *   The threshold to decided whether a multipart upload is needed.
     * - request_checksum_calculation: (string, default=`when_supported`)
     *   To decide whether a checksum validation will be applied to the response.
     * - response_checksum_validation: (string, default=`when_supported`)
     * - multipart_download_type: (string, default='part')
     *   The download type to be used in a multipart download.
     * - concurrency: (int, default=5)
     *   Maximum number of concurrent operations allowed during a multipart
     *   upload/download.
     * - track_progress: (bool, default=false)
     *   To enable progress tracker in a multipart upload/download, and or
     *   a directory upload/download operation.
     * - default_region: (string, default="us-east-2")
     */
    public static function fromArray(array $config): self {
        return new self(
            $config['target_part_size_bytes']
            ?? self::DEFAULT_TARGET_PART_SIZE_BYTES,
            $config['multipart_upload_threshold_bytes']
            ?? self::DEFAULT_MULTIPART_UPLOAD_THRESHOLD_BYTES,
                $config['request_checksum_calculation']
            ?? self::DEFAULT_REQUEST_CHECKSUM_CALCULATION,
            $config['response_checksum_validation']
            ?? self::DEFAULT_RESPONSE_CHECKSUM_VALIDATION,
            $config['multipart_download_type']
            ?? self::DEFAULT_MULTIPART_DOWNLOAD_TYPE,
            $config['concurrency']
            ?? self::DEFAULT_CONCURRENCY,
            $config['track_progress'] ?? self::DEFAULT_TRACK_PROGRESS,
            $config['default_region'] ?? null
        );
    }

    /**
     * @return int
     */
    public function getTargetPartSizeBytes(): int
    {
        return $this->targetPartSizeBytes;
    }

    /**
     * @return int
     */
    public function getMultipartUploadThresholdBytes(): int
    {
        return $this->multipartUploadThresholdBytes;
    }

    /**
     * @return string
     */
    public function getRequestChecksumCalculation(): string
    {
        return $this->requestChecksumCalculation;
    }

    /**
     * @return string
     */
    public function getResponseChecksumValidation(): string
    {
        return $this->responseChecksumValidation;
    }

    /**
     * @return string
     */
    public function getMultipartDownloadType(): string
    {
        return $this->multipartDownloadType;
    }

    /**
     * @return int
     */
    public function getConcurrency(): int
    {
        return $this->concurrency;
    }

    /**
     * @return bool
     */
    public function isTrackProgress(): bool
    {
        return $this->trackProgress;
    }

    /**
     * @return string|null
     */
    public function getDefaultRegion(): ?string
    {
        return $this->defaultRegion;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'target_part_size_bytes' => $this->targetPartSizeBytes,
            'multipart_upload_threshold_bytes' => $this->multipartUploadThresholdBytes,
            'request_checksum_calculation' => $this->requestChecksumCalculation,
            'response_checksum_validation' => $this->responseChecksumValidation,
            'multipart_download_type' => $this->multipartDownloadType,
            'concurrency' => $this->concurrency,
            'track_progress' => $this->trackProgress,
            'default_region' => $this->defaultRegion,
        ];
    }
}
