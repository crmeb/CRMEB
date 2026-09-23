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
 * Encapsulates the call settings for an API call.
 */
class CallSettings
{
    const INHERIT_TIMEOUT = -1;

    private $timeoutMillis;
    private $retrySettings;
    private $userHeaders;

    /**
     * Constructs an array mapping method names to CallSettings.
     *
     * @param string $serviceName
     *     The fully-qualified name of this service, used as a key into
     *     the client config file.
     * @param array $clientConfig
     *     An array parsed from the standard API client config file.
     * @param array $retryingOverrides
     *     A dictionary of method names to RetrySettings that
     *     override those specified in $clientConfig.
     * @param array $statusCodes
     *     An array which maps the strings referring to response status
     *     codes to the PHP objects representing those codes.
     * @param int $timeoutMillisDefault
     *     The timeout (in milliseconds) to use for calls that don't
     *     have a retry configured, and don't have timeout_millis set
     *     in $clientConfig.
     *
     * @return CallSettings[] $callSettings
     */
    public static function load(
        $serviceName,
        $clientConfig,
        $retryingOverrides,
        $statusCodes,
        $timeoutMillisDefault
    ) {
    
        $callSettings = [];

        $serviceConfig = $clientConfig['interfaces'][$serviceName];
        foreach ($serviceConfig['methods'] as $methodName => $methodConfig) {
            $phpMethodKey = lcfirst($methodName);
            $retrySettings = null;
            if (self::inheritRetrySettings($retryingOverrides, $phpMethodKey)) {
                $retrySettings =
                        self::constructRetry(
                            $methodConfig,
                            $statusCodes,
                            $serviceConfig['retry_codes'],
                            $serviceConfig['retry_params']
                        );
            } else {
                $retrySettings = $retryingOverrides[$phpMethodKey];
            }

            if (array_key_exists('timeout_millis', $methodConfig)) {
                $timeoutMillis = $methodConfig['timeout_millis'];
            } else {
                $timeoutMillis = $timeoutMillisDefault;
            }

            $callSettings[$phpMethodKey] = new CallSettings(
                ['timeoutMillis' => $timeoutMillis,
                'retrySettings' => $retrySettings]
            );
        }
        return $callSettings;
    }

    private static function inheritRetrySettings($retryingOverrides, $phpMethodKey)
    {
        if (empty($retryingOverrides)) {
            return true;
        }
        if (!array_key_exists($phpMethodKey, $retryingOverrides)) {
            return true;
        }
        $retrySettings = $retryingOverrides[$phpMethodKey];
        if (is_null($retrySettings)) {
            // Retry has been turned off explicitly.
            return false;
        }
        return $retrySettings->shouldInherit();
    }

    private static function constructRetry(
        $methodConfig,
        $statusCodes,
        $retryCodes,
        $retryParams
    ) {
    
        $codes = [];
        if (!empty($retryCodes)) {
            foreach ($retryCodes as $retryCodesName => $retryCodeList) {
                if (isset($methodConfig['retry_codes_name']) &&
                    $retryCodesName === $methodConfig['retry_codes_name'] &&
                    !empty($retryCodeList)) {
                    foreach ($retryCodeList as $retryCodeName) {
                        if (!array_key_exists($retryCodeName, $statusCodes)) {
                            throw new InvalidArgumentException("Invalid status code: $retryCodeName");
                        }
                        array_push($codes, $statusCodes[$retryCodeName]);
                    }
                    break;
                }
            }
        }
        $backoffSettings = null;
        if (!empty($methodConfig['retry_params_name'])) {
            foreach ($retryParams as $retryParamsName => $retryParamValues) {
                if ($retryParamsName === $methodConfig['retry_params_name']) {
                    $backoffSettings = BackoffSettings::fromSnakeCase($retryParamValues);
                }
            }
        }
        if (!empty($codes) && !empty($backoffSettings)) {
            return new RetrySettings($codes, $backoffSettings);
        } else {
            return null;
        }
    }

    /**
     * Construct an instance.
     *
     * @param array $settings {
     *    Optional.
     *    @type \Google\GAX\RetrySettings $retrySettings
     *          Retry settings to use for this method. If present, then
     *          $timeout is ignored.
     *    @type integer $timeoutMillis
     *          Timeout to use for the call. Only used if $retrySettings
     *          is not set.
     *    @type array $userHeaders
     *          An array of headers to be included in the request.
     * }
     */
    public function __construct($settings = [])
    {
        $this->timeoutMillis = self::INHERIT_TIMEOUT;
        $this->retrySettings = RetrySettings::inherit();

        if (array_key_exists('timeoutMillis', $settings)) {
            $this->timeoutMillis = $settings['timeoutMillis'];
        }
        if (array_key_exists('retrySettings', $settings)) {
            $this->retrySettings = $settings['retrySettings'];
        }
        if (array_key_exists('userHeaders', $settings)) {
            $this->userHeaders = $settings['userHeaders'];
        }
    }

    public function getTimeoutMillis()
    {
        return $this->timeoutMillis;
    }

    public function getRetrySettings()
    {
        return $this->retrySettings;
    }

    public function getUserHeaders()
    {
        return $this->userHeaders;
    }

    /**
     * Returns a new CallSettings merged from this and another CallSettings object.
     *
     * @param CallSettings $otherSettings
     *     A CallSettings whose values override those in this object. If
     *     null, then a copy of this object is returned.
     * @return CallSettings
     */
    public function merge(CallSettings $otherSettings = null)
    {
        if (is_null($otherSettings)) {
            return new CallSettings([
                'timeoutMillis' => $this->timeoutMillis,
                'retrySettings' => $this->retrySettings,
                'userHeaders' => $this->userHeaders,
            ]);
        } else {
            $timeoutMillis = $this->timeoutMillis;
            if ($otherSettings->getTimeoutMillis() != self::INHERIT_TIMEOUT) {
                $timeoutMillis = $otherSettings->getTimeoutMillis();
            }
            $retrySettings = $this->retrySettings;
            if (is_null($otherSettings->getRetrySettings())
                || !$otherSettings->getRetrySettings()->shouldInherit()) {
                $retrySettings = $otherSettings->getRetrySettings();
            }
            $userHeaders = $this->userHeaders;
            if (!is_null($otherSettings->userHeaders)) {
                $userHeaders = $otherSettings->userHeaders;
            }
            return new CallSettings([
                'timeoutMillis' => $timeoutMillis,
                'retrySettings' => $retrySettings,
                'userHeaders' => $userHeaders,
            ]);
        }
    }
}
