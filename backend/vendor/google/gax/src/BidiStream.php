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
use Google\GAX\ApiException;
use Google\GAX\ValidationException;

/**
 * BidiStream is the response object from a gRPC bidirectional streaming API call.
 */
class BidiStream
{
    private $call;
    private $isComplete = false;
    private $writesClosed = false;
    private $resourcesField = null;
    private $pendingResources = [];

    /**
     * BidiStream constructor.
     *
     * @param \Grpc\BidiStreamingCall $bidiStreamingCall The gRPC bidirectional streaming call object
     * @param array $grpcStreamingDescriptor
     */
    public function __construct($bidiStreamingCall, $grpcStreamingDescriptor = [])
    {
        $this->call = $bidiStreamingCall;
        if (array_key_exists('resourcesField', $grpcStreamingDescriptor)) {
            $this->resourcesField = $grpcStreamingDescriptor['resourcesField'];
        }
    }

    /**
     * @param callable $callable
     * @param mixed[] $grpcStreamingDescriptor
     * @return callable ApiCall
     */
    public static function createApiCall($callable, $grpcStreamingDescriptor)
    {
        return function () use ($callable, $grpcStreamingDescriptor) {
            $response = ApiCallable::callWithoutRequest($callable, func_get_args());
            return new BidiStream($response, $grpcStreamingDescriptor);
        };
    }

    /**
     * Write request to the server.
     *
     * @param mixed $request The request to write
     * @throws ValidationException
     */
    public function write($request)
    {
        if ($this->isComplete) {
            throw new ValidationException("Cannot call write() after streaming call is complete.");
        }
        if ($this->writesClosed) {
            throw new ValidationException("Cannot call write() after calling closeWrite().");
        }
        $this->call->write($request);
    }

    /**
     * Write all requests in $requests.
     *
     * @param mixed[] $requests An Iterable of request objects to write to the server
     *
     * @throws ValidationException
     * @throws ApiException
     */
    public function writeAll($requests = [])
    {
        foreach ($requests as $request) {
            $this->write($request);
        }
    }

    /**
     * Inform the server that no more requests will be written. The write() function cannot be
     * called after closeWrite() is called.
     */
    public function closeWrite()
    {
        if ($this->isComplete) {
            throw new ValidationException(
                "Cannot call closeWrite() after streaming call is complete."
            );
        }
        if (!$this->writesClosed) {
            $this->call->writesDone();
            $this->writesClosed = true;
        }
    }

    /**
     * Read the next response from the server. Returns null if the streaming call completed
     * successfully. Throws an ApiException if the streaming call failed.
     *
     * @return mixed
     * @throws ValidationException
     * @throws ApiException
     */
    public function read()
    {
        if ($this->isComplete) {
            throw new ValidationException("Cannot call read() after streaming call is complete.");
        }
        $resourcesField = $this->resourcesField;
        if (!is_null($resourcesField)) {
            if (count($this->pendingResources) === 0) {
                $response = $this->call->read();
                if (!is_null($response)) {
                    $this->pendingResources = array_reverse($response->$resourcesField());
                }
            }
            $result = array_pop($this->pendingResources);
        } else {
            $result = $this->call->read();
        }
        if (is_null($result)) {
            $status = $this->call->getStatus();
            $this->isComplete = true;
            if (!($status->code == Grpc\STATUS_OK)) {
                throw ApiException::createFromStdClass($status);
            }
        }
        return $result;
    }

    /**
     * Call closeWrite(), and read all responses from the server, until the streaming call is
     * completed. Throws an ApiException if the streaming call failed.
     *
     * @return \Generator|mixed[]
     * @throws ValidationException
     * @throws ApiException
     */
    public function closeWriteAndReadAll()
    {
        $this->closeWrite();
        $response = $this->read();
        while (!is_null($response)) {
            yield $response;
            $response = $this->read();
        }
    }

    /**
     * Return the underlying gRPC call object
     *
     * @return \Grpc\BidiStreamingCall
     */
    public function getBidiStreamingCall()
    {
        return $this->call;
    }
}
