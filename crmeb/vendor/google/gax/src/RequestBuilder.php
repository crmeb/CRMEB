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

namespace Google\ApiCore;

use Google\ApiCore\ResourceTemplate\AbsoluteResourceTemplate;
use Google\Protobuf\Internal\Message;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Builds a PSR-7 request from a set of request information.
 *
 * @internal
 */
class RequestBuilder
{
    use ArrayTrait;
    use UriTrait;
    use ValidationTrait;

    private $baseUri;
    private $restConfig;

    /**
     * @param string $baseUri
     * @param string $restConfigPath
     * @throws ValidationException
     */
    public function __construct(string $baseUri, string $restConfigPath)
    {
        self::validateFileExists($restConfigPath);
        $this->baseUri = $baseUri;
        $this->restConfig = require($restConfigPath);
    }

    /**
     * @param string $path
     * @return bool
     */
    public function pathExists(string $path)
    {
        list($interface, $method) = explode('/', $path);
        return isset($this->restConfig['interfaces'][$interface][$method]);
    }

    /**
     * @param string $path
     * @param Message $message
     * @param array $headers
     * @return RequestInterface
     * @throws ValidationException
     */
    public function build(string $path, Message $message, array $headers = [])
    {
        list($interface, $method) = explode('/', $path);

        if (!isset($this->restConfig['interfaces'][$interface][$method])) {
            throw new ValidationException(
                "Failed to build request, as the provided path ($path) was not found in the configuration."
            );
        }

        $numericEnums = isset($this->restConfig['numericEnums']) && $this->restConfig['numericEnums'];
        $methodConfig = $this->restConfig['interfaces'][$interface][$method] + [
            'placeholders' => [],
            'body' => null,
            'additionalBindings' => null,
        ];
        $bindings = $this->buildBindings($methodConfig['placeholders'], $message);
        $uriTemplateConfigs = $this->getConfigsForUriTemplates($methodConfig);

        foreach ($uriTemplateConfigs as $config) {
            $pathTemplate = $this->tryRenderPathTemplate($config['uriTemplate'], $bindings);

            if ($pathTemplate) {
                // We found a valid uriTemplate - now build and return the Request

                list($body, $queryParams) = $this->constructBodyAndQueryParameters($message, $config);

                // Request enum fields will be encoded as numbers rather than strings  (in the response).
                if ($numericEnums) {
                    $queryParams['$alt'] = "json;enum-encoding=int";
                }

                $uri = $this->buildUri($pathTemplate, $queryParams);

                return new Request(
                    $config['method'],
                    $uri,
                    ['Content-Type' => 'application/json'] + $headers,
                    $body
                );
            }
        }

        // No valid uriTemplate found - construct an exception
        $uriTemplates = [];
        foreach ($uriTemplateConfigs as $config) {
            $uriTemplates[] = $config['uriTemplate'];
        }

        throw new ValidationException("Could not map bindings for $path to any Uri template.\n" .
            "Bindings: " . print_r($bindings, true) .
            "UriTemplates: " . print_r($uriTemplates, true));
    }

    /**
     * Create a list of all possible configs using the additionalBindings
     *
     * @param array $config
     * @return array[] An array of configs
     */
    private function getConfigsForUriTemplates(array $config)
    {
        $configs = [$config];

        if ($config['additionalBindings']) {
            foreach ($config['additionalBindings'] as $additionalBinding) {
                $configs[] = $additionalBinding + $config;
            }
        }

        return $configs;
    }

    /**
     * @param Message $message
     * @param array $config
     * @return array Tuple [$body, $queryParams]
     */
    private function constructBodyAndQueryParameters(Message $message, array $config)
    {
        $messageDataJson = $message->serializeToJsonString();

        if ($config['body'] === '*') {
            return [$messageDataJson, []];
        }

        $body = null;
        $queryParams = [];
        $messageData = json_decode($messageDataJson, true);
        foreach ($messageData as $name => $value) {
            if (array_key_exists($name, $config['placeholders'])) {
                continue;
            }

            if (Serializer::toSnakeCase($name) === $config['body']) {
                if (($bodyMessage = $message->{"get$name"}()) instanceof Message) {
                    $body = $bodyMessage->serializeToJsonString();
                } else {
                    $body = json_encode($value);
                }
                continue;
            }

            if (is_array($value) && $this->isAssoc($value)) {
                foreach ($value as $key => $value2) {
                    $queryParams[$name . '.' . $key] = $value2;
                }
            } else {
                $queryParams[$name] = $value;
            }
        }

        // Ensures required query params with default values are always sent
        // over the wire.
        if (isset($config['queryParams'])) {
            foreach ($config['queryParams'] as $requiredQueryParam) {
                $requiredQueryParam = Serializer::toCamelCase($requiredQueryParam);
                if (!array_key_exists($requiredQueryParam, $queryParams)) {
                    $getter = Serializer::getGetter($requiredQueryParam);
                    $queryParams[$requiredQueryParam] = $message->$getter();
                }
            }
        }

        return [$body, $queryParams];
    }

    /**
     * @param array $placeholders
     * @param Message $message
     * @return array Bindings from path template fields to values from message
     */
    private function buildBindings(array $placeholders, Message $message)
    {
        $bindings = [];
        foreach ($placeholders as $placeholder => $metadata) {
            $value = array_reduce(
                $metadata['getters'],
                function (Message $result = null, $getter) {
                    if ($result) {
                        return $result->$getter();
                    }
                },
                $message
            );

            $bindings[$placeholder] = $value;
        }
        return $bindings;
    }

    /**
     * Try to render the resource name. The rendered resource name will always contain a leading '/'
     *
     * @param string $uriTemplate
     * @param array $bindings
     * @return null|string
     * @throws ValidationException
     */
    private function tryRenderPathTemplate(string $uriTemplate, array $bindings)
    {
        $template = new AbsoluteResourceTemplate($uriTemplate);

        try {
            return $template->render($bindings);
        } catch (ValidationException $e) {
            return null;
        }
    }

    /**
     * @param string $path
     * @param array $queryParams
     * @return UriInterface
     */
    private function buildUri(string $path, array $queryParams)
    {
        $uri = Utils::uriFor(
            sprintf(
                'https://%s%s',
                $this->baseUri,
                $path
            )
        );
        if ($queryParams) {
            $uri = $this->buildUriWithQuery(
                $uri,
                $queryParams
            );
        }
        return $uri;
    }
}
