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

namespace Google\ApiCore\Transport\Rest;

use Google\ApiCore\ApiException;
use Google\ApiCore\ApiStatus;
use Google\ApiCore\ServerStreamingCallInterface;
use Google\Rpc\Code;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use stdClass;

/**
 * Class RestServerStreamingCall implements \Google\ApiCore\ServerStreamingCallInterface.
 *
 * @experimental
 */
class RestServerStreamingCall implements ServerStreamingCallInterface
{
    /**
     * @var callable
     */
    private $httpHandler;

    /**
     * @var RequestInterface
     */
    private $originalRequest;

    /**
     * @var ?JsonStreamDecoder
     */
    private $decoder;

    /**
     * @var string
     */
    private $decodeType;

    /**
     * @var array<mixed>
     */
    private $decoderOptions;

    /**
     * @var ?ResponseInterface
     */
    private $response;

    /**
     * @var stdClass
     */
    private $status;

    /**
     * @param callable $httpHandler
     * @param string $decodeType
     * @param array<mixed> $decoderOptions
     */
    public function __construct(callable $httpHandler, string $decodeType, array $decoderOptions)
    {
        $this->httpHandler = $httpHandler;
        $this->decodeType = $decodeType;
        $this->decoderOptions = $decoderOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function start($request, array $headers = [], array $callOptions = [])
    {
        $this->originalRequest = $this->appendHeaders($request, $headers);

        try {
            $handler = $this->httpHandler;
            $response = $handler(
                $this->originalRequest,
                $callOptions
            )->wait();
        } catch (\Exception $ex) {
            if ($ex instanceof RequestException && $ex->hasResponse()) {
                $ex = ApiException::createFromRequestException($ex, /* isStream */ true);
            }
            throw $ex;
        }

        // Create an OK Status for a successful request just so that it
        // has a return value.
        $this->status = new stdClass();
        $this->status->code = Code::OK;
        $this->status->message = ApiStatus::OK;
        $this->status->details = [];

        $this->response = $response;
    }

    /**
     * @param RequestInterface $request
     * @param array<mixed> $headers
     * @return RequestInterface
     */
    private function appendHeaders(RequestInterface $request, array $headers)
    {
        foreach ($headers as $key => $value) {
            $request = $request->hasHeader($key) ?
                        $request->withAddedHeader($key, $value) :
                        $request->withHeader($key, $value);
        }

        return $request;
    }

    /**
     * {@inheritdoc}
     */
    public function responses()
    {
        if (is_null($this->response)) {
            throw new \Exception('Stream has not been started.');
        }

        // Decode the stream and yield responses as they are read.
        $this->decoder = new JsonStreamDecoder(
            $this->response->getBody(),
            $this->decodeType,
            $this->decoderOptions
        );

        foreach ($this->decoder->decode() as $message) {
            yield $message;
        }
    }

    /**
     * Return the status of the server stream. If the call has not been started
     * this will be null.
     *
     * @return stdClass The status, with integer $code, string
     *                   $details, and array $metadata members
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        return is_null($this->response) ? null : $this->response->getHeaders();
    }

    /**
     * The Rest transport does not support trailing metadata. This is a
     * passthrough to getMetadata().
     */
    public function getTrailingMetadata()
    {
        return $this->getMetadata();
    }

    /**
     * {@inheritdoc}
     */
    public function getPeer()
    {
        return $this->originalRequest->getUri();
    }

    /**
     * {@inheritdoc}
     */
    public function cancel()
    {
        if (!is_null($this->decoder)) {
            $this->decoder->close();
        }
    }

    /**
     * For the REST transport this is a no-op.
     * {@inheritdoc}
     */
    public function setCallCredentials($call_credentials)
    {
        // Do nothing.
    }
}
