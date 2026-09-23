<?php

namespace Aws\S3\S3Transfer\Utils;

use Aws\S3\S3Transfer\Progress\AbstractTransferListener;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;

final class StreamDownloadHandler extends AbstractDownloadHandler
{
    /** @var StreamInterface|null */
    private ?StreamInterface $stream;

    /**
     * @param StreamInterface|null $stream
     */
    public function __construct(?StreamInterface $stream = null)
    {
        if (is_null($stream)) {
            $stream = Utils::streamFor(
                fopen('php://temp', 'r+')
            );
        } else {
            // Position at the end
            $stream->seek($stream->getSize());
        }

        $this->stream = $stream;
    }

    /**
     * @return int
     */
    public function priority(): int
    {
        return -1;
    }

    /**
     * @inheritDoc
     */
    public function bytesTransferred(array $context): bool
    {
        $snapshot = $context[AbstractTransferListener::PROGRESS_SNAPSHOT_KEY];
        $response = $snapshot->getResponse();
        $partBody = $response['Body'];

        if ($partBody->isSeekable()) {
            $partBody->rewind();
        }

        Utils::copyToStream(
            $partBody,
            $this->stream
        );

        return true;
    }

    /**
     * @param array $context
     *
     * @return void
     */
    public function transferComplete(array $context): void
    {
        $this->stream->rewind();
    }

    /**
     * @param array $context
     *
     * @return void
     */
    public function transferFail(array $context): void
    {
        $this->stream->close();
        $this->stream = null;
    }

    /**
     * @inheritDoc
     *
     * @return StreamInterface
     */
    public function getHandlerResult(): StreamInterface
    {
        return $this->stream;
    }

    /**
     * @inheritDoc
     */
    public function isConcurrencySupported(): bool
    {
        return false;
    }
}
