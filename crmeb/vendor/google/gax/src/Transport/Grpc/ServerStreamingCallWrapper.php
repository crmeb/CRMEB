<?php
/*
 * Copyright 2021 Google LLC
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

namespace Google\ApiCore\Transport\Grpc;

use Google\ApiCore\ServerStreamingCallInterface;
use Grpc\Gcp\GCPServerStreamCall;
use Grpc\ServerStreamingCall;

/**
 * Class ServerStreamingCallWrapper implements \Google\ApiCore\ServerStreamingCallInterface.
 * This is essentially a wrapper class around the \Grpc\ServerStreamingCall.
 */
class ServerStreamingCallWrapper implements ServerStreamingCallInterface
{
    /**
     * @var ServerStreamingCall|GCPServerStreamCall
     */
    private $stream;

    /**
     * @param ServerStreamingCall|GCPServerStreamCall $stream
     */
    public function __construct($stream)
    {
        $this->stream = $stream;
    }

    /**
     * {@inheritdoc}
     */
    public function start($data, array $metadata = [], array $callOptions = [])
    {
        $this->stream->start($data, $metadata, $callOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function responses()
    {
        foreach ($this->stream->responses() as $response) {
            yield $response;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->stream->getStatus();
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        return $this->stream->getMetadata();
    }

    /**
     * {@inheritdoc}
     */
    public function getTrailingMetadata()
    {
        return $this->stream->getTrailingMetadata();
    }

    /**
     * {@inheritdoc}
     */
    public function getPeer()
    {
        return $this->stream->getPeer();
    }

    /**
     * {@inheritdoc}
     */
    public function cancel()
    {
        $this->stream->cancel();
    }

    /**
     * {@inheritdoc}
     */
    public function setCallCredentials($call_credentials)
    {
        $this->stream->setCallCredentials($call_credentials);
    }
}
