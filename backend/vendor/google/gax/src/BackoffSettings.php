<?php
/*
 * Copyright 2016, Google Inc.
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
namespace Google\GAX;

use InvalidArgumentException;

/**
 * Holds the parameters used for exponential backoff logic.
 *
 * The "total timeout" parameter has ultimate control over how long the logic should
 * keep trying the remote call until it gives up completely. The higher the total
 * timeout, the more retries can be attempted. The other settings are considered
 * more advanced.
 *
 * Retry delay and timeout start at specific values, and are tracked separately from
 * each other. The very first call (before any retries) will use the initial timeout.
 *
 * If the last remote call is a failure, then the retrier will wait for the current
 * retry delay before attempting another call, and then the retry delay will be
 * multiplied by the retry delay multiplier for the next failure. The timeout will
 * not be affected, except in the case where the timeout would result in a deadline
 * past the total timeout; in that circumstance, a new timeout value is computed
 * which will terminate the call when the total time is up.
 *
 * If the last remote call is a timeout, then the retrier will compute a new timeout
 * and make another call. The new timeout is computed by multiplying the current
 * timeout by the timeout multiplier, but if that results in a deadline after the
 * total timeout, then a new timeout value is computed which will terminate the call
 * when the total time is up.
 */
class BackoffSettings
{
    private $initialRetryDelayMillis;
    private $retryDelayMultiplier;
    private $maxRetryDelayMillis;
    private $initialRpcTimeoutMillis;
    private $rpcTimeoutMultiplier;
    private $maxRpcTimeoutMillis;
    private $totalTimeoutMillis;

    /**
     * Constructs an instance from snake-case parameters.
     *
     * @param array $settings {
     *     Required. Settings for configuring the backoff settings
     *
     *     @type integer $initial_retry_delay_millis The initial delay of retry in milliseconds.
     *     @type integer $retry_delay_multiplier The exponential multiplier of retry delay.
     *     @type integer $max_retry_delay_millis The max delay of retry in milliseconds.
     *     @type integer $initial_rpc_timeout_millis The initial timeout of rpc call in milliseconds.
     *     @type integer $rpc_timeout_multiplier The exponential multiplier of rpc timeout.
     *     @type integer $max_rpc_timeout_millis The max timout of rpc call in milliseconds.
     *     @type integer $total_timeout_millis The max accumulative timeout in total.
     * }
     */
    public static function fromSnakeCase($settings)
    {
        $camelCaseSettings = [];
        foreach ($settings as $key => $value) {
            $wordsKey = str_replace('_', ' ', $key);
            $camelCaseKey = str_replace(' ', '', ucwords($wordsKey));
            $camelCaseSettings[lcfirst($camelCaseKey)] = $value;
        }
        return new BackoffSettings($camelCaseSettings);
    }

    /**
     * Constructs an instance.
     *
     * @param array $settings {
     *     Required. Settings for configuring the backoff settings
     *
     *     @type integer $initialRetryDelayMillis The initial delay of retry in milliseconds.
     *     @type integer $retryDelayMultiplier The exponential multiplier of retry delay.
     *     @type integer $maxRetryDelayMillis The max delay of retry in milliseconds.
     *     @type integer $initialRpcTimeoutMillis The initial timeout of rpc call in milliseconds.
     *     @type integer $rpcTimeoutMultiplier The exponential multiplier of rpc timeout.
     *     @type integer $maxRpcTimeoutMillis The max timout of rpc call in milliseconds.
     *     @type integer $totalTimeoutMillis The max accumulative timeout in total.
     * }
     */
    public function __construct($settings)
    {
        self::validate($settings);
        $this->initialRetryDelayMillis = $settings['initialRetryDelayMillis'];
        $this->retryDelayMultiplier = $settings['retryDelayMultiplier'];
        $this->maxRetryDelayMillis = $settings['maxRetryDelayMillis'];
        $this->initialRpcTimeoutMillis = $settings['initialRpcTimeoutMillis'];
        $this->rpcTimeoutMultiplier = $settings['rpcTimeoutMultiplier'];
        $this->maxRpcTimeoutMillis = $settings['maxRpcTimeoutMillis'];
        $this->totalTimeoutMillis = $settings['totalTimeoutMillis'];
    }

    public function getInitialRetryDelayMillis()
    {
        return $this->initialRetryDelayMillis;
    }

    public function getRetryDelayMultiplier()
    {
        return $this->retryDelayMultiplier;
    }

    public function getMaxRetryDelayMillis()
    {
        return $this->maxRetryDelayMillis;
    }

    public function getInitialRpcTimeoutMillis()
    {
        return $this->initialRpcTimeoutMillis;
    }

    public function getRpcTimeoutMultiplier()
    {
        return $this->rpcTimeoutMultiplier;
    }

    public function getMaxRpcTimeoutMillis()
    {
        return $this->maxRpcTimeoutMillis;
    }

    public function getTotalTimeoutMillis()
    {
        return $this->totalTimeoutMillis;
    }

    private static function validate($settings)
    {
        $requiredFields = ['initialRetryDelayMillis',
            'retryDelayMultiplier', 'maxRetryDelayMillis', 'initialRpcTimeoutMillis',
            'rpcTimeoutMultiplier', 'maxRpcTimeoutMillis', 'totalTimeoutMillis'];
        foreach ($requiredFields as $field) {
            if (empty($settings[$field])) {
                throw new InvalidArgumentException("$field is required for BackoffSettings");
            }
        }
    }
}
