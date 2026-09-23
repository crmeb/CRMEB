<?php

namespace Aws\S3\S3Transfer\Utils;

use Aws\S3\ApplyChecksumMiddleware;
use Aws\S3\S3Transfer\AbstractMultipartDownloader;
use Aws\S3\S3Transfer\Exception\FileDownloadException;
use Aws\S3\S3Transfer\Progress\AbstractTransferListener;

final class FileDownloadHandler extends AbstractDownloadHandler
    implements ResumableDownloadHandlerInterface
{
    private const IDENTIFIER_LENGTH = 8;
    private const TEMP_INFIX = '.s3tmp.';
    private const RESUME_SUFFIX = '.resume';
    private const MAX_UNIQUE_ID_ATTEMPTS = 100;

    /** @var string */
    private string $destination;

    /** @var bool */
    private bool $failsWhenDestinationExists;

    /** @var string|null */
    private ?string $temporaryFilePath;

    /** @var int|null */
    private ?int $fixedPartSize;

    /** @var bool */
    private bool $resumeEnabled;

    /** @var mixed|null */
    private mixed $handle;

    /** @var bool */
    private bool $transferFailed;

    /**
     * @param string $destination
     * @param bool $failsWhenDestinationExists
     * @param bool $resumeEnabled
     * @param string|null $temporaryFilePath
     * @param int|null $fixedPartSize
     */
    public function __construct(
        string $destination,
        bool $failsWhenDestinationExists,
        bool $resumeEnabled = false,
        ?string $temporaryFilePath = null,
        ?int $fixedPartSize = null,
    ) {
        $this->destination = $destination;
        $this->failsWhenDestinationExists = $failsWhenDestinationExists;
        $this->resumeEnabled = $resumeEnabled;
        $this->temporaryFilePath = $temporaryFilePath;
        $this->fixedPartSize = $fixedPartSize;
        $this->handle = null;
        $this->transferFailed = false;
    }

    /**
     * @return string
     */
    public function getDestination(): string
    {
        return $this->destination;
    }

    /**
     * @return bool
     */
    public function isFailsWhenDestinationExists(): bool
    {
        return $this->failsWhenDestinationExists;
    }

    /**
     * @param array $context
     *
     * @return void
     */
    public function transferInitiated(array $context): void
    {
        $this->validateDestination();
        $this->ensureDirectoryExists();
        // temporary destination may have been set by resume
        if (empty($this->temporaryFilePath)) {
            $this->temporaryFilePath = $this->generateTemporaryFilePath();
        } else {
            $this->openExistingFile();
        }
    }

    /**
     * Open an existing temporary file for resuming.
     * Opens in 'r+' mode which allows reading and writing without truncating.
     *
     * @return void
     */
    private function openExistingFile(): void
    {
        if ($this->handle !== null) {
            return;
        }

        $handle = fopen($this->temporaryFilePath, 'r+');

        if ($handle === false) {
            throw new FileDownloadException(
                "Failed to open existing temporary file '{$this->temporaryFilePath}' for resuming."
            );
        }

        $this->handle = $handle;
    }

    /**
     * @param array $context
     *
     * @return bool
     */
    public function bytesTransferred(array $context): bool
    {
        if ($this->transferFailed) {
            return false;
        }

        $snapshot = $context[AbstractTransferListener::PROGRESS_SNAPSHOT_KEY];
        $response = $snapshot->getResponse();

        if ($this->handle === null) {
            $this->fixedPartSize = $response['ContentLength'];
            $this->initializeDestination($response);
        }

        if ($this->handle === null) {
            throw new FileDownloadException(
                "Failed to initialize destination for downloading."
            );
        }

        return $this->writePartToDestinationHandle($response);
    }

    /**
     * @param array $context
     *
     * @return void
     */
    public function transferComplete(array $context): void
    {
        $this->closeDestinationHandle();
        $this->replaceDestinationFile();
    }

    /**
     * @param array $context
     *
     * @return void
     */
    public function transferFail(array $context): void
    {
        $this->transferFailed = true;
        $this->closeDestinationHandle();
        $this->cleanupAfterFailure($context);
    }

    /**
     * @param array $response
     *
     * @return void
     */
    public function initializeDestination(array $response): void
    {
        $objectSize = AbstractMultipartDownloader::computeObjectSizeFromContentRange(
            $response['ContentRange'] ?? ""
        );

        $this->createTruncatedFile($objectSize);
    }

    /**
     * @param array $response
     *
     * @return bool
     */
    private function writePartToDestinationHandle(array $response): bool
    {
        $contentRange = $response['ContentRange'] ?? null;
        if ($contentRange === null) {
            throw new FileDownloadException(
                "Unable to get content range from response."
            );
        }

        $partNo = (int) ceil(
            AbstractMultipartDownloader::getRangeTo($contentRange) / $this->fixedPartSize
        );
        $position = ($partNo - 1) * $this->fixedPartSize;

        if (!flock($this->handle, LOCK_EX)) {
            throw new FileDownloadException("Failed to acquire file lock.");
        }

        try {
            fseek($this->handle, $position);

            $body = $response['Body'];
            // In case body was already consumed by another process
            if ($body->isSeekable()) {
                $body->rewind();
            }

            // Try to validate a checksum when writting to disk
            $checksumParameter = ApplyChecksumMiddleware::filterChecksum(
                $response
            );
            $hashContext = null;
            if ($checksumParameter !== null) {
                $checksumAlgorithm = strtolower(
                    str_replace(
                        "Checksum",
                        "",
                        $checksumParameter
                    )
                );
                $checksumAlgorithm = $checksumAlgorithm === 'crc32'
                    ? 'crc32b'
                    : $checksumAlgorithm;
                $hashContext = hash_init($checksumAlgorithm);
            }

            while (!$body->eof()) {
                $chunk = $body->read(self::READ_BUFFER_SIZE);

                if (fwrite($this->handle, $chunk) === false) {
                    throw new FileDownloadException("Failed to write data to temporary file.");
                }

                if ($hashContext !== null) {
                    hash_update($hashContext, $chunk);
                }
            }

            if ($hashContext !== null) {
                $calculatedChecksum = base64_encode(
                    hash_final($hashContext, true)
                );
                if ($calculatedChecksum !== $response[$checksumParameter]) {
                    throw new FileDownloadException(
                        "Checksum mismatch when writing part to destination file."
                    );
                }
            }

            fflush($this->handle);

            return true;
        } finally {
            flock($this->handle, LOCK_UN);
        }
    }

    /**
     * @return void
     */
    private function closeDestinationHandle(): void
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
            $this->handle = null;
        }
    }

    /**
     * @return string
     */
    public function getHandlerResult(): string
    {
        return $this->destination;
    }

    /**
     * @return void
     */
    private function validateDestination(): void
    {
        if ($this->failsWhenDestinationExists && file_exists($this->destination)) {
            throw new FileDownloadException(
                "The destination '{$this->destination}' already exists."
            );
        }

        if (is_dir($this->destination)) {
            throw new FileDownloadException(
                "The destination '{$this->destination}' can't be a directory."
            );
        }
    }

    /**
     * @return void
     */
    private function ensureDirectoryExists(): void
    {
        $directory = dirname($this->destination);

        if (!is_dir($directory) && !mkdir($directory, 0755, true)
            && !is_dir($directory)) {
            throw new FileDownloadException(
                "Failed to create directory '{$directory}'."
            );
        }
    }

    /**
     * @return string
     */
    private function generateTemporaryFilePath(): string
    {
        for ($attempt = 0; $attempt < self::MAX_UNIQUE_ID_ATTEMPTS; $attempt++) {
            $uniqueId = $this->generateUniqueIdentifier();
            $temporaryPath = $this->destination . self::TEMP_INFIX . $uniqueId;

            if (!file_exists($temporaryPath)) {
                return $temporaryPath;
            }
        }

        throw new FileDownloadException(
            "Unable to generate a unique temporary file name after " . self::MAX_UNIQUE_ID_ATTEMPTS . " attempts."
        );
    }

    /**
     * @return string
     */
    private function generateUniqueIdentifier(): string
    {
        $uniqueId = uniqid();

        if (strlen($uniqueId) > self::IDENTIFIER_LENGTH) {
            return substr($uniqueId, 0, self::IDENTIFIER_LENGTH);
        }

        return str_pad($uniqueId, self::IDENTIFIER_LENGTH, "0");
    }

    /**
     * @param int $size
     *
     * @return void
     */
    private function createTruncatedFile(int $size): void
    {
        $handle = fopen($this->temporaryFilePath, 'w+');

        if ($handle === false) {
            throw new FileDownloadException(
                "Failed to open temporary file '{$this->temporaryFilePath}' for writing."
            );
        }

        $this->handle = $handle;

        if (!ftruncate($this->handle, $size)) {
            throw new FileDownloadException(
                "Failed to allocate {$size} bytes for temporary file."
            );
        }
    }

    /**
     * @return void
     */
    private function replaceDestinationFile(): void
    {
        if (file_exists($this->destination)) {
            if ($this->failsWhenDestinationExists) {
                throw new FileDownloadException(
                    "The destination '{$this->destination}' already exists."
                );
            }

            if (!unlink($this->destination)) {
                throw new FileDownloadException(
                    "Failed to delete existing file '{$this->destination}'."
                );
            }
        }

        if (!rename($this->temporaryFilePath, $this->destination)) {
            throw new FileDownloadException(
                "Unable to rename the file '{$this->temporaryFilePath}' to '{$this->destination}'."
            );
        }
    }

    /**
     * @param array $context
     *
     * @return void
     */
    private function cleanupAfterFailure(array $context): void
    {
        if (!$this->resumeEnabled && file_exists($this->temporaryFilePath)) {
            unlink($this->temporaryFilePath);
            return;
        }

        $reason = $context[self::REASON_KEY] ?? '';
        $isDestinationExistsError = str_contains(
            $reason,
            "The destination '{$this->destination}' already exists."
        );

        if (file_exists($this->destination) && !$isDestinationExistsError) {
            unlink($this->destination);
        }
    }

    /**
     * @inheritDoc
     */
    public function isConcurrencySupported(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    public function getResumeFilePath(): string
    {
        return $this->temporaryFilePath . self::RESUME_SUFFIX;
    }

    /**
     * @return string
     */
    public function getTemporaryFilePath(): string
    {
        return $this->temporaryFilePath;
    }

    /**
     * @return int
     */
    public function getFixedPartSize(): int
    {
        return $this->fixedPartSize;
    }
}
