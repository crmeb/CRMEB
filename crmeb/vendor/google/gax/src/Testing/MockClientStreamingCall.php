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

namespace Google\ApiCore\Testing;

use Google\ApiCore\ApiException;
use Google\ApiCore\ApiStatus;
use Google\Protobuf\Internal\Message;
use Google\Rpc\Code;
use Grpc;
use stdClass;

/**
 * The MockClientStreamingCall class is used to mock out the \Grpc\ClientStreamingCall class
 * (https://github.com/grpc/grpc/blob/master/src/php/lib/Grpc/ClientStreamingCall.php)
 *
 * The MockClientStreamingCall object is constructed with a response object, an optional deserialize
 * method, and an optional status. The response object and status are returned immediately from the
 * wait() method. It also provides a write() method that accepts request objects, and a
 * getAllRequests() method that returns all request objects passed to write(), and clears them.
 *
 * @internal
 */
class MockClientStreamingCall extends Grpc\ClientStreamingCall
{
    private $mockUnaryCall;
    private $waitCalled = false;
    private $receivedWrites = [];

    /**
     * MockClientStreamingCall constructor.
     * @param Message|string $response The response object.
     * @param callable|array|null $deserialize An optional deserialize method for the response object.
     * @param stdClass|null $status An optional status object. If set to null, a status of OK is used.
     */
    public function __construct($response, $deserialize = null, stdClass $status = null)
    {
        $this->mockUnaryCall = new MockUnaryCall($response, $deserialize, $status);
    }

    /**
     * Immediately return the preset response object and status.
     * @return array The response object and status.
     */
    public function wait()
    {
        $this->waitCalled = true;
        return $this->mockUnaryCall->wait();
    }

    /**
     * Save the request object, to be retrieved via getReceivedCalls()
     * @param Message|mixed $request The request object
     * @param array $options An array of options
     * @throws ApiException
     */
    public function write($request, array $options = [])
    {
        if ($this->waitCalled) {
            throw new ApiException("Cannot call write() after wait()",  Code::INTERNAL, ApiStatus::INTERNAL);
        }
        if (is_a($request, '\Google\Protobuf\Internal\Message')) {
            /** @var Message $newRequest */
            $newRequest = new $request();
            $newRequest->mergeFromString($request->serializeToString());
            $request = $newRequest;
        }
        $this->receivedWrites[] = $request;
    }

    /**
     * Return a list of calls made to write(), and clear $receivedFuncCalls.
     *
     * @return mixed[] An array of received requests
     */
    public function popReceivedCalls()
    {
        $receivedFuncCallsTemp = $this->receivedWrites;
        $this->receivedWrites = [];
        return $receivedFuncCallsTemp;
    }
}
