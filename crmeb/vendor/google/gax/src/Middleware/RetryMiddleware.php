<?php
/*
 * Copyright 2018 Google LLC
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are
 * met:
 *
 *     * Redistributions of source code must retain the above copyright
 * notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above
 * copyright notice, this list of conditions and the following disclaimer
 * in the documentation and/or other materials provided with the
 * distribution.
 *     * Neither the name of Google Inc. nor the names of its
 * contributors may be used to endorse or promote products derived from
 * this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
namespace Google\ApiCore\Middleware;

use Google\ApiCore\ApiException;
use Google\ApiCore\ApiStatus;
use Google\ApiCore\Call;
use Google\ApiCore\RetrySettings;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Middleware that adds retry functionality.
 */
class RetryMiddleware
{
    /** @var callable */
    private $nextHandler;

    /** @var RetrySettings */
    private $retrySettings;

    /** @var float|null */
    private $deadlineMs;

    public function __construct(
        callable $nextHandler,
        RetrySettings $retrySettings,
        $deadlineMs = null
    ) {
        $this->nextHandler = $nextHandler;
        $this->retrySettings = $retrySettings;
        $this->deadlineMs = $deadlineMs;
    }

    /**
     * @param Call $call
     * @param array $options
     *
     * @return PromiseInterface
     */
    public function __invoke(Call $call, array $options)
    {
        $nextHandler = $this->nextHandler;

        if (!isset($options['timeoutMillis'])) {
            // default to "noRetriesRpcTimeoutMillis" when retries are disabled, otherwise use "initialRpcTimeoutMillis"
            if (!$this->retrySettings->retriesEnabled() && $this->retrySettings->getNoRetriesRpcTimeoutMillis() > 0) {
                $options['timeoutMillis'] = $this->retrySettings->getNoRetriesRpcTimeoutMillis();
            } elseif ($this->retrySettings->getInitialRpcTimeoutMillis() > 0) {
                $options['timeoutMillis'] = $this->retrySettings->getInitialRpcTimeoutMillis();
            }
        }

        // Call the handler immediately if retry settings are disabled.
        if (!$this->retrySettings->retriesEnabled()) {
            return $nextHandler($call, $options);
        }

        return $nextHandler($call, $options)->then(null, function ($e) use ($call, $options) {
            if (!$e instanceof ApiException) {
                throw $e;
            }

            if (!in_array($e->getStatus(), $this->retrySettings->getRetryableCodes())) {
                throw $e;
            }

            return $this->retry($call, $options, $e->getStatus());
        });
    }

    /**
     * @param Call $call
     * @param array $options
     * @param string $status
     *
     * @return PromiseInterface
     * @throws ApiException
     */
    private function retry(Call $call, array $options, string $status)
    {
        $delayMult = $this->retrySettings->getRetryDelayMultiplier();
        $maxDelayMs = $this->retrySettings->getMaxRetryDelayMillis();
        $timeoutMult = $this->retrySettings->getRpcTimeoutMultiplier();
        $maxTimeoutMs = $this->retrySettings->getMaxRpcTimeoutMillis();
        $totalTimeoutMs = $this->retrySettings->getTotalTimeoutMillis();

        $delayMs = $this->retrySettings->getInitialRetryDelayMillis();
        $timeoutMs = $options['timeoutMillis'];
        $currentTimeMs = $this->getCurrentTimeMs();
        $deadlineMs = $this->deadlineMs ?: $currentTimeMs + $totalTimeoutMs;

        if ($currentTimeMs >= $deadlineMs) {
            throw new ApiException(
                'Retry total timeout exceeded.',
                \Google\Rpc\Code::DEADLINE_EXCEEDED,
                ApiStatus::DEADLINE_EXCEEDED
            );
        }

        $delayMs = min($delayMs * $delayMult, $maxDelayMs);
        $timeoutMs = min(
            $timeoutMs * $timeoutMult,
            $maxTimeoutMs,
            $deadlineMs - $this->getCurrentTimeMs()
        );

        $nextHandler = new RetryMiddleware(
            $this->nextHandler,
            $this->retrySettings->with([
                'initialRetryDelayMillis' => $delayMs,
            ]),
            $deadlineMs
        );

        // Set the timeout for the call
        $options['timeoutMillis'] = $timeoutMs;

        return $nextHandler(
            $call,
            $options
        );
    }

    protected function getCurrentTimeMs()
    {
        return microtime(true) * 1000.0;
    }
}
