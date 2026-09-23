<?php

namespace Aws\S3\S3Transfer\Progress;

use Throwable;

final class TransferProgressSnapshot
{
    /** @var string */
    private string $identifier;

    /** @var int */
    private int $transferredBytes;

    /** @var int */
    private int $totalBytes;

    /** @var array|null */
    private array|null $response;

    /** @var Throwable|string|null */
    private Throwable|string|null $reason;

    /**
     * @param string $identifier
     * @param int $transferredBytes
     * @param int $totalBytes
     * @param array|null $response
     * @param Throwable|string|null $reason
     */
    public function __construct(
        string $identifier,
        int $transferredBytes,
        int $totalBytes,
        ?array $response = null,
        Throwable|string|null $reason = null,
    ) {
        $this->identifier = $identifier;
        $this->transferredBytes = $transferredBytes;
        $this->totalBytes = $totalBytes;
        $this->response = $response;
        $this->reason = $reason;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return int
     */
    public function getTransferredBytes(): int
    {
        return $this->transferredBytes;
    }

    /**
     * @return int
     */
    public function getTotalBytes(): int
    {
        return $this->totalBytes;
    }

    /**
     * @return array|null
     */
    public function getResponse(): array|null
    {
        return $this->response;
    }

    /**
     * @return float
     */
    public function ratioTransferred(): float
    {
        if ($this->totalBytes === 0) {
            // Unable to calculate ratio
            return 0;
        }

        return $this->transferredBytes / $this->totalBytes;
    }

    /**
     * @return Throwable|string|null
     */
    public function getReason(): Throwable|string|null
    {
        return $this->reason;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'identifier' => $this->identifier,
            'transferredBytes' => $this->transferredBytes,
            'totalBytes' => $this->totalBytes,
            'reason' => $this->reason,
            'response' => $this->response,
        ];
    }

    /**
     * @param array $response
     *
     * @return TransferProgressSnapshot
     */
    public function withResponse(array $response): TransferProgressSnapshot
    {
        return new self(
            $this->identifier,
            $this->transferredBytes,
            $this->totalBytes,
            $response,
        );
    }

    /**
     * @param array $data
     *
     * @return TransferProgressSnapshot
     */
    public static function fromArray(array $data): TransferProgressSnapshot
    {
        return new self(
            $data['identifier'] ?? null,
            $data['transferredBytes'] ?? 0,
            $data['totalBytes'] ?? 0,
            $data['response'] ?? null,
            $data['reason'] ?? null
        );
    }
}
