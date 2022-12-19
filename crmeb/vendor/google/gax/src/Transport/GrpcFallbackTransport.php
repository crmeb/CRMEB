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

use Google\ApiCore\ApiException;
use Google\ApiCore\ApiStatus;
use Google\ApiCore\Call;
use Google\ApiCore\ServiceAddressTrait;
use Google\ApiCore\ValidationException;
use Google\ApiCore\ValidationTrait;
use Google\Protobuf\Internal\Message;
use Google\Rpc\Status;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * A transport that sends protobuf over HTTP 1.1 that can be used when full gRPC support
 * is not available.
 */
class GrpcFallbackTransport implements TransportInterface
{
    use ValidationTrait;
    use ServiceAddressTrait;
    use HttpUnaryTransportTrait;

    private $baseUri;

    /**
     * @param string $baseUri
     * @param callable $httpHandler A handler used to deliver PSR-7 requests.
     */
    public function __construct(
        string $baseUri,
        callable $httpHandler
    ) {
        $this->baseUri = $baseUri;
        $this->httpHandler = $httpHandler;
        $this->transportName = 'grpc-fallback';
    }

    /**
     * Builds a GrpcFallbackTransport.
     *
     * @param string $apiEndpoint
     *        The address of the API remote host, for example "example.googleapis.com".
     * @param array $config {
     *    Config options used to construct the grpc-fallback transport.
     *
     *    @type callable $httpHandler A handler used to deliver PSR-7 requests.
     * }
     * @return GrpcFallbackTransport
     * @throws ValidationException
     */
    public static function build(string $apiEndpoint, array $config = [])
    {
        $config += [
            'httpHandler'  => null,
            'clientCertSource' => null,
        ];
        list($baseUri, $port) = self::normalizeServiceAddress($apiEndpoint);
        $httpHandler = $config['httpHandler'] ?: self::buildHttpHandlerAsync();
        $transport = new GrpcFallbackTransport("$baseUri:$port", $httpHandler);
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
        $httpHandler = $this->httpHandler;
        return $httpHandler(
            $this->buildRequest($call, $options),
            $this->getCallOptions($options)
        )->then(
            function (ResponseInterface $response) use ($options) {
                if (isset($options['metadataCallback'])) {
                    $metadataCallback = $options['metadataCallback'];
                    $metadataCallback($response->getHeaders());
                }
                return $response;
            }
        )->then(
            function (ResponseInterface $response) use ($call) {
                return $this->unpackResponse($call, $response);
            },
            function (\Exception $ex) {
                throw $this->transformException($ex);
            }
        );
    }

    /**
     * @param Call $call
     * @param array $options
     * @return RequestInterface
     */
    private function buildRequest(Call $call, array $options)
    {
        // Build common headers and set the content type to 'application/x-protobuf'
        $headers = ['Content-Type' => 'application/x-protobuf'] + self::buildCommonHeaders($options);

        // It is necessary to supply 'grpc-web' in the 'x-goog-api-client' header
        // when using the grpc-fallback protocol.
        $headers += ['x-goog-api-client' => []];
        $headers['x-goog-api-client'][] = 'grpc-web';

        // Uri format: https://<service>/$rpc/<method>
        $uri = "https://{$this->baseUri}/\$rpc/{$call->getMethod()}";

        return new Request(
            'POST',
            $uri,
            $headers,
            $call->getMessage()->serializeToString()
        );
    }

    /**
     * @param Call $call
     * @param ResponseInterface $response
     * @return Message
     */
    private function unpackResponse(Call $call, ResponseInterface $response)
    {
        $decodeType = $call->getDecodeType();
        /** @var Message $responseMessage */
        $responseMessage = new $decodeType;
        $responseMessage->mergeFromString((string)$response->getBody());
        return $responseMessage;
    }

    /**
     * @param array $options
     * @return array
     */
    private function getCallOptions(array $options)
    {
        $callOptions = isset($options['transportOptions']['grpcFallbackOptions'])
            ? $options['transportOptions']['grpcFallbackOptions']
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

    /**
     * @param \Exception $ex
     * @return \Exception
     */
    private function transformException(\Exception $ex)
    {
        if ($ex instanceof RequestException && $ex->hasResponse()) {
            $res = $ex->getResponse();
            $body = (string) $res->getBody();
            $status = new Status();
            try {
                $status->mergeFromString($body);
                return ApiException::createFromRpcStatus($status);
            } catch (\Exception $parseException) {
                // We were unable to parse the response body into a $status object. Instead,
                // create an ApiException using the unparsed $body as message.
                $code = ApiStatus::rpcCodeFromHttpStatusCode($res->getStatusCode());
                return ApiException::createFromApiResponse($body, $code, null, $parseException);
            }
        } else {
            return $ex;
        }
    }
}
