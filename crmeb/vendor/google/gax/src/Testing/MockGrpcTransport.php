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

namespace Google\ApiCore\Testing;

use Google\ApiCore\Transport\GrpcTransport;
use Grpc\ChannelCredentials;

/**
 * @internal
 */
class MockGrpcTransport extends GrpcTransport
{
    private $requestArguments;
    private $mockCall;

    /**
     * @param mixed $mockCall
     */
    public function __construct($mockCall = null)
    {
        $this->mockCall = $mockCall;
        $opts = ['credentials' => ChannelCredentials::createSsl()];
        parent::__construct('', $opts);
    }

    /**
     * @param string $method
     * @param array $arguments
     * @param callable $deserialize
     */
    protected function _simpleRequest(
        $method,
        $arguments,
        $deserialize,
        array $metadata = [],
        array $options = []
    ) {
        $this->logCall($method, $deserialize, $metadata, $options, $arguments);
        return $this->mockCall;
    }

    /**
     * @param string $method
     * @param callable $deserialize
     */
    protected function _clientStreamRequest(
        $method,
        $deserialize,
        array $metadata = [],
        array $options = []
    ) {
        $this->logCall($method, $deserialize, $metadata, $options);
        return $this->mockCall;
    }

    /**
     * @param string $method
     * @param array $arguments
     * @param callable $deserialize
     */
    protected function _serverStreamRequest(
        $method,
        $arguments,
        $deserialize,
        array $metadata = [],
        array $options = []
    ) {
        $this->logCall($method, $deserialize, $metadata, $options, $arguments);
        return $this->mockCall;
    }

    /**
     * @param string $method
     * @param callable $deserialize
     */
    protected function _bidiRequest(
        $method,
        $deserialize,
        array $metadata = [],
        array $options = []
    ) {
        $this->logCall($method, $deserialize, $metadata, $options);
        return $this->mockCall;
    }

    /**
     * @param string $method
     * @param callable $deserialize
     * @param array $arguments
     */
    private function logCall(
        $method,
        $deserialize,
        array $metadata = [],
        array $options = [],
        $arguments = null
    ) {
        $this->requestArguments = [
            'method' => $method,
            'arguments' => $arguments,
            'deserialize' => $deserialize,
            'metadata' => $metadata,
            'options' => $options,
        ];
    }

    public function getRequestArguments()
    {
        return $this->requestArguments;
    }
}
