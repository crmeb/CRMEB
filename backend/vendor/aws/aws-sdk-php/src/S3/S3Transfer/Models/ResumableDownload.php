<?php

namespace Aws\S3\S3Transfer\Models;

use Aws\S3\S3Transfer\Exception\S3TransferException;

/**
 * Represents the state of a resumable multipart download.
 * This class can be serialized to/from JSON to persist download progress.
 */
final class ResumableDownload extends AbstractResumableTransfer
{
    /** @var array */
    private array $initialRequestResult;

    /** @var array */
    private array $partsCompleted;

    /** @var int */
    private int $totalNumberOfParts;

    /** @var string|null */
    private ?string $temporaryFile;

    /** @var string */
    private string $eTag;

    /** @var int */
    private int $objectSizeInBytes;

    /** @var int */
    private int $fixedPartSize;

    /** @var string */
    private string $destination;

    /**
     * @param array $requestArgs The request arguments used for the download
     * @param array $config The config used in the request
     * @param array $initialRequestResult The response from the initial request
     * @param array $currentSnapshot The current progress snapshot
     * @param array $partsCompleted Map of completed part numbers (partNo => true)
     * @param int $totalNumberOfParts Total number of parts in the download
     * @param string|null $temporaryFile Path to the temporary file being downloaded to
     * @param string $eTag ETag of the S3 object for consistency verification
     * @param int $objectSizeInBytes Total size of the object in bytes
     * @param int $fixedPartSize Size of each part in bytes
     * @param string $destination Final destination path for the downloaded file
     */
    public function __construct(
        string $resumeFilePath,
        array $requestArgs,
        array $config,
        array $currentSnapshot,
        array $initialRequestResult,
        array $partsCompleted,
        int $totalNumberOfParts,
        ?string $temporaryFile,
        string $eTag,
        int $objectSizeInBytes,
        int $fixedPartSize,
        string $destination
    ) {
        parent::__construct(
            $resumeFilePath,
            $requestArgs,
            $config,
            $currentSnapshot,
        );
        $this->initialRequestResult = $initialRequestResult;
        $this->partsCompleted = $partsCompleted;
        $this->totalNumberOfParts = $totalNumberOfParts;
        $this->temporaryFile = $temporaryFile;
        $this->eTag = $eTag;
        $this->objectSizeInBytes = $objectSizeInBytes;
        $this->fixedPartSize = $fixedPartSize;
        $this->destination = $destination;
    }

    /**
     * Serialize the resumable download state to JSON format.
     *
     * @return string JSON-encoded state
     */
    public function toJson(): string
    {
        $data = [
            'version' => self::VERSION,
            'resumeFilePath' => $this->resumeFilePath,
            'requestArgs' => $this->requestArgs,
            'config' => $this->config,
            'initialRequestResult' => $this->initialRequestResult,
            'currentSnapshot' => $this->currentSnapshot,
            'partsCompleted' => $this->partsCompleted,
            'totalNumberOfParts' => $this->totalNumberOfParts,
            'temporaryFile' => $this->temporaryFile,
            'eTag' => $this->eTag,
            'objectSizeInBytes' => $this->objectSizeInBytes,
            'fixedPartSize' => $this->fixedPartSize,
            'destination' => $this->destination,
        ];

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Deserialize a resumable download state from JSON format.
     *
     * @param string $json JSON-encoded state
     * @return self
     * @throws S3TransferException If the JSON is invalid or missing required fields
     */
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new S3TransferException(
                'Failed to parse resume file: ' . json_last_error_msg()
            );
        }

        if (!is_array($data)) {
            throw new S3TransferException(
                'Invalid resume file format: expected JSON object'
            );
        }

        // Validate version
        if (!isset($data['version']) || $data['version'] !== self::VERSION) {
            throw new S3TransferException(
                'Invalid or unsupported resume file version'
            );
        }

        // Validate required fields
        $requiredFields = [
            'resumeFilePath',
            'requestArgs',
            'config',
            'initialRequestResult',
            'currentSnapshot',
            'partsCompleted',
            'totalNumberOfParts',
            'temporaryFile',
            'eTag',
            'objectSizeInBytes',
            'fixedPartSize',
            'destination',
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
            $data['initialRequestResult'],
            $data['partsCompleted'],
            $data['totalNumberOfParts'],
            $data['temporaryFile'],
            $data['eTag'],
            $data['objectSizeInBytes'],
            $data['fixedPartSize'],
            $data['destination']
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
     * @return array
     */
    public function getInitialRequestResult(): array
    {
        return $this->initialRequestResult;
    }

    /**
     * @return array
     */
    public function getPartsCompleted(): array
    {
        return $this->partsCompleted;
    }

    /**
     * @return int
     */
    public function getTotalNumberOfParts(): int
    {
        return $this->totalNumberOfParts;
    }

    /**
     * @return string|null
     */
    public function getTemporaryFile(): ?string
    {
        return $this->temporaryFile;
    }

    /**
     * @return string
     */
    public function getETag(): string
    {
        return $this->eTag;
    }

    /**
     * @return int
     */
    public function getObjectSizeInBytes(): int
    {
        return $this->objectSizeInBytes;
    }

    /**
     * @return int
     */
    public function getFixedPartSize(): int
    {
        return $this->fixedPartSize;
    }

    /**
     * @return string
     */
    public function getDestination(): string
    {
        return $this->destination;
    }

    /**
     * Mark a part as completed.
     *
     * @param int $partNumber The part number to mark as completed
     */
    public function markPartCompleted(int $partNumber): void
    {
        $this->partsCompleted[$partNumber] = true;
    }
}
