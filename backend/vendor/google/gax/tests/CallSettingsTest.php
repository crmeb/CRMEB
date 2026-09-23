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
namespace Google\GAX\UnitTests;

use Google\GAX\BackoffSettings;
use Google\GAX\CallSettings;
use Google\GAX\PageStreamingDescriptor;
use Google\GAX\RetrySettings;
use PHPUnit_Framework_TestCase;
use Grpc;

class CallSettingsTest extends PHPUnit_Framework_TestCase
{
    const SERVICE_NAME = 'test.interface.v1.api';

    private static function buildInputConfig()
    {
        return [
            'interfaces' => [
                CallSettingsTest::SERVICE_NAME => [
                    'retry_codes' => [
                        'foo_retry' => ['code_a', 'code_b'],
                        'bar_retry' => ['code_c']
                    ],
                    'retry_params' => [
                        'default' => [
                            'initial_retry_delay_millis' => 100,
                            'retry_delay_multiplier' => 1.2,
                            'max_retry_delay_millis' => 1000,
                            'initial_rpc_timeout_millis' => 300,
                            'rpc_timeout_multiplier' => 1.3,
                            'max_rpc_timeout_millis' => 3000,
                            'total_timeout_millis' => 30000
                        ]
                    ],
                    'methods' => [
                        'SimpleMethod' => [
                            'retry_codes_name' => 'foo_retry',
                            'retry_params_name' => 'default'
                        ],
                        'PageStreamingMethod' => [
                            'retry_codes_name' => 'bar_retry',
                            'retry_params_name' => 'default'
                        ]
                    ]
                ]
            ]
        ];
    }

    public function testConstructSettings()
    {
        $statusCodes = ['code_a' => 'code_val_a',
            'code_b' => 'code_val_b',
            'code_c' => 'code_val_c'
        ];
        $inputConfig = CallSettingsTest::buildInputConfig();

        $defaultCallSettings =
                CallSettings::load(
                    CallSettingsTest::SERVICE_NAME,
                    $inputConfig,
                    [],
                    $statusCodes,
                    30
                );
        $simpleMethod = $defaultCallSettings['simpleMethod'];
        $this->assertEquals(30, $simpleMethod->getTimeoutMillis());
        $simpleMethodRetry = $simpleMethod->getRetrySettings();
        $this->assertEquals(['code_val_a', 'code_val_b'], $simpleMethodRetry->getRetryableCodes());
        $this->assertEquals(100, $simpleMethodRetry->getBackoffSettings()->getInitialRetryDelayMillis());
        $pageStreamingMethod = $defaultCallSettings['pageStreamingMethod'];
        $pageStreamingMethodRetry = $pageStreamingMethod->getRetrySettings();
        $this->assertEquals(['code_val_c'], $pageStreamingMethodRetry->getRetryableCodes());
    }

    public function testConstructSettingsOverride()
    {
        $statusCodes = ['code_a' => 'code_val_a',
            'code_b' => 'code_val_b',
            'code_c' => 'code_val_c'
        ];
        $inputConfig = CallSettingsTest::buildInputConfig();

        // Turn off retries for simpleMethod
        $retryingOverride = ['simpleMethod' => null];
        $defaultCallSettings =
                CallSettings::load(
                    CallSettingsTest::SERVICE_NAME,
                    $inputConfig,
                    $retryingOverride,
                    $statusCodes,
                    30
                );
        $simpleMethod = $defaultCallSettings['simpleMethod'];
        $this->assertEquals(30, $simpleMethod->getTimeoutMillis());
        $simpleMethodRetry = $simpleMethod->getRetrySettings();
        $this->assertNull($simpleMethodRetry);
        $pageStreamingMethod = $defaultCallSettings['pageStreamingMethod'];
        $pageStreamingMethodRetry = $pageStreamingMethod->getRetrySettings();
        $this->assertEquals(['code_val_c'], $pageStreamingMethodRetry->getRetryableCodes());
    }

    public function testMergeEmpty()
    {
        $backoffSettings = new BackoffSettings([
            'initialRetryDelayMillis' => 100,
            'retryDelayMultiplier' => 1.3,
            'maxRetryDelayMillis' => 400,
            'initialRpcTimeoutMillis' => 150,
            'rpcTimeoutMultiplier' => 2,
            'maxRpcTimeoutMillis' => 500,
            'totalTimeoutMillis' => 2000
        ]);

        $retrySettings = new RetrySettings(['a', 'b'], $backoffSettings);
        $settings = new CallSettings(['timeoutMillis' => 10, 'retrySettings' => $retrySettings]);
        $emptySettings = new CallSettings([]);
        $mergedSettings = $settings->merge($emptySettings);
        $this->assertEquals(10, $mergedSettings->getTimeoutMillis());
        $this->assertEquals(['a', 'b'], $mergedSettings->getRetrySettings()->getRetryableCodes());
    }

    public function testMerge()
    {
        $backoffSettings = new BackoffSettings([
            'initialRetryDelayMillis' => 100,
            'retryDelayMultiplier' => 1.3,
            'maxRetryDelayMillis' => 400,
            'initialRpcTimeoutMillis' => 150,
            'rpcTimeoutMultiplier' => 2,
            'maxRpcTimeoutMillis' => 500,
            'totalTimeoutMillis' => 2000
        ]);

        $retrySettings = new RetrySettings(['a', 'b'], $backoffSettings);
        $settings = new CallSettings(['timeoutMillis' => 10, 'retrySettings' => $retrySettings]);
        $otherRetrySettings = new RetrySettings(['c'], $backoffSettings);
        $otherSettings = new CallSettings(['timeoutMillis' => 20, 'retrySettings' => $otherRetrySettings]);
        $mergedSettings = $settings->merge($otherSettings);
        $this->assertEquals(20, $mergedSettings->getTimeoutMillis());
        $this->assertEquals(['c'], $mergedSettings->getRetrySettings()->getRetryableCodes());
    }
}
