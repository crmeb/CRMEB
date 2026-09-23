<?php

namespace Aws\S3\S3Transfer\Progress;

use Throwable;

final class DirectoryTransferProgressSnapshot
{
    /** @var string */
    private string $identifier;

    /** @var int */
    private int $transferredBytes;

    /** @var int */
    private int $totalBytes;

    /** @var int */
    private int $transferredFiles;

    /** @var int */
    private int $totalFiles;

    /** @var array|null */
    private ?array $response;

    /** @var Throwable|string|null */
    private Throwable|string|null $reason;

    public function __construct(
        string $identifier,
        int $transferredBytes,
        int $totalBytes,
        int $transferredFiles,
        int $totalFiles,
        ?array $response = null,
        Throwable|string|null $reason = null,
    ) {
        $this->identifier = $identifier;
        $this->transferredBytes = $transferredBytes;
        $this->totalBytes = $totalBytes;
        $this->transferredFiles = $transferredFiles;
        $this->totalFiles = $totalFiles;
        $this->response = $response;
        $this->reason = $reason;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getTransferredBytes(): int
    {
        return $this->transferredBytes;
    }

    public function getTotalBytes(): int
    {
        return $this->totalBytes;
    }

    public function getTransferredFiles(): int
    {
        return $this->transferredFiles;
    }

    public function getTotalFiles(): int
    {
        return $this->totalFiles;
    }

    public function getResponse(): ?array
    {
        return $this->response;
    }

    public function ratioTransferred(): float
    {
        if ($this->totalBytes === 0) {
            return 0;
        }

        return $this->transferredBytes / $this->totalBytes;
    }

    public function getReason(): Throwable|string|null
    {
        return $this->reason;
    }

    public function toArray(): array
    {
        return [
            'identifier' => $this->identifier,
            'transferredBytes' => $this->transferredBytes,
            'totalBytes' => $this->totalBytes,
            'transferredFiles' => $this->transferredFiles,
            'totalFiles' => $this->totalFiles,
            'response' => $this->response,
            'reason' => $this->reason,
        ];
    }

    public function withResponse(array $response): DirectoryTransferProgressSnapshot
    {
        return new self(
            $this->identifier,
            $this->transferredBytes,
            $this->totalBytes,
            $this->transferredFiles,
            $this->totalFiles,
            $response,
            $this->reason,
        );
    }

    public function withTotals(int $totalBytes, int $totalFiles): DirectoryTransferProgressSnapshot
    {
        return new self(
            $this->identifier,
            $this->transferredBytes,
            $totalBytes,
            $this->transferredFiles,
            $totalFiles,
            $this->response,
            $this->reason,
        );
    }

    public function withProgress(int $transferredBytes, int $transferredFiles): DirectoryTransferProgressSnapshot
    {
        return new self(
            $this->identifier,
            $transferredBytes,
            $this->totalBytes,
            $transferredFiles,
            $this->totalFiles,
            $this->response,
            $this->reason,
        );
    }

    public static function fromArray(array $data): DirectoryTransferProgressSnapshot
    {
        return new self(
            $data['identifier'] ?? '',
            $data['transferredBytes'] ?? 0,
            $data['totalBytes'] ?? 0,
            $data['transferredFiles'] ?? 0,
            $data['totalFiles'] ?? 0,
            $data['response'] ?? null,
            $data['reason'] ?? null,
        );
    }
}
