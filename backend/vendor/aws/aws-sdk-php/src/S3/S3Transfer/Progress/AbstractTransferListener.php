<?php

namespace Aws\S3\S3Transfer\Progress;

abstract class AbstractTransferListener
{
    public const REQUEST_ARGS_KEY = 'request_args';
    public const PROGRESS_SNAPSHOT_KEY = 'progress_snapshot';
    public const REASON_KEY = 'reason';

    /**
     * @param array $context
     * - request_args: (array) The request arguments that will be provided
     *   as part of the request initialization.
     * - progress_snapshot: (TransferProgressSnapshot) The transfer snapshot holder.
     *
     * @return void
     */
    public function transferInitiated(array $context): void {}

    /**
     * @param array $context
     * - request_args: (array) The request arguments that will be provided
     *   as part of the operation that originated the bytes transferred event.
     * - progress_snapshot: (TransferProgressSnapshot) The transfer snapshot holder.
     *
     * @return bool true to notify successful handling otherwise false.
     */
    public function bytesTransferred(array $context): bool {
        return true;
    }

    /**
     * @param array $context
     * - request_args: (array) The request arguments that will be provided
     *   as part of the operation that originated the bytes transferred event.
     * - progress_snapshot: (TransferProgressSnapshot) The transfer snapshot holder.
     *
     * @return void
     */
    public function transferComplete(array $context): void {}

    /**
     * @param array $context
     * - request_args: (array) The request arguments that will be provided
     *    as part of the operation that originated the bytes transferred event.
     * - progress_snapshot: (TransferProgressSnapshot) The transfer snapshot holder.
     * - reason: (Throwable) The exception originated by the transfer failure.
     *
     * @return void
     */
    public function transferFail(array $context): void {}

    /**
     * To provide an order on which listener is notified first.
     * By default, it will provide a neutral value.
     *
     * @return int
     */
    public function priority(): int
    {
        return 0;
    }
}
