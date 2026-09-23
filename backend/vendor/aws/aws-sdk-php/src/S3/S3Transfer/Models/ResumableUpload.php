<?php
namespace Aws\S3\S3Transfer\Models;

use Aws\S3\S3Transfer\Exception\S3TransferException;

final class ResumableUpload extends AbstractResumableTransfer
{
    /** @var string */
    private string $uploadId;

    /** @var array */
    private array $partsCompleted;

    /** @var string */
    private string $source;

    /** @var int */
    private int $objectSize;

    /** @var int */
    private int $partSize;

    /** @var bool */
    private bool $isFullObjectChecksum;

    /**
     * @param string $resumeFilePath
     * @param array $requestArgs
     * @param array $config
     * @param array $currentSnapshot
     * @param string $uploadId
     * @param array $partsCompleted
     * @param string $source
     * @param int $objectSize
     * @param int $partSize
     * @param bool $isFullObjectChecksum
     */
    public function __construct(
        string $resumeFilePath,
        array $requestArgs,
        array $config,
        array $currentSnapshot,
        string $uploadId,
        array $partsCompleted,
        string $source,
        int $objectSize,
        int $partSize,
        bool $isFullObjectChecksum
    ) {
        parent::__construct(
            $resumeFilePath,
            $requestArgs,
            $config,
            $currentSnapshot,
        );
        $this->uploadId = $uploadId;
        $this->partsCompleted = $partsCompleted;
        $this->source = $source;
        $this->objectSize = $objectSize;
        $this->partSize = $partSize;
        $this->isFullObjectChecksum = $isFullObjectChecksum;
    }

    /**
     * @return string
     */
    public function toJson(): string
    {
        return json_encode([
            'version' => self::VERSION,
            'resumeFilePath' => $this->resumeFilePath,
            'requestArgs' => $this->requestArgs,
            'config' => $this->config,
            'uploadId' => $this->uploadId,
            'partsCompleted' => $this->partsCompleted,
            'currentSnapshot' => $this->currentSnapshot,
            'source' => $this->source,
            'objectSize' => $this->objectSize,
            'partSize' => $this->partSize,
            'isFullObjectChecksum' => $this->isFullObjectChecksum,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param string $json
     *
     * @return self
     */
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new S3TransferException('Failed to parse resume file: ' . json_last_error_msg());
        }

        $requiredFields = [
            'version',
            'resumeFilePath',
            'requestArgs',
            'config',
            'currentSnapshot',
            'uploadId',
            'partsCompleted',
            'source',
            'objectSize',
            'partSize',
            'isFullObjectChecksum',
        ];
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $data)) {
                throw new S3TransferException(
                    "Invalid resume file: missing required field '$field'"
                );
            }
        }

        return new self(
            $data['resumeFilePath'],
            $data['requestArgs'],
            $data['config'],
            $data['currentSnapshot'],
            $data['uploadId'],
            $data['partsCompleted'],
            $data['source'],
            $data['objectSize'],
            $data['partSize'],
            $data['isFullObjectChecksum'],
        );
    }

    /**
     * @param string $filePath
     *
     * @return self
     */
    public static function fromFile(string $filePath): self
    {
        if (!file_exists($filePath)) {
            throw new S3TransferException(
                "Resume file does not exist: $filePath"
            );
        }
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new S3TransferException(
                "Failed to read resume file: $filePath"
            );
        }

        $fileData = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new S3TransferException(
                'Failed to parse resume file: ' . json_last_error_msg()
            );
        }

        // Validate signature if present
        if (isset($fileData['signature'], $fileData['data'])) {
            $expectedSignature = hash(
                self::SIGNATURE_CHECKSUM_ALGORITHM,
                json_encode(
                    $fileData['data'],
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                )
            );

            if (!hash_equals($fileData['signature'], $expectedSignature)) {
                throw new S3TransferException(
                    'Resume file integrity check failed: signature mismatch'
                );
            }

            $json = json_encode($fileData['data']);
        } else {
            // Legacy format without signature
            $json = $content;
        }

        return self::fromJson($json);
    }

    /**
     * @return string
     */
    public function getUploadId(): string
    {
        return $this->uploadId;
    }

    /**
     * @return array
     */
    public function getPartsCompleted(): array
    {
        return $this->partsCompleted;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @return int
     */
    public function getObjectSize(): int
    {
        return $this->objectSize;
    }

    /**
     * @return int
     */
    public function getPartSize(): int
    {
        return $this->partSize;
    }

    /**
     * @return bool
     */
    public function isFullObjectChecksum(): bool
    {
        return $this->isFullObjectChecksum;
    }

    /**
     * Mark a part as completed.
     *
     * @param int $partNumber The part number to mark as completed
     */
    public function markPartCompleted(int $partNumber, array $part): void
    {
        $this->partsCompleted[$partNumber] = $part;
    }
}
