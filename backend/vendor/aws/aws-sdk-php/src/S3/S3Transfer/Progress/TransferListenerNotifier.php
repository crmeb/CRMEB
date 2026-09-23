<?php

namespace Aws\S3\S3Transfer\Progress;

final class TransferListenerNotifier extends AbstractTransferListener
{
    /** @var AbstractTransferListener[] */
    private array $listeners;

    /**
     * @param array $listeners
     */
    public function __construct(array $listeners = [])
    {
        usort($listeners, fn($a, $b) => $a->priority() <=> $b->priority());
        foreach ($listeners as $listener) {
            if (!$listener instanceof AbstractTransferListener) {
                throw new \InvalidArgumentException(
                    "Listener must implement " . AbstractTransferListener::class . "."
                );
            }
        }

        $this->listeners = $listeners;
    }

    /**
     * @param AbstractTransferListener $listener
     *
     * @return void
     */
    public function addListener(AbstractTransferListener $listener): void
    {
        $this->listeners[] = $listener;
    }

    /**
     * @inheritDoc
     *
     * @return void
     */
    public function transferInitiated(array $context): void
    {
        foreach ($this->listeners as $listener) {
            $listener->transferInitiated($context);
        }
    }

    /**
     * @inheritDoc
     */
    public function bytesTransferred(array $context): bool
    {
        foreach ($this->listeners as $listener) {
            $listener->bytesTransferred($context);
        }

        return true;
    }

    /**
     * @inheritDoc
     *
     * @return void
     */
    public function transferComplete(array $context): void
    {
        foreach ($this->listeners as $listener) {
            $listener->transferComplete($context);
        }
    }

    /**
     * @inheritDoc
     *
     * @return void
     */
    public function transferFail(array $context): void
    {
        foreach ($this->listeners as $listener) {
            $listener->transferFail($context);
        }
    }
}
