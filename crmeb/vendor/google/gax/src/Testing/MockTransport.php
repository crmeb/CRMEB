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

use Google\ApiCore\ApiException;
use Google\ApiCore\BidiStream;
use Google\ApiCore\Call;
use Google\ApiCore\ClientStream;
use Google\ApiCore\ServerStream;
use Google\ApiCore\Transport\TransportInterface;
use Google\Rpc\Code;
use GuzzleHttp\Promise\Promise;

/**
 * @internal
 */
class MockTransport implements TransportInterface
{
    use MockStubTrait;

    private $agentHeaderDescriptor; // @phpstan-ignore-line

    public function setAgentHeaderDescriptor($agentHeaderDescriptor)
    {
        $this->agentHeaderDescriptor = $agentHeaderDescriptor;
    }

    public function startUnaryCall(Call $call, array $options)
    {
        $call = call_user_func([$this, $call->getMethod()], $call, $options);
        return $promise = new Promise(
            function () use ($call, &$promise) {
                list($response, $status) = $call->wait();

                if ($status->code == Code::OK) {
                    $promise->resolve($response);
                } else {
                    throw ApiException::createFromStdClass($status);
                }
            },
            [$call, 'cancel']
        );
    }

    public function startBidiStreamingCall(Call $call, array $options)
    {
        $newArgs = ['/' . $call->getMethod(), $this->deserialize, $options, $options];
        $response = call_user_func_array(array($this, '_bidiRequest'), $newArgs);
        return new BidiStream($response, $call->getDescriptor());
    }

    public function startClientStreamingCall(Call $call, array $options)
    {
        $newArgs = ['/' . $call->getMethod(), $this->deserialize, $options, $options];
        $response = call_user_func_array(array($this, '_clientStreamRequest'), $newArgs);
        return new ClientStream($response, $call->getDescriptor());
    }

    public function startServerStreamingCall(Call $call, array $options)
    {
        $newArgs = ['/' . $call->getMethod(), $call->getMessage(), $this->deserialize, $options, $options];
        $response = call_user_func_array(array($this, '_serverStreamRequest'), $newArgs);
        return new ServerStream($response, $call->getDescriptor());
    }

    public function __call(string $name, array $arguments)
    {
        $call = $arguments[0];
        $options = $arguments[1];
        $decode = $call->getDecodeType() ? [$call->getDecodeType(), 'decode'] : null;
        return $this->_simpleRequest(
            '/' . $call->getMethod(),
            $call->getMessage(),
            $decode,
            isset($options['headers']) ? $options['headers'] : [],
            $options
        );
    }

    public function close()
    {
        // does nothing
    }
}
