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
namespace Google\ApiCore\Transport;

use Exception;
use Google\ApiCore\Call;
use Google\ApiCore\ValidationException;
use Google\Auth\HttpHandler\HttpHandlerFactory;

/**
 * A trait for shared functionality between transports that support only unary RPCs using simple
 * HTTP requests.
 *
 * @internal
 */
trait HttpUnaryTransportTrait
{
    private $httpHandler;
    private $transportName;
    private $clientCertSource;

    /**
     * {@inheritdoc}
     * @return never
     * @throws \BadMethodCallException
     */
    public function startClientStreamingCall(Call $call, array $options)
    {
        $this->throwUnsupportedException();
    }

    /**
     * {@inheritdoc}
     * @return never
     * @throws \BadMethodCallException
     */
    public function startServerStreamingCall(Call $call, array $options)
    {
        $this->throwUnsupportedException();
    }

    /**
     * {@inheritdoc}
     * @return never
     * @throws \BadMethodCallException
     */
    public function startBidiStreamingCall(Call $call, array $options)
    {
        $this->throwUnsupportedException();
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        // Nothing to do.
    }

    /**
     * @param array $options
     * @return array
     */
    private static function buildCommonHeaders(array $options)
    {
        $headers = isset($options['headers'])
            ? $options['headers']
            : [];

        if (!is_array($headers)) {
            throw new \InvalidArgumentException(
                'The "headers" option must be an array'
            );
        }

        // If not already set, add an auth header to the request
        if (!isset($headers['Authorization']) && isset($options['credentialsWrapper'])) {
            $credentialsWrapper = $options['credentialsWrapper'];
            $audience = isset($options['audience'])
                ? $options['audience']
                : null;
            $callback = $credentialsWrapper
                ->getAuthorizationHeaderCallback($audience);
            // Prevent unexpected behavior, as the authorization header callback
            // uses lowercase "authorization"
            unset($headers['authorization']);
            $authHeaders = $callback();
            if (!is_array($authHeaders)) {
                throw new \UnexpectedValueException(
                    'Expected array response from authorization header callback'
                );
            }
            $headers += $authHeaders;
        }

        return $headers;
    }

    /**
     * @return callable
     * @throws ValidationException
     */
    private static function buildHttpHandlerAsync()
    {
        try {
            return [HttpHandlerFactory::build(), 'async'];
        } catch (Exception $ex) {
            throw new ValidationException("Failed to build HttpHandler", $ex->getCode(), $ex);
        }
    }

    /**
     * Set the path to a client certificate.
     *
     * @param callable $clientCertSource
     */
    private function configureMtlsChannel(callable $clientCertSource)
    {
        $this->clientCertSource = $clientCertSource;
    }

    /**
     * @return never
     * @throws \BadMethodCallException
     */
    private function throwUnsupportedException()
    {
        throw new \BadMethodCallException(
            "Streaming calls are not supported while using the {$this->transportName} transport."
        );
    }

    private static function loadClientCertSource(callable $clientCertSource)
    {
        $certFile = tempnam(sys_get_temp_dir(), 'cert');
        $keyFile = tempnam(sys_get_temp_dir(), 'key');
        list($cert, $key) = call_user_func($clientCertSource);
        file_put_contents($certFile, $cert);
        file_put_contents($keyFile, $key);

        // the key and the cert are returned in one temporary file
        return [$certFile, $keyFile];
    }
}
