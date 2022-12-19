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
use Google\Protobuf\Internal\Message;
use Google\Rpc\Code;
use Grpc;
use stdClass;

/**
 * The MockBidiStreamingCall class is used to mock out the \Grpc\BidiStreamingCall class
 * (https://github.com/grpc/grpc/blob/master/src/php/lib/Grpc/BidiStreamingCall.php)
 *
 * @internal
 */
class MockBidiStreamingCall extends Grpc\BidiStreamingCall
{
    use SerializationTrait;

    private $responses;
    private $status;
    private $writesDone = false;
    private $receivedWrites = [];

    /**
     * MockBidiStreamingCall constructor.
     * @param mixed[] $responses A list of response objects.
     * @param mixed|null $deserialize An optional deserialize method for the response object.
     * @param stdClass|null $status An optional status object. If set to null, a status of OK is used.
     */
    public function __construct(array $responses, $deserialize = null, stdClass $status = null)
    {
        $this->responses = $responses;
        $this->deserialize = $deserialize;
        if (is_null($status)) {
            $status = new MockStatus(Code::OK);
        }
        $this->status = $status;
    }

    /**
     * @return mixed|null
     * @throws ApiException
     */
    public function read()
    {
        if (count($this->responses) > 0) {
            $resp = array_shift($this->responses);
            if (is_null($resp)) {
                // Null was added to the responses list to simulate a failed stream
                // To ensure that getStatus can now be called, we clear the remaining
                // responses and set writesDone to true
                $this->responses = [];
                $this->writesDone();
                return null;
            }
            $obj = $this->deserializeMessage($resp, $this->deserialize);
            return $obj;
        } elseif ($this->writesDone) {
            return null;
        } else {
            throw new ApiException(
                "No more responses to read, but closeWrite() not called - "
                . "this would be blocking",
                Grpc\STATUS_INTERNAL,
                null
            );
        }
    }

    /**
     * @return stdClass|null
     * @throws ApiException
     */
    public function getStatus()
    {
        if (count($this->responses) > 0) {
            throw new ApiException(
                "Calls to getStatus() will block if all responses are not read",
                Grpc\STATUS_INTERNAL,
                null
            );
        }
        if (!$this->writesDone) {
            throw new ApiException(
                "Calls to getStatus() will block if closeWrite() not called",
                Grpc\STATUS_INTERNAL,
                null
            );
        }
        return $this->status;
    }

    /**
     * Save the request object, to be retrieved via getReceivedCalls()
     * @param Message|mixed $request The request object
     * @param array $options An array of options.
     * @throws ApiException
     */
    public function write($request, array $options = [])
    {
        if ($this->writesDone) {
            throw new ApiException(
                "Cannot call write() after writesDone()",
                Grpc\STATUS_INTERNAL,
                null
            );
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
     * Set writesDone to true
     */
    public function writesDone()
    {
        $this->writesDone = true;
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
