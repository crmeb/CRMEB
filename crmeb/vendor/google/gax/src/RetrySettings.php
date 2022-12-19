<?php
/*
 * Copyright 2016 Google LLC
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
 * The RetrySettings class is used to configure retrying and timeouts for RPCs.
 * This class can be passed as an optional parameter to RPC methods, or as part
 * of an optional array in the constructor of a client object. In addition,
 * many RPCs and API clients accept a PHP array in place of a RetrySettings
 * object. This can be used to change particular retry parameters without
 * needing to construct a complete RetrySettings object.
 *
 * Constructing a RetrySettings object
 * -----------------------------------
 *
 * See the RetrySettings constructor for documentation about parameters that
 * can be passed to RetrySettings.
 *
 * Example of creating a RetrySettings object using the constructor:
 * ```
 * $retrySettings = new RetrySettings([
 *     'initialRetryDelayMillis' => 100,
 *     'retryDelayMultiplier' => 1.3,
 *     'maxRetryDelayMillis' => 60000,
 *     'initialRpcTimeoutMillis' => 20000,
 *     'rpcTimeoutMultiplier' => 1.0,
 *     'maxRpcTimeoutMillis' => 20000,
 *     'totalTimeoutMillis' => 600000,
 *     'retryableCodes' => [ApiStatus::DEADLINE_EXCEEDED, ApiStatus::UNAVAILABLE],
 * ]);
 * ```
 *
 * It is also possible to create a new RetrySettings object from an existing
 * object using the {@see \Google\ApiCore\RetrySettings::with()} method.
 *
 * Example modifying an existing RetrySettings object using `with()`:
 * ```
 * $newRetrySettings = $retrySettings->with([
 *     'totalTimeoutMillis' => 700000,
 * ]);
 * ```
 *
 * Modifying the retry behavior of an RPC method
 * ---------------------------------------------
 *
 * RetrySettings objects can be used to control retries for many RPC methods in
 * [google-cloud-php](https://github.com/googleapis/google-cloud-php).
 * The examples below make use of the
 * [GroupServiceClient](https://googleapis.github.io/google-cloud-php/#/docs/google-cloud/monitoring/v3/groupserviceclient)
 * from the [Monitoring V3 API](https://github.com/googleapis/google-cloud-php/tree/master/src/Monitoring/V3),
 * but they can be applied to other APIs in the
 * [google-cloud-php](https://github.com/googleapis/google-cloud-php) repository.
 *
 * It is possible to specify the retry behavior to be used by an RPC via the
 * `retrySettings` field in the `optionalArgs` parameter. The `retrySettings`
 * field can contain either a RetrySettings object, or a PHP array containing
 * the particular retry parameters to be updated.
 *
 * Example of disabling retries for a single call to the
 * [listGroups](https://googleapis.github.io/google-cloud-php/#/docs/google-cloud/monitoring/v3/groupserviceclient?method=listGroups)
 * method, and setting a custom timeout:
 * ```
 * $result = $client->listGroups($name, [
 *     'retrySettings' => [
 *         'retriesEnabled' => false,
 *         'noRetriesRpcTimeoutMillis' => 5000,
 *     ]
 * ]);
 * ```
 *
 * Example of creating a new RetrySettings object and using it to override
 * the retry settings for a call to the
 * [listGroups](https://googleapis.github.io/google-cloud-php/#/docs/google-cloud/monitoring/v3/groupserviceclient?method=listGroups)
 * method:
 * ```
 * $customRetrySettings = new RetrySettings([
 *     'initialRetryDelayMillis' => 100,
 *     'retryDelayMultiplier' => 1.3,
 *     'maxRetryDelayMillis' => 60000,
 *     'initialRpcTimeoutMillis' => 20000,
 *     'rpcTimeoutMultiplier' => 1.0,
 *     'maxRpcTimeoutMillis' => 20000,
 *     'totalTimeoutMillis' => 600000,
 *     'retryableCodes' => [ApiStatus::DEADLINE_EXCEEDED, ApiStatus::UNAVAILABLE],
 * ]);
 *
 * $result = $client->listGroups($name, [
 *     'retrySettings' => $customRetrySettings
 * ]);
 * ```
 *
 * Modifying the default retry behavior for RPC methods on a Client object
 * -----------------------------------------------------------------------
 *
 * It is also possible to specify the retry behavior for RPC methods when
 * constructing a client object using the 'retrySettingsArray'. The examples
 * below again make use of the
 * [GroupServiceClient](https://googleapis.github.io/google-cloud-php/#/docs/google-cloud/monitoring/v3/groupserviceclient)
 * from the [Monitoring V3 API](https://github.com/googleapis/google-cloud-php/tree/master/src/Monitoring/V3),
 * but they can be applied to other APIs in the
 * [google-cloud-php](https://github.com/googleapis/google-cloud-php) repository.
 *
 * The GroupServiceClient object accepts an optional `retrySettingsArray`
 * parameter, which can be used to specify retry behavior for RPC methods
 * on the client. The `retrySettingsArray` accepts a PHP array in which keys
 * are the names of RPC methods on the client, and values are either a
 * RetrySettings object or a PHP array containing the particular retry
 * parameters to be updated.
 *
 * Example updating the retry settings for four methods of GroupServiceClient:
 * ```
 * use Google\Cloud\Monitoring\V3\GroupServiceClient;
 *
 * $customRetrySettings = new RetrySettings([
 *     'initialRetryDelayMillis' => 100,
 *     'retryDelayMultiplier' => 1.3,
 *     'maxRetryDelayMillis' => 60000,
 *     'initialRpcTimeoutMillis' => 20000,
 *     'rpcTimeoutMultiplier' => 1.0,
 *     'maxRpcTimeoutMillis' => 20000,
 *     'totalTimeoutMillis' => 600000,
 *     'retryableCodes' => [ApiStatus::DEADLINE_EXCEEDED, ApiStatus::UNAVAILABLE],
 * ]);
 *
 * $updatedCustomRetrySettings = $customRetrySettings->with([
 *     'totalTimeoutMillis' => 700000
 * ]);
 *
 * $client = new GroupServiceClient([
 *     'retrySettingsArray' => [
 *         'listGroups' => ['retriesEnabled' => false],
 *         'getGroup' => [
 *             'initialRpcTimeoutMillis' => 10000,
 *             'maxRpcTimeoutMillis' => 30000,
 *             'totalTimeoutMillis' => 60000,
 *         ],
 *         'deleteGroup' => $customRetrySettings,
 *         'updateGroup' => $updatedCustomRetrySettings
 *     ],
 * ]);
 * ```
 *
 * Configure the use of logical timeout
 * ------------------------------------
 *
 * To configure the use of a logical timeout, where a logical timeout is the
 * duration a method is given to complete one or more RPC attempts, with each
 * attempt using only the time remaining in the logical timeout, use
 * {@see \Google\ApiCore\RetrySettings::logicalTimeout()} combined with
 * {@see \Google\ApiCore\RetrySettings::with()}.
 *
 * ```
 * $timeoutSettings = RetrySettings::logicalTimeout(30000);
 *
 * $customRetrySettings = $customRetrySettings->with($timeoutSettings);
 *
 * $result = $client->listGroups($name, [
 *     'retrySettings' => $customRetrySettings
 * ]);
 * ```
 *
 * {@see \Google\ApiCore\RetrySettings::logicalTimeout()} can also be used on a
 * method call independent of a RetrySettings instance.
 *
 * ```
 * $timeoutSettings = RetrySettings::logicalTimeout(30000);
 *
 * $result = $client->listGroups($name, [
 *     'retrySettings' => $timeoutSettings
 * ]);
 * ```
 */
class RetrySettings
{
    use ValidationTrait;

    private $retriesEnabled;

    private $retryableCodes;

    private $initialRetryDelayMillis;
    private $retryDelayMultiplier;
    private $maxRetryDelayMillis;
    private $initialRpcTimeoutMillis;
    private $rpcTimeoutMultiplier;
    private $maxRpcTimeoutMillis;
    private $totalTimeoutMillis;

    private $noRetriesRpcTimeoutMillis;

    /**
     * Constructs an instance.
     *
     * @param array $settings {
     *     Required. Settings for configuring the retry behavior. All parameters are required except
     *     $retriesEnabled and $noRetriesRpcTimeoutMillis, which are optional and have defaults
     *     determined based on the other settings provided.
     *
     *     @type bool    $retriesEnabled Optional. Enables retries. If not specified, the value is
     *                   determined using the $retryableCodes setting. If $retryableCodes is empty,
     *                   then $retriesEnabled is set to false; otherwise, it is set to true.
     *     @type int     $noRetriesRpcTimeoutMillis Optional. The timeout of the rpc call to be used
     *                   if $retriesEnabled is false, in milliseconds. It not specified, the value
     *                   of $initialRpcTimeoutMillis is used.
     *     @type array   $retryableCodes The Status codes that are retryable. Each status should be
     *                   either one of the string constants defined on {@see \Google\ApiCore\ApiStatus}
     *                   or an integer constant defined on {@see \Google\Rpc\Code}.
     *     @type int     $initialRetryDelayMillis The initial delay of retry in milliseconds.
     *     @type int     $retryDelayMultiplier The exponential multiplier of retry delay.
     *     @type int     $maxRetryDelayMillis The max delay of retry in milliseconds.
     *     @type int     $initialRpcTimeoutMillis The initial timeout of rpc call in milliseconds.
     *     @type int     $rpcTimeoutMultiplier The exponential multiplier of rpc timeout.
     *     @type int     $maxRpcTimeoutMillis The max timeout of rpc call in milliseconds.
     *     @type int     $totalTimeoutMillis The max accumulative timeout in total.
     * }
     */
    public function __construct(array $settings)
    {
        $this->validateNotNull($settings, [
            'initialRetryDelayMillis',
            'retryDelayMultiplier',
            'maxRetryDelayMillis',
            'initialRpcTimeoutMillis',
            'rpcTimeoutMultiplier',
            'maxRpcTimeoutMillis',
            'totalTimeoutMillis',
            'retryableCodes'
        ]);
        $this->initialRetryDelayMillis = $settings['initialRetryDelayMillis'];
        $this->retryDelayMultiplier = $settings['retryDelayMultiplier'];
        $this->maxRetryDelayMillis = $settings['maxRetryDelayMillis'];
        $this->initialRpcTimeoutMillis = $settings['initialRpcTimeoutMillis'];
        $this->rpcTimeoutMultiplier = $settings['rpcTimeoutMultiplier'];
        $this->maxRpcTimeoutMillis = $settings['maxRpcTimeoutMillis'];
        $this->totalTimeoutMillis = $settings['totalTimeoutMillis'];
        $this->retryableCodes = $settings['retryableCodes'];
        $this->retriesEnabled = array_key_exists('retriesEnabled', $settings)
            ? $settings['retriesEnabled']
            : (count($this->retryableCodes) > 0);
        $this->noRetriesRpcTimeoutMillis = array_key_exists('noRetriesRpcTimeoutMillis', $settings)
            ? $settings['noRetriesRpcTimeoutMillis']
            : $this->initialRpcTimeoutMillis;
    }

    /**
     * Constructs an array mapping method names to CallSettings.
     *
     * @param string $serviceName
     *     The fully-qualified name of this service, used as a key into
     *     the client config file.
     * @param array $clientConfig
     *     An array parsed from the standard API client config file.
     * @param bool $disableRetries
     *     Disable retries in all loaded RetrySettings objects. Defaults to false.
     * @throws ValidationException
     * @return RetrySettings[] $retrySettings
     */
    public static function load(
        string $serviceName,
        array $clientConfig,
        bool $disableRetries = false
    ) {
        $serviceRetrySettings = [];

        $serviceConfig = $clientConfig['interfaces'][$serviceName];
        $retryCodes = $serviceConfig['retry_codes'];
        $retryParams = $serviceConfig['retry_params'];
        foreach ($serviceConfig['methods'] as $methodName => $methodConfig) {
            $timeoutMillis = $methodConfig['timeout_millis'];

            if (empty($methodConfig['retry_codes_name']) || empty($methodConfig['retry_params_name'])) {
                // Construct a RetrySettings object with retries disabled
                $retrySettings = self::constructDefault()->with([
                    'noRetriesRpcTimeoutMillis' => $timeoutMillis,
                ]);
            } else {
                $retryCodesName = $methodConfig['retry_codes_name'];
                $retryParamsName = $methodConfig['retry_params_name'];

                if (!array_key_exists($retryCodesName, $retryCodes)) {
                    throw new ValidationException("Invalid retry_codes_name setting: '$retryCodesName'");
                }
                if (!array_key_exists($retryParamsName, $retryParams)) {
                    throw new ValidationException("Invalid retry_params_name setting: '$retryParamsName'");
                }

                foreach ($retryCodes[$retryCodesName] as $status) {
                    if (!ApiStatus::isValidStatus($status)) {
                        throw new ValidationException("Invalid status code: '$status'");
                    }
                }

                $retryParameters = self::convertArrayFromSnakeCase($retryParams[$retryParamsName]) + [
                    'retryableCodes' => $retryCodes[$retryCodesName],
                    'noRetriesRpcTimeoutMillis' => $timeoutMillis,
                ];
                if ($disableRetries) {
                    $retryParameters['retriesEnabled'] = false;
                }

                $retrySettings = new RetrySettings($retryParameters);
            }

            $serviceRetrySettings[$methodName] = $retrySettings;
        }

        return $serviceRetrySettings;
    }

    public static function constructDefault()
    {
        return new RetrySettings([
            'retriesEnabled' => false,
            'noRetriesRpcTimeoutMillis' => 30000,
            'initialRetryDelayMillis' => 100,
            'retryDelayMultiplier' => 1.3,
            'maxRetryDelayMillis' => 60000,
            'initialRpcTimeoutMillis' => 20000,
            'rpcTimeoutMultiplier' => 1,
            'maxRpcTimeoutMillis' => 20000,
            'totalTimeoutMillis' => 600000,
            'retryableCodes' => []]);
    }

    /**
     * Creates a new instance of RetrySettings that updates the settings in the existing instance
     * with the settings specified in the $settings parameter.
     *
     * @param array $settings {
     *     Settings for configuring the retry behavior. Supports all of the options supported by
     *     the constructor; see {@see \Google\ApiCore\RetrySettings::__construct()}. All parameters
     *     are optional - all unset parameters will default to the value in the existing instance.
     * }
     * @return RetrySettings
     */
    public function with(array $settings)
    {
        $existingSettings = [
            'initialRetryDelayMillis' => $this->getInitialRetryDelayMillis(),
            'retryDelayMultiplier' => $this->getRetryDelayMultiplier(),
            'maxRetryDelayMillis' => $this->getMaxRetryDelayMillis(),
            'initialRpcTimeoutMillis' => $this->getInitialRpcTimeoutMillis(),
            'rpcTimeoutMultiplier' => $this->getRpcTimeoutMultiplier(),
            'maxRpcTimeoutMillis' => $this->getMaxRpcTimeoutMillis(),
            'totalTimeoutMillis' => $this->getTotalTimeoutMillis(),
            'retryableCodes' => $this->getRetryableCodes(),
            'retriesEnabled' => $this->retriesEnabled(),
            'noRetriesRpcTimeoutMillis' => $this->getNoRetriesRpcTimeoutMillis(),
        ];
        return new RetrySettings($settings + $existingSettings);
    }

    /**
     * Creates an associative array of the {@see \Google\ApiCore\RetrySettings} timeout fields configured
     * with the given timeout specified in the $timeout parameter interpreted as a logical timeout.
     *
     * @param int $timeout The timeout in milliseconds to be used as a logical call timeout.
     * @return array
     */
    public static function logicalTimeout(int $timeout)
    {
        return [
            'initialRpcTimeoutMillis' => $timeout,
            'maxRpcTimeoutMillis' => $timeout,
            'totalTimeoutMillis' => $timeout,
            'noRetriesRpcTimeoutMillis' => $timeout,
            'rpcTimeoutMultiplier' => 1.0
        ];
    }

    /**
     * @return bool Returns true if retries are enabled, otherwise returns false.
     */
    public function retriesEnabled()
    {
        return $this->retriesEnabled;
    }

    /**
     * @return int The timeout of the rpc call to be used if $retriesEnabled is false,
     *             in milliseconds.
     */
    public function getNoRetriesRpcTimeoutMillis()
    {
        return $this->noRetriesRpcTimeoutMillis;
    }

    /**
     * @return int[] Status codes to retry
     */
    public function getRetryableCodes()
    {
        return $this->retryableCodes;
    }

    /**
     * @return int The initial retry delay in milliseconds. If $this->retriesEnabled()
     *             is false, this setting is unused.
     */
    public function getInitialRetryDelayMillis()
    {
        return $this->initialRetryDelayMillis;
    }

    /**
     * @return float The retry delay multiplier. If $this->retriesEnabled()
     *               is false, this setting is unused.
     */
    public function getRetryDelayMultiplier()
    {
        return $this->retryDelayMultiplier;
    }

    /**
     * @return int The maximum retry delay in milliseconds. If $this->retriesEnabled()
     *             is false, this setting is unused.
     */
    public function getMaxRetryDelayMillis()
    {
        return $this->maxRetryDelayMillis;
    }

    /**
     * @return int The initial rpc timeout in milliseconds. If $this->retriesEnabled()
     *             is false, this setting is unused - use noRetriesRpcTimeoutMillis to
     *             set the timeout in that case.
     */
    public function getInitialRpcTimeoutMillis()
    {
        return $this->initialRpcTimeoutMillis;
    }

    /**
     * @return float The rpc timeout multiplier. If $this->retriesEnabled()
     *               is false, this setting is unused.
     */
    public function getRpcTimeoutMultiplier()
    {
        return $this->rpcTimeoutMultiplier;
    }

    /**
     * @return int The maximum rpc timeout in milliseconds. If $this->retriesEnabled()
     *             is false, this setting is unused - use noRetriesRpcTimeoutMillis to
     *             set the timeout in that case.
     */
    public function getMaxRpcTimeoutMillis()
    {
        return $this->maxRpcTimeoutMillis;
    }

    /**
     * @return int The total time in milliseconds to spend on the call, including all
     *             retry attempts and delays between attempts. If $this->retriesEnabled()
     *             is false, this setting is unused - use noRetriesRpcTimeoutMillis to
     *             set the timeout in that case.
     */
    public function getTotalTimeoutMillis()
    {
        return $this->totalTimeoutMillis;
    }

    private static function convertArrayFromSnakeCase(array $settings)
    {
        $camelCaseSettings = [];
        foreach ($settings as $key => $value) {
            $camelCaseKey = str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            $camelCaseSettings[lcfirst($camelCaseKey)] = $value;
        }
        return $camelCaseSettings;
    }
}
