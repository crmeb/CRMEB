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

use Grpc;
use Exception;
use InvalidArgumentException;

/**
 * Creates a function wrapper that provides extra functionalities such as retry and bundling.
 */
class ApiCallable
{
    const GRPC_CALLABLE_PARAM_COUNT = 3;
    const GRPC_CALLABLE_METADATA_INDEX = 1;
    const GRPC_CALLABLE_OPTION_INDEX = 2;
    const GRPC_RESPONSE_STATUS_INDEX = 1;

    private static function setTimeout($apiCall, $timeoutMillis)
    {
        $inner = function () use ($apiCall, $timeoutMillis) {
            $params = func_get_args();
            if (count($params) != self::GRPC_CALLABLE_PARAM_COUNT ||
                !is_array($params[self::GRPC_CALLABLE_OPTION_INDEX])) {
                throw new InvalidArgumentException('Options argument is not found.');
            } else {
                $timeoutMicros = $timeoutMillis * 1000;
                $params[self::GRPC_CALLABLE_OPTION_INDEX]['timeout'] = $timeoutMicros;
            }
            return call_user_func_array($apiCall, $params);
        };
        return $inner;
    }

    private static function setRetry($apiCall, RetrySettings $retrySettings, $timeFuncMillis)
    {
        if (!isset($timeFuncMillis)) {
            $timeFuncMillis = function () {
                return microtime(true) / 1000.0;
            };
        }

        $inner = function () use ($apiCall, $retrySettings, $timeFuncMillis) {
            $backoffSettings = $retrySettings->getBackoffSettings();

            // Initialize retry parameters
            $delayMult = $backoffSettings->getRetryDelayMultiplier();
            $maxDelayMillis = $backoffSettings->getMaxRetryDelayMillis();
            $timeoutMult = $backoffSettings->getRpcTimeoutMultiplier();
            $maxTimeoutMillis = $backoffSettings->getMaxRpcTimeoutMillis();
            $totalTimeoutMillis = $backoffSettings->getTotalTimeoutMillis();

            $delayMillis = $backoffSettings->getInitialRetryDelayMillis();
            $timeoutMillis = $backoffSettings->getInitialRpcTimeoutMillis();
            $currentTimeMillis = $timeFuncMillis();
            $deadlineMillis = $currentTimeMillis + $totalTimeoutMillis;

            while ($currentTimeMillis < $deadlineMillis) {
                $nextApiCall = self::setTimeout($apiCall, $timeoutMillis);
                try {
                    return call_user_func_array($nextApiCall, func_get_args());
                } catch (ApiException $e) {
                    if (!in_array($e->getCode(), $retrySettings->getRetryableCodes())) {
                        throw $e;
                    }
                } catch (Exception $e) {
                    throw $e;
                }
                // Don't sleep if the failure was a timeout
                if ($e->getCode() != Grpc\STATUS_DEADLINE_EXCEEDED) {
                    usleep($delayMillis * 1000);
                }
                $currentTimeMillis = $timeFuncMillis();
                $delayMillis = min($delayMillis * $delayMult, $maxDelayMillis);
                $timeoutMillis = min(
                    $timeoutMillis * $timeoutMult,
                    $maxTimeoutMillis,
                    $deadlineMillis - $currentTimeMillis
                );
            }
            throw new ApiException("Retry total timeout exceeded.", Grpc\STATUS_DEADLINE_EXCEEDED);
        };
        return $inner;
    }

    private static function setPageStreaming($callable, $pageStreamingDescriptor)
    {
        $inner = function () use ($callable, $pageStreamingDescriptor) {
            return new PagedListResponse(func_get_args(), $callable, $pageStreamingDescriptor);
        };
        return $inner;
    }

    private static function setLongRunnning($callable, $longRunningDescriptor)
    {
        $inner = function () use ($callable, $longRunningDescriptor) {
            $response = call_user_func_array($callable, func_get_args());
            $name = $response->getName();
            $client = $longRunningDescriptor['operationsClient'];
            $options = $longRunningDescriptor + [
                'lastProtoResponse' => $response,
            ];
            return new OperationResponse($name, $client, $options);
        };
        return $inner;
    }

    public static function callWithoutRequest($callable, $params)
    {
        array_shift($params);
        return call_user_func_array($callable, $params);
    }

    private static function createUnaryApiCall($callable)
    {
        return function () use ($callable) {
            list($response, $status) =
                call_user_func_array($callable, func_get_args())->wait();
            if ($status->code == Grpc\STATUS_OK) {
                return $response;
            } else {
                throw ApiException::createFromStdClass($status);
            }
        };
    }

    private static function createGrpcStreamingApiCall($callable, $grpcStreamingDescriptor)
    {
        switch ($grpcStreamingDescriptor['grpcStreamingType']) {
            case 'ClientStreaming':
                return ClientStream::createApiCall($callable, $grpcStreamingDescriptor);
            case 'ServerStreaming':
                return ServerStream::createApiCall($callable, $grpcStreamingDescriptor);
            case 'BidiStreaming':
                return BidiStream::createApiCall($callable, $grpcStreamingDescriptor);
            default:
                throw new ValidationException('Unexpected gRPC streaming type: ' .
                    $grpcStreamingDescriptor['grpcStreamingType']);
        }
    }

    private static function setCustomHeader($callable, $headerDescriptor, $userHeaders = null)
    {
        $inner = function () use ($callable, $headerDescriptor, $userHeaders) {
            $params = func_get_args();
            if (count($params) != self::GRPC_CALLABLE_PARAM_COUNT ||
                !is_array($params[self::GRPC_CALLABLE_METADATA_INDEX])
            ) {
                throw new InvalidArgumentException('Metadata argument is not found.');
            } else {
                $metadata = $params[self::GRPC_CALLABLE_METADATA_INDEX];
                $headers = [];
                // Check $userHeaders first, and then merge $headerDescriptor headers, to ensure
                // that $headerDescriptor headers such as x-goog-api-client cannot be overwritten
                // by the $userHeaders.
                if (!is_null($userHeaders)) {
                    $headers = $userHeaders;
                }
                if (!is_null($headerDescriptor)) {
                    $headers = array_merge($headers, $headerDescriptor->getHeader());
                }
                $params[self::GRPC_CALLABLE_METADATA_INDEX] = array_merge($headers, $metadata);
                return call_user_func_array($callable, $params);
            }
        };
        return $inner;
    }

    /**
     * @param \Grpc\BaseStub $stub the gRPC stub to make calls through.
     * @param string $methodName the method name on the stub to call.
     * @param \Google\GAX\CallSettings $settings the call settings to use for this call.
     * @param array $options {
     *     Optional.
     *     @type \Google\GAX\PageStreamingDescriptor $pageStreamingDescriptor
     *           the descriptor used for page-streaming.
     *     @type \Google\GAX\AgentHeaderDescriptor $headerDescriptor
     *           the descriptor used for creating GAPIC header.
     * }
     *
     * @return callable
     * @throws ValidationException
     */
    public static function createApiCall($stub, $methodName, CallSettings $settings, $options = [])
    {
        ApiCallable::validateApiCallSettings($settings, $options);

        $callable = array($stub, $methodName);
        if (array_key_exists('grpcStreamingDescriptor', $options)) {
            $apiCall = ApiCallable::createGrpcStreamingApiCall(
                $callable,
                $options['grpcStreamingDescriptor']
            );
        } else {
            $apiCall = ApiCallable::createUnaryApiCall($callable);
        }

        $retrySettings = $settings->getRetrySettings();
        if (!is_null($retrySettings) && !is_null($retrySettings->getRetryableCodes())) {
            $timeFuncMillis = null;
            if (array_key_exists('timeFuncMillis', $options)) {
                $timeFuncMillis = $options['timeFuncMillis'];
            }
            $apiCall = self::setRetry($apiCall, $settings->getRetrySettings(), $timeFuncMillis);
        } elseif ($settings->getTimeoutMillis() > 0) {
            $apiCall = self::setTimeout($apiCall, $settings->getTimeoutMillis());
        }

        if (array_key_exists('pageStreamingDescriptor', $options)) {
            $apiCall = self::setPageStreaming($apiCall, $options['pageStreamingDescriptor']);
        }

        if (array_key_exists('longRunningDescriptor', $options)) {
            $apiCall = self::setLongRunnning($apiCall, $options['longRunningDescriptor']);
        }

        if (array_key_exists('headerDescriptor', $options) || !is_null($settings->getUserHeaders())) {
            $apiCall = self::setCustomHeader($apiCall, $options['headerDescriptor'], $settings->getUserHeaders());
        }
        return $apiCall;
    }

    private static function validateApiCallSettings(CallSettings $settings, $options)
    {
        $retrySettings = $settings->getRetrySettings();
        $isGrpcStreaming = array_key_exists('grpcStreamingDescriptor', $options);
        if ($isGrpcStreaming) {
            if (!is_null($retrySettings) && !is_null($retrySettings->getRetryableCodes())) {
                throw new ValidationException(
                    'grpcStreamingDescriptor not compatible with retry settings'
                );
            }
            if (array_key_exists('pageStreamingDescriptor', $options)) {
                throw new ValidationException(
                    'grpcStreamingDescriptor not compatible with pageStreamingDescriptor'
                );
            }
            if (array_key_exists('longRunningDescriptor', $options)) {
                throw new ValidationException(
                    'grpcStreamingDescriptor not compatible with longRunningDescriptor'
                );
            }
        }
    }
}
