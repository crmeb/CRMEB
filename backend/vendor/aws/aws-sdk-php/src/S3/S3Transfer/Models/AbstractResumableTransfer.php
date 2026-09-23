<?php

namespace Aws\S3\S3Transfer\Models;

use Aws\S3\S3Transfer\Exception\S3TransferException;

abstract class AbstractResumableTransfer
{
    protected const VERSION = '1.0';
    protected const SIGNATURE_CHECKSUM_ALGORITHM = 'sha256';

    /** @var string */
    protected string $resumeFilePath;

    /** @var array */
    protected array $requestArgs;

    /** @var array */
    protected array $config;

    /** @var array */
    protected array $currentSnapshot;

    /**
     * @param string $resumeFilePath
     * @param array $requestArgs The request arguments used for the transfer
     * @param array $config The config used in the request
     * @param array $currentSnapshot The current progress snapshot
     */
    public function __construct(
        string $resumeFilePath,
        array $requestArgs,
        array $config,
        array $currentSnapshot,
    ) {
        // Resume files must end in .resume
        if (!str_ends_with($resumeFilePath, '.resume')) {
            $resumeFilePath .= '.resume';
        }

        $this->resumeFilePath = $resumeFilePath;
        $this->requestArgs = $requestArgs;
        $this->config = $config;
        $this->currentSnapshot = $currentSnapshot;
    }

    /**
     * Serialize the resumable state to JSON format.
     *
     * @return string JSON-encoded state
     */
    public abstract function toJson(): string;

    /**
     * Deserialize a resumable state from JSON format.
     *
     * @param string $json JSON-encoded state
     * @return self
     * @throws S3TransferException If the JSON is invalid or missing required fields
     */
    public static abstract function fromJson(string $json): self;

    /**
     * Load a resumable state from a file.
     *
     * @param string $filePath Path to the resume file
     * @return self
     * @throws S3TransferException If the file cannot be read or is invalid
     */
    public static abstract function fromFile(string $filePath): self;

    /**
     * Save the resumable state to a file.
     * When a file path is not provided by default it will use
     * the `resumeFilePath` property.
     *
     * @param string|null $filePath Path where the resume file should be saved
     */
    public function toFile(?string $filePath = null): void
    {
        $saveFileToPath = $filePath ?? $this->resumeFilePath;

        // Ensure directory exists
        $resumeDir = dirname($saveFileToPath);
        if (!is_dir($resumeDir)
            && !mkdir($resumeDir, 0755, true)) {
            throw new S3TransferException(
                "Failed to create resume directory: $resumeDir"
            );
        }

        $json = $this->toJson();
        $signature = hash(self::SIGNATURE_CHECKSUM_ALGORITHM, $json);
        $dataWithSignature = json_encode([
            'signature' => $signature,
            'data' => json_decode($json, true)
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $result = file_put_contents($saveFileToPath, $dataWithSignature, LOCK_EX);
        if ($result === false) {
            throw new S3TransferException(
                "Failed to write resume file: $saveFileToPath"
            );
        }
    }

    /**
     * @param string|null $filePath
     *
     * @return void
     */
    public function deleteResumeFile(?string $filePath = null): void
    {
        $resumeFilePath = $filePath ?? $this->resumeFilePath;
        if (file_exists($resumeFilePath)) {
            unlink($resumeFilePath);
        }
    }

    /**
     * @return string
     */
    public function getResumeFilePath(): string
    {
        return $this->resumeFilePath;
    }

    /**
     * @return array
     */
    public function getRequestArgs(): array
    {
        return $this->requestArgs;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @return string
     */
    public function getBucket(): string
    {
        return $this->requestArgs['Bucket'];
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->requestArgs['Key'];
    }

    /**
     * @return array
     */
    public function getCurrentSnapshot(): array
    {
        return $this->currentSnapshot;
    }

    /**
     * Update the current snapshot.
     *
     * @param array $snapshot The new snapshot data
     */
    public function updateCurrentSnapshot(array $snapshot): void
    {
        $this->currentSnapshot = $snapshot;
    }

    /**
     * Check if a file path is a valid resume file.
     *
     * @param string $filePath
     * @return bool
     */
    public static function isResumeFile(string $filePath): bool
    {
        // Check file extension
        if (!str_ends_with($filePath, '.resume')) {
            return false;
        }

        // Check if file exists and is readable
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return false;
        }

        // Validate file content by attempting to parse it
        try {
            $json = file_get_contents($filePath);
            if ($json === false) {
                return false;
            }

            $data = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return false;
            }

            // Check for required version field
            return isset($data['data']) && isset($data['signature']);
        } catch (\Exception $e) {
            return false;
        }
    }
}
