<?php
namespace Aws\S3\S3Transfer;

use Aws\HashingStream;
use Aws\PhpHash;
use Aws\ResultInterface;
use Aws\S3\ApplyChecksumMiddleware;
use Aws\S3\S3ClientInterface;
use Aws\S3\S3Transfer\Exception\S3TransferException;
use Aws\S3\S3Transfer\Models\ResumableUpload;
use Aws\S3\S3Transfer\Models\S3TransferManagerConfig;
use Aws\S3\S3Transfer\Models\UploadResult;
use Aws\S3\S3Transfer\Progress\AbstractTransferListener;
use Aws\S3\S3Transfer\Progress\TransferListenerNotifier;
use Aws\S3\S3Transfer\Progress\TransferProgressSnapshot;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\Each;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\LazyOpenStream;
use GuzzleHttp\Psr7\LimitStream;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;
use Throwable;

/**
 * Multipart uploader implementation.
 */
final class MultipartUploader extends AbstractMultipartUploader
{
    private const STREAM_WRAPPER_TYPE_PLAIN_FILE = 'plainfile';
    public const DEFAULT_CHECKSUM_CALCULATION_ALGORITHM = 'crc32';
    private const CHECKSUM_TYPE_FULL_OBJECT = 'FULL_OBJECT';

    /** @var int */
    protected int $calculatedObjectSize;

    /** @var StreamInterface */
    private StreamInterface $body;

    /** @var StreamInterface|string */
    private StreamInterface|string $source;

    /**
     * For custom or default checksum.
     *
     * @var string|null
     */
    protected ?string $requestChecksum;

    /**
     * This will be used for custom or default checksum.
     *
     * @var string|null
     */
    protected ?string $requestChecksumAlgorithm;

    /** @var bool */
    private bool $isFullObjectChecksum;

    /** @var bool */
    private bool $isResuming;

    /** @var ResumableUpload|null */
    private ?ResumableUpload $resumableUpload;

    /**
     * @param S3ClientInterface $s3Client
     * @param array $requestArgs
     * @param string|StreamInterface $source
     * @param array $config
     *  - target_part_size_bytes: (int, optional)
     *  - request_checksum_calculation: (string, optional)
     *  - concurrency: (int, optional)
     * @param TransferListenerNotifier|null $listenerNotifier
     * @param ResumableUpload|null $resumableUpload
     */
    public function __construct(
        S3ClientInterface $s3Client,
        array $requestArgs,
        string|StreamInterface $source,
        array $config = [],
        ?TransferListenerNotifier $listenerNotifier = null,
        ?ResumableUpload $resumableUpload = null,
    ) {
        if (!isset($config['request_checksum_calculation'])) {
            $config['request_checksum_calculation'] = S3TransferManagerConfig::DEFAULT_REQUEST_CHECKSUM_CALCULATION;
        }

        $uploadId = null;
        $partsCompleted = [];
        $currentSnapshot = null;
        $calculatedObjectSize = 0;
        $isFullObjectChecksum = false;
        $this->resumableUpload = $resumableUpload;
        $this->isResuming = $resumableUpload !== null;
        if ($this->isResuming) {
            $config = $resumableUpload->getConfig();
            $uploadId = $resumableUpload->getUploadId();
            $partsCompleted = $resumableUpload->getPartsCompleted();
            $snapshotData = $resumableUpload->getCurrentSnapshot();
            if (!empty($snapshotData)) {
                $currentSnapshot = TransferProgressSnapshot::fromArray(
                    $snapshotData
                );
            }
            $calculatedObjectSize = $resumableUpload->getObjectSize();
            $isFullObjectChecksum = $resumableUpload->isFullObjectChecksum();
        }

        parent::__construct(
            $s3Client,
            $requestArgs,
            $config,
            $uploadId,
            $partsCompleted,
            $currentSnapshot,
            $listenerNotifier
        );
        $this->source = $source;
        $this->body = $this->parseBody($source);
        $this->calculatedObjectSize = $calculatedObjectSize;
        $this->isFullObjectChecksum = $isFullObjectChecksum;
        $this->evaluateCustomChecksum();
    }

    /**
     * @inheritDoc
     *
     * @return PromiseInterface
     */
    protected function createMultipartOperation(): PromiseInterface
    {
        $createMultipartUploadArgs = $this->requestArgs;
        if ($this->requestChecksum !== null) {
            $createMultipartUploadArgs['ChecksumType'] = self::CHECKSUM_TYPE_FULL_OBJECT;
            $createMultipartUploadArgs['ChecksumAlgorithm'] = $this->requestChecksumAlgorithm;
            $this->isFullObjectChecksum = true;
        } elseif ($this->config['request_checksum_calculation'] === 'when_supported') {
            $this->requestChecksumAlgorithm = $createMultipartUploadArgs['ChecksumAlgorithm']
                ?? self::DEFAULT_CHECKSUM_CALCULATION_ALGORITHM;
            $createMultipartUploadArgs['ChecksumAlgorithm'] = $this->requestChecksumAlgorithm;
        }

        // Make sure algorithm with full object is a supported one
        if (($createMultipartUploadArgs['ChecksumType'] ?? '') === self::CHECKSUM_TYPE_FULL_OBJECT) {
            if (stripos($this->requestChecksumAlgorithm, 'crc') !== 0) {
                return Create::rejectionFor(
                    new S3TransferException(
                        "Full object checksum algorithm must be `CRC` family base."
                    )
                );
            }
        }

        if ($this->isResuming && $this->uploadId !== null) {
            // Not need to initialize multipart
            return Create::promiseFor("");
        }

        $this->operationInitiated($createMultipartUploadArgs);
        $command = $this->s3Client->getCommand(
            'CreateMultipartUpload',
            $createMultipartUploadArgs
        );

        return $this->s3Client->executeAsync($command)
            ->then(function (ResultInterface $result) {
                $this->uploadId = $result['UploadId'];
            });
    }

    /**
     * Process a multipart upload operation.
     *
     * @return PromiseInterface
     */
    protected function processMultipartOperation(): PromiseInterface
    {
        $uploadPartCommandArgs = $this->requestArgs;
        $this->calculatedObjectSize = 0;
        $partSize = $this->calculatePartSize();
        $partsCount = ceil($this->getTotalSize() / $partSize);
        $uploadPartCommandArgs['UploadId'] = $this->uploadId;
        // Customer provided checksum
        if ($this->requestChecksum !== null) {
            // To avoid default calculation for individual parts
            $uploadPartCommandArgs['@context']['request_checksum_calculation'] = 'when_required';
            unset($uploadPartCommandArgs['Checksum'. strtoupper($this->requestChecksumAlgorithm)]);
        } elseif ($this->requestChecksumAlgorithm !== null) {
            $uploadPartCommandArgs['ChecksumAlgorithm'] = $this->requestChecksumAlgorithm;
        }

        $promises = $this->createUploadPartPromises(
            $uploadPartCommandArgs,
            $partSize,
            $partsCount,
        );

        return Each::ofLimitAll($promises, $this->config['concurrency']);
    }

    /**
     * @inheritDoc
     *
     * @return PromiseInterface
     */
    protected function completeMultipartOperation(): PromiseInterface
    {
        $this->sortParts();
        $completeMultipartUploadArgs = $this->requestArgs;
        $completeMultipartUploadArgs['UploadId'] = $this->uploadId;
        $completeMultipartUploadArgs['MultipartUpload'] = [
            'Parts' => array_values($this->partsCompleted)
        ];
        $completeMultipartUploadArgs['MpuObjectSize'] = $this->getTotalSize();

        if ($this->isFullObjectChecksum && $this->requestChecksum !== null) {
            $completeMultipartUploadArgs['ChecksumType'] = self::CHECKSUM_TYPE_FULL_OBJECT;
            $completeMultipartUploadArgs[
            'Checksum' . strtoupper($this->requestChecksumAlgorithm)
            ] = $this->requestChecksum;
        }

        $command = $this->s3Client->getCommand(
            'CompleteMultipartUpload',
            $completeMultipartUploadArgs
        );

        return $this->s3Client->executeAsync($command)
            ->then(function (ResultInterface $result) {
                $this->operationCompleted($result);

                // Clean resume file on completion
                if ($this->allowResume()) {
                    $this->resumableUpload?->deleteResumeFile();
                }

                return $result;
            });
    }

    /**
     * @return PromiseInterface
     */
    protected function abortMultipartOperation(): PromiseInterface
    {
        // When resume is enabled then we skip aborting.
        if ($this->allowResume()) {
            return Create::promiseFor("");
        }

        $abortMultipartUploadArgs = $this->requestArgs;
        $abortMultipartUploadArgs['UploadId'] = $this->uploadId;
        $command = $this->s3Client->getCommand(
            'AbortMultipartUpload',
            $abortMultipartUploadArgs
        );

        return $this->s3Client->executeAsync($command);
    }

    /**
     * Sync upload method.
     *
     * @return UploadResult
     */
    public function upload(): UploadResult
    {
        return $this->promise()->wait();
    }

    /**
     * Parses the source into an instance of
     * StreamInterface to be read.
     *
     * @param string|StreamInterface $source
     *
     * @return StreamInterface
     */
    private function parseBody(
        string|StreamInterface $source
    ): StreamInterface
    {
        if (is_string($source)) {
            // Make sure the files exists
            if (!is_readable($source)) {
                throw new \InvalidArgumentException(
                    "The source for this upload must be either a"
                    . " readable file path or a valid stream."
                );
            }
            $body = new LazyOpenStream($source, 'r');
            // To make sure the resource is closed.
            $this->onCompletionCallbacks[] = function () use ($body) {
                $body->close();
            };
        } elseif ($source instanceof StreamInterface) {
            $body = $source;
        } else {
            throw new \InvalidArgumentException(
                "The source must be a valid string file path or a StreamInterface."
            );
        }

        return $body;
    }

    /**
     * Evaluates if custom checksum has been provided,
     * and if so then, the values are placed in the
     * respective properties.
     *
     * @return void
     */
    private function evaluateCustomChecksum(): void
    {
        // Evaluation for custom provided checksums
        $checksumName = ApplyChecksumMiddleware::filterChecksum(
            $this->requestArgs
        );
        if ($checksumName !== null) {
            $this->requestChecksum = $this->requestArgs[$checksumName];
            $this->requestChecksumAlgorithm = str_replace(
                'Checksum',
                '',
                $checksumName
            );
            $this->requestChecksumAlgorithm = strtolower(
                $this->requestChecksumAlgorithm
            );
        } else {
            $this->requestChecksum = null;
            $this->requestChecksumAlgorithm = null;
        }
    }

    /**
     * @param array $uploadPartCommandArgs
     * @param int $partSize
     * @param int $partsCount
     *
     * @return \Generator
     */
    private function createUploadPartPromises(
        array $uploadPartCommandArgs,
        int $partSize,
        int $partsCount
    ): \Generator
    {
        $bytesRead = 0;
        $isSeekable = $this->body->isSeekable()
            && $this->body->getMetadata('wrapper_type')
            === self::STREAM_WRAPPER_TYPE_PLAIN_FILE;

        if ($isSeekable) {
            $this->body->rewind();
        }

        $partNo = 0;
        while (!$this->body->eof()) {
            if ($isSeekable) {
                $partBody = new LimitStream(
                    new LazyOpenStream(
                        $this->body->getMetadata('uri'),
                        'r'
                    ),
                    $partSize,
                    $bytesRead,
                );
            } else {
                $body = new LimitStream(
                    $this->body,
                    $partSize,
                    $bytesRead,
                );
                $body = $this->decorateWithHashes($body, $uploadPartCommandArgs);
                $partBody = Utils::streamFor();
                Utils::copyToStream($body, $partBody);
            }

            $bodyLength = $partBody->getSize();
            if ($bodyLength === 0) {
                $partBody->close();
                break;
            }

            $partNo++;
            $bytesRead += $bodyLength;

            $uploadPartCommandArgs['PartNumber'] = $partNo;
            $uploadPartCommandArgs['ContentLength'] = $bodyLength;
            // Attach body
            if ($isSeekable) {
                $partBody->rewind();
            }
            $uploadPartCommandArgs['Body'] = $partBody;

            $this->calculatedObjectSize += $bodyLength;
            if ($partNo > self::PART_MAX_NUM) {
                return Create::rejectionFor(
                    "The max number of parts has been exceeded. " .
                    "Max = " . self::PART_MAX_NUM
                );
            }

            if ($isSeekable && $partNo > $partsCount) {
                return Create::rejectionFor(
                    "The current part `$partNo` is over "
                    . "the expected number of parts `$partsCount`"
                );
            }

            $command = $this->s3Client->getCommand(
                'UploadPart',
                $uploadPartCommandArgs
            );

            // Advance if behind
            if ($bytesRead < $this->body->tell()) {
                $this->body->seek($bytesRead);
            }

            if (isset($this->partsCompleted[$partNo])) {
                // Part already uploaded
                continue;
            }

            yield $this->s3Client->executeAsync($command)
                ->then(function (ResultInterface $result)
                use ($command, $partBody) {
                    $partBody->close();
                    // To make sure we don't continue when a failure occurred
                    if ($this->currentSnapshot->getReason() !== null) {
                        throw $this->currentSnapshot->getReason();
                    }

                    $partData = $this->collectPart(
                        $result,
                        $command
                    );

                    // Part Upload Completed Event
                    $this->partCompleted(
                        $command['ContentLength'],
                        $command->toArray(),
                        $partData,
                    );
                })->otherwise(function (Throwable $e) use ($partBody) {
                    $partBody->close();
                    $this->partFailed($e);

                    throw $e;
                });
        }
    }

    /**
     * @param int $partSize
     * @param array $requestArgs
     * @param array $partData
     *
     * @return void
     */
    protected function partCompleted(
        int $partSize,
        array $requestArgs,
        array $partData
    ): void
    {
        $newSnapshot = new TransferProgressSnapshot(
            $this->currentSnapshot->getIdentifier(),
            $this->currentSnapshot->getTransferredBytes() + $partSize,
            $this->currentSnapshot->getTotalBytes(),
            $this->currentSnapshot->getResponse(),
            $this->currentSnapshot->getReason(),
        );

        $this->currentSnapshot = $newSnapshot;

        // Persist resume state if allowed
        if ($this->allowResume()) {
            $this->persistResumeState($partData);
        }

        $this->listenerNotifier?->bytesTransferred([
            AbstractTransferListener::REQUEST_ARGS_KEY => $requestArgs,
            AbstractTransferListener::PROGRESS_SNAPSHOT_KEY => $this->currentSnapshot
        ]);
    }

    /**
     * Resume works just when the source is a file path and is enabled.
     *
     * @return bool
     */
    private function allowResume(): bool
    {
        return ($this->config['resume_enabled'] ?? false)
            && is_string($this->source);
    }

    /**
     * Persist the current upload state to a resume file.
     *
     * @param array $partData
     */
    private function persistResumeState(array $partData): void
    {
        if ($this->resumableUpload === null) {
            if ($this->config['resume_file_path'] ?? false) {
                $resumeFilePath = $this->config['resume_file_path'];
            } else {
                $resumeFilePath = $this->source . '.resume';
            }

            $sourceSize = $this->body->getSize()
                ?? $this->calculatedObjectSize;
            $this->resumableUpload = new ResumableUpload(
                $resumeFilePath,
                $this->requestArgs,
                $this->config,
                $this->currentSnapshot->toArray(),
                $this->uploadId,
                $this->partsCompleted,
                $this->source,
                $sourceSize,
                $this->calculatePartSize(),
                $this->isFullObjectChecksum
            );
        }

        // Update the completed parts and current snapshot
        $this->resumableUpload->markPartCompleted(
            $partData['PartNumber'],
            $partData
        );
        $this->resumableUpload->updateCurrentSnapshot(
            $this->currentSnapshot->toArray()
        );

        // Save to file
        $this->resumableUpload->toFile();
    }

    /**
     * @return int
     */
    protected function getTotalSize(): int
    {
        if ($this->calculatedObjectSize > 0) {
            return $this->calculatedObjectSize;
        }

        return $this->body->getSize();
    }

    /**
     * @param ResultInterface $result
     *
     * @return UploadResult
     */
    protected function createResponse(ResultInterface $result): UploadResult
    {
        return new UploadResult(
            $result->toArray()
        );
    }

    /**
     * @param StreamInterface $stream
     * @param array $data
     *
     * @return StreamInterface
     */
    private function decorateWithHashes(
        StreamInterface $stream,
        array &$data
    ): StreamInterface
    {
        // Decorate source with a hashing stream
        $hash = new PhpHash('sha256');
        return new HashingStream($stream, $hash, function ($result) use (&$data) {
            $data['ContentSHA256'] = bin2hex($result);
        });
    }
}
