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
namespace Google\GAX\Testing;

use google\rpc\Status;
use UnderflowException;

/**
 * The MockStubTrait is used by generated mock stub classes which extent \Grpc\BaseStub
 * (https://github.com/grpc/grpc/blob/master/src/php/lib/Grpc/BaseStub.php)
 * It provides functionality to add responses, get received calls, and overrides the _simpleRequest
 * method so that the elements of $responses are returned instead of making a call to the API.
 */
trait MockStubTrait
{
    private $receivedFuncCalls = [];
    private $responses = [];
    private $serverStreamingStatus = null;
    private $callObjects = [];

    /**
     * Overrides the _simpleRequest method in \Grpc\BaseStub
     * (https://github.com/grpc/grpc/blob/master/src/php/lib/Grpc/BaseStub.php)
     * Returns a MockUnaryCall object that will return the first item from $responses
     * @param string $method The API method name to be called
     * @param mixed $argument The request object to the API method
     * @param callable $deserialize A function to deserialize the response object
     * @param array $metadata
     * @param array $options
     * @return MockUnaryCall
     */
    public function _simpleRequest(
        $method,
        $argument,
        $deserialize,
        $metadata = [],
        $options = []
    ) {
        if (is_a($argument, 'DrSlump\Protobuf\Message')) {
            $argument = $argument::deserialize($argument->serialize());
        }
        $this->receivedFuncCalls[] = new ReceivedRequest($method, $argument, $deserialize, $metadata, $options);
        if (count($this->responses) < 1) {
            throw new UnderflowException("ran out of responses");
        }
        list($response, $status) = array_shift($this->responses);
        $call = new MockUnaryCall($response, $deserialize, $status);
        $this->callObjects[] = $call;
        return $call;
    }

    /**
     * Overrides the _clientStreamRequest method in \Grpc\BaseStub
     * (https://github.com/grpc/grpc/blob/master/src/php/lib/Grpc/BaseStub.php)
     * Returns a MockClientStreamingCall object that will return the first item from $responses
     *
     * @param string   $method      The name of the method to call
     * @param callable $deserialize A function that deserializes the responses
     * @param array    $metadata    A metadata map to send to the server
     *                              (optional)
     * @param array    $options     An array of options (optional)
     *
     * @return MockClientStreamingCall The active call object
     */
    public function _clientStreamRequest(
        $method,
        $deserialize,
        array $metadata = [],
        array $options = []
    ) {

        $this->receivedFuncCalls[] = new ReceivedRequest($method, null, $deserialize, $metadata, $options);
        if (count($this->responses) < 1) {
            throw new UnderflowException("ran out of responses");
        }
        list($response, $status) = array_shift($this->responses);
        $call = new MockClientStreamingCall($response, $deserialize, $status);
        $this->callObjects[] = $call;
        return $call;
    }

    /**
     * Overrides the _serverStreamRequest method in \Grpc\BaseStub
     * (https://github.com/grpc/grpc/blob/master/src/php/lib/Grpc/BaseStub.php)
     * Returns a MockServerStreamingCall object that will stream items from $responses, and return
     * a final status of $serverStreamingStatus.
     *
     * @param string   $method      The name of the method to call
     * @param mixed    $argument    The argument to the method
     * @param callable $deserialize A function that deserializes the responses
     * @param array    $metadata    A metadata map to send to the server
     *                              (optional)
     * @param array    $options     An array of options (optional)
     *
     * @return MockServerStreamingCall The active call object
     */
    public function _serverStreamRequest(
        $method,
        $argument,
        $deserialize,
        array $metadata = [],
        array $options = []
    ) {

        if (is_a($argument, 'DrSlump\Protobuf\Message')) {
            $argument = $argument::deserialize($argument->serialize());
        }
        $this->receivedFuncCalls[] = new ReceivedRequest($method, $argument, $deserialize, $metadata, $options);
        $responses = MockStubTrait::stripStatusFromResponses($this->responses);
        $this->responses = [];
        $call = new MockServerStreamingCall($responses, $deserialize, $this->serverStreamingStatus);
        $this->callObjects[] = $call;
        return $call;
    }

    /**
     * Overrides the _bidiRequest method in \Grpc\BaseStub
     * (https://github.com/grpc/grpc/blob/master/src/php/lib/Grpc/BaseStub.php)
     * Returns a MockBidiStreamingCall object that will stream items from $responses, and return
     * a final status of $serverStreamingStatus.
     *
     * @param string   $method      The name of the method to call
     * @param callable $deserialize A function that deserializes the responses
     * @param array    $metadata    A metadata map to send to the server
     *                              (optional)
     * @param array    $options     An array of options (optional)
     *
     * @return MockBidiStreamingCall The active call object
     */
    public function _bidiRequest(
        $method,
        $deserialize,
        array $metadata = [],
        array $options = []
    ) {

        $this->receivedFuncCalls[] = new ReceivedRequest($method, null, $deserialize, $metadata, $options);
        $responses = MockStubTrait::stripStatusFromResponses($this->responses);
        $this->responses = [];
        $call = new MockBidiStreamingCall($responses, $deserialize, $this->serverStreamingStatus);
        $this->callObjects[] = $call;
        return $call;
    }

    public static function stripStatusFromResponses($responses)
    {
        $strippedResponses = [];
        foreach ($responses as $response) {
            list($resp, $status) = $response;
            $strippedResponses[] = $resp;
        }
        return $strippedResponses;
    }

    /**
     * Add a response object, and an optional status, to the list of responses to be returned via
     * _simpleRequest.
     * @param mixed $response
     * @param Status $status
     */
    public function addResponse($response, $status = null)
    {
        if (is_a($response, 'DrSlump\Protobuf\Message')) {
            $response = $response->serialize();
        }
        $this->responses[] = [$response, $status];
    }

    /**
     * Set the status object to be used when creating streaming calls.
     *
     * @param Status $status
     */
    public function setStreamingStatus($status)
    {
        $this->serverStreamingStatus = $status;
    }

    /**
     * Return a list of calls made to _simpleRequest, and clear $receivedFuncCalls.
     *
     * @return ReceivedRequest[] An array of received requests
     */
    public function popReceivedCalls()
    {
        $receivedFuncCallsTemp = $this->receivedFuncCalls;
        $this->receivedFuncCalls = [];
        return $receivedFuncCallsTemp;
    }

    /**
     * @return int The number of calls received.
     */
    public function getReceivedCallCount()
    {
        return count($this->receivedFuncCalls);
    }

    /**
     * @return mixed[] The call objects created by calls to the stub
     */
    public function popCallObjects()
    {
        $callObjectsTemp = $this->callObjects;
        $this->callObjects = [];
        return $callObjectsTemp;
    }

    /**
     * @return bool True if $receivedFuncCalls and $response are empty.
     */
    public function isExhausted()
    {
        return count($this->receivedFuncCalls) === 0
            && count($this->responses) === 0;
    }
}
