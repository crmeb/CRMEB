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

use BadMethodCallException;
use Google\ApiCore\ApiException;
use Google\ApiCore\Call;
use Google\ApiCore\RequestBuilder;
use Google\ApiCore\ServerStream;
use Google\ApiCore\ServiceAddressTrait;
use Google\ApiCore\Transport\Rest\RestServerStreamingCall;
use Google\ApiCore\ValidationException;
use Google\ApiCore\ValidationTrait;
use Google\Protobuf\Internal\Message;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * A REST based transport implementation.
 */
class RestTransport implements TransportInterface
{
    use ValidationTrait;
    use ServiceAddressTrait;
    use HttpUnaryTransportTrait {
        startServerStreamingCall as protected unsupportedServerStreamingCall;
    }

    /**
     * @var RequestBuilder
     */
    private $requestBuilder;

    /**
     * @param RequestBuilder $requestBuilder A builder responsible for creating
     *        a PSR-7 request from a set of request information.
     * @param callable $httpHandler A handler used to deliver PSR-7 requests.
     */
    public function __construct(
        RequestBuilder $requestBuilder,
        callable $httpHandler
    ) {
        $this->requestBuilder = $requestBuilder;
        $this->httpHandler = $httpHandler;
        $this->transportName = 'REST';
    }

    /**
     * Builds a RestTransport.
     *
     * @param string $apiEndpoint
     *        The address of the API remote host, for example "example.googleapis.com".
     * @param string $restConfigPath
     *        Path to rest config file.
     * @param array<mixed> $config {
     *    Config options used to construct the gRPC transport.
     *
     *    @type callable $httpHandler A handler used to deliver PSR-7 requests.
     *    @type callable $clientCertSource A callable which returns the client cert as a string.
     * }
     * @return RestTransport
     * @throws ValidationException
     */
    public static function build(string $apiEndpoint, string $restConfigPath, array $config = [])
    {
        $config += [
            'httpHandler'  => null,
            'clientCertSource' => null,
        ];
        list($baseUri, $port) = self::normalizeServiceAddress($apiEndpoint);
        $requestBuilder = new RequestBuilder("$baseUri:$port", $restConfigPath);
        $httpHandler = $config['httpHandler'] ?: self::buildHttpHandlerAsync();
        $transport = new RestTransport($requestBuilder, $httpHandler);
        if ($config['clientCertSource']) {
            $transport->configureMtlsChannel($config['clientCertSource']);
        }
        return $transport;
    }

    /**
     * {@inheritdoc}
     */
    public function startUnaryCall(Call $call, array $options)
    {
        $headers = self::buildCommonHeaders($options);

        // call the HTTP handler
        $httpHandler = $this->httpHandler;
        return $httpHandler(
            $this->requestBuilder->build(
                $call->getMethod(),
                $call->getMessage(),
                $headers
            ),
            $this->getCallOptions($options)
        )->then(
            function (ResponseInterface $response) use ($call, $options) {
                $decodeType = $call->getDecodeType();
                /** @var Message $return */
                $return = new $decodeType;
                $return->mergeFromJsonString(
                    (string) $response->getBody(),
                    true
                );

                if (isset($options['metadataCallback'])) {
                    $metadataCallback = $options['metadataCallback'];
                    $metadataCallback($response->getHeaders());
                }

                return $return;
            },
            function (\Exception $ex) {
                if ($ex instanceof RequestException && $ex->hasResponse()) {
                    throw ApiException::createFromRequestException($ex);
                }

                throw $ex;
            }
        );
    }

    /**
     * {@inheritdoc}
     * @throws \BadMethodCallException for forwards compatibility with older GAPIC clients
     */
    public function startServerStreamingCall(Call $call, array $options)
    {
        $message = $call->getMessage();
        if (!$message) {
            throw new \InvalidArgumentException('A message is required for ServerStreaming calls.');
        }

        // Maintain forwards compatibility with older GAPIC clients not configured for REST server streaming
        // @see https://github.com/googleapis/gax-php/issues/370
        if (!$this->requestBuilder->pathExists($call->getMethod())) {
            $this->unsupportedServerStreamingCall($call, $options);
        }

        $headers = self::buildCommonHeaders($options);
        $callOptions = $this->getCallOptions($options);
        $request = $this->requestBuilder->build(
            $call->getMethod(),
            $call->getMessage()
            // Exclude headers here because they will be added in _serverStreamRequest().
        );

        $decoderOptions = [];
        if (isset($options['decoderOptions'])) {
            $decoderOptions = $options['decoderOptions'];
        }

        return new ServerStream(
            $this->_serverStreamRequest(
                $this->httpHandler,
                $request,
                $headers,
                $call->getDecodeType(),
                $callOptions,
                $decoderOptions
            ),
            $call->getDescriptor()
        );
    }

    /**
     * Creates and starts a RestServerStreamingCall.
     *
     * @param callable $httpHandler The HTTP Handler to invoke the request with.
     * @param RequestInterface $request The request to invoke.
     * @param array<mixed> $headers The headers to include in the request.
     * @param string $decodeType The response stream message type to decode.
     * @param array<mixed> $callOptions The call options to use when making the call.
     * @param array<mixed> $decoderOptions The options to use for the JsonStreamDecoder.
     *
     * @return RestServerStreamingCall
     */
    private function _serverStreamRequest(
        $httpHandler,
        $request,
        $headers,
        $decodeType,
        $callOptions,
        $decoderOptions = []
    ) {
        $call = new RestServerStreamingCall(
            $httpHandler,
            $decodeType,
            $decoderOptions
        );
        $call->start($request, $headers, $callOptions);

        return $call;
    }

    /**
     * @param array<mixed> $options
     *
     * @return array<mixed>
     */
    private function getCallOptions(array $options)
    {
        $callOptions = isset($options['transportOptions']['restOptions'])
            ? $options['transportOptions']['restOptions']
            : [];

        if (isset($options['timeoutMillis'])) {
            $callOptions['timeout'] = $options['timeoutMillis'] / 1000;
        }

        if ($this->clientCertSource) {
            list($cert, $key) = self::loadClientCertSource($this->clientCertSource);
            $callOptions['cert'] = $cert;
            $callOptions['key'] = $key;
        }

        return $callOptions;
    }
}
