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

namespace Google\ApiCore;

/**
 * @internal
 */
trait PollingTrait
{
    /**
     * @param callable $pollCallable The call to poll. Must return a boolean indicating whether the
     *        operation has completed, and the polling loop can be terminated.
     * @param array $options {
     *                       Options for configuring the polling behaviour.
     *
     *     @type int $initialPollDelayMillis The initial polling interval to use, in milliseconds.
     *     @type int $pollDelayMultiplier Multiplier applied to the polling interval on each retry.
     *     @type int $maxPollDelayMillis The maximum polling interval to use, in milliseconds.
     *     @type int $totalPollTimeoutMillis The maximum amount of time to continue polling, in milliseconds.
     * }
     * @return bool
     */
    private function poll(callable $pollCallable, array $options)
    {
        $currentPollDelayMillis = $options['initialPollDelayMillis'];
        $pollDelayMultiplier = $options['pollDelayMultiplier'];
        $maxPollDelayMillis = $options['maxPollDelayMillis'];
        $totalPollTimeoutMillis = $options['totalPollTimeoutMillis'];

        $hasTotalPollTimeout = $totalPollTimeoutMillis > 0.0;
        $endTime = $this->getCurrentTimeMillis() + $totalPollTimeoutMillis;

        while (true) {
            if ($hasTotalPollTimeout && $this->getCurrentTimeMillis() > $endTime) {
                return false;
            }
            $this->sleepMillis($currentPollDelayMillis);
            if ($pollCallable()) {
                return true;
            }
            $currentPollDelayMillis = min([
                $currentPollDelayMillis * $pollDelayMultiplier,
                $maxPollDelayMillis
            ]);
        }
    }

    /**
     * Protected to allow overriding for tests
     *
     * @return float Current time in milliseconds
     */
    protected function getCurrentTimeMillis()
    {
        return microtime(true) * 1000.0;
    }

    /**
     * Protected to allow overriding for tests
     *
     * @param int $millis
     */
    protected function sleepMillis(int $millis)
    {
        usleep($millis * 1000);
    }
}
