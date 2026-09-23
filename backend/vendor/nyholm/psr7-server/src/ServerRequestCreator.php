<?php

declare(strict_types=1);

namespace Nyholm\Psr7Server;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 * @author Martijn van der Ven <martijn@vanderven.se>
 */
final class ServerRequestCreator implements ServerRequestCreatorInterface
{
    private $serverRequestFactory;

    private $uriFactory;

    private $uploadedFileFactory;

    private $streamFactory;

    public function __construct(
        ServerRequestFactoryInterface $serverRequestFactory,
        UriFactoryInterface $uriFactory,
        UploadedFileFactoryInterface $uploadedFileFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->serverRequestFactory = $serverRequestFactory;
        $this->uriFactory = $uriFactory;
        $this->uploadedFileFactory = $uploadedFileFactory;
        $this->streamFactory = $streamFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function fromGlobals(): ServerRequestInterface
    {
        $server = $_SERVER;
        if (false === isset($server['REQUEST_METHOD'])) {
            $server['REQUEST_METHOD'] = 'GET';
        }

        $headers = \function_exists('getallheaders') ? getallheaders() : static::getHeadersFromServer($_SERVER);

        $post = null;
        if ('POST' === $this->getMethodFromEnv($server)) {
            foreach ($headers as $headerName => $headerValue) {
                if (true === \is_int($headerName) || 'content-type' !== \strtolower($headerName)) {
                    continue;
                }
                if (\in_array(
                    \strtolower(\trim(\explode(';', $headerValue, 2)[0])),
                    ['application/x-www-form-urlencoded', 'multipart/form-data']
                )) {
                    $post = $_POST;

                    break;
                }
            }
        }

        return $this->fromArrays($server, $headers, $_COOKIE, $_GET, $post, $_FILES, \fopen('php://input', 'r') ?: null);
    }

    /**
     * {@inheritdoc}
     */
    public function fromArrays(array $server, array $headers = [], array $cookie = [], array $get = [], ?array $post = null, array $files = [], $body = null): ServerRequestInterface
    {
        $method = $this->getMethodFromEnv($server);
        $uri = $this->getUriFromEnvWithHTTP($server);
        $protocol = isset($server['SERVER_PROTOCOL']) ? \str_replace('HTTP/', '', $server['SERVER_PROTOCOL']) : '1.1';

        $serverRequest = $this->serverRequestFactory->createServerRequest($method, $uri, $server);
        foreach ($headers as $name => $value) {
            // Because PHP automatically casts array keys set with numeric strings to integers, we have to make sure
            // that numeric headers will not be sent along as integers, as withAddedHeader can only accept strings.
            if (\is_int($name)) {
                $name = (string) $name;
            }
            $serverRequest = $serverRequest->withAddedHeader($name, $value);
        }

        $serverRequest = $serverRequest
            ->withProtocolVersion($protocol)
            ->withCookieParams($cookie)
            ->withQueryParams($get)
            ->withParsedBody($post)
            ->withUploadedFiles($this->normalizeFiles($files));

        if (null === $body) {
            return $serverRequest;
        }

        if (\is_resource($body)) {
            $body = $this->streamFactory->createStreamFromResource($body);
        } elseif (\is_string($body)) {
            $body = $this->streamFactory->createStream($body);
        } elseif (!$body instanceof StreamInterface) {
            throw new \InvalidArgumentException('The $body parameter to ServerRequestCreator::fromArrays must be string, resource or StreamInterface');
        }

        return $serverRequest->withBody($body);
    }

    /**
     * Implementation from Laminas\Diactoros\marshalHeadersFromSapi().
     */
    public static function getHeadersFromServer(array $server): array
    {
        $headers = [];
        foreach ($server as $key => $value) {
            // Apache prefixes environment variables with REDIRECT_
            // if they are added by rewrite rules
            if (0 === \strpos($key, 'REDIRECT_')) {
                $key = \substr($key, 9);

                // We will not overwrite existing variables with the
                // prefixed versions, though
                if (\array_key_exists($key, $server)) {
                    continue;
                }
            }

            if ($value && 0 === \strpos($key, 'HTTP_')) {
                $name = \strtr(\strtolower(\substr($key, 5)), '_', '-');
                $headers[$name] = $value;

                continue;
            }

            if ($value && 0 === \strpos($key, 'CONTENT_')) {
                $name = 'content-'.\strtolower(\substr($key, 8));
                $headers[$name] = $value;

                continue;
            }
        }

        return $headers;
    }

    private function getMethodFromEnv(array $environment): string
    {
        if (false === isset($environment['REQUEST_METHOD'])) {
            throw new \InvalidArgumentException('Cannot determine HTTP method');
        }

        return $environment['REQUEST_METHOD'];
    }

    private function getUriFromEnvWithHTTP(array $environment): UriInterface
    {
        $uri = $this->createUriFromArray($environment);
        if (empty($uri->getScheme())) {
            $uri = $uri->withScheme('http');
        }

        return $uri;
    }

    /**
     * Return an UploadedFile instance array.
     *
     * @param array $files A array which respect $_FILES structure
     *
     * @return UploadedFileInterface[]
     *
     * @throws \InvalidArgumentException for unrecognized values
     */
    private function normalizeFiles(array $files): array
    {
        $normalized = [];

        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$key] = $value;
            } elseif (\is_array($value) && isset($value['tmp_name'])) {
                $normalized[$key] = $this->createUploadedFileFromSpec($value);
            } elseif (\is_array($value)) {
                $normalized[$key] = $this->normalizeFiles($value);
            } else {
                throw new \InvalidArgumentException('Invalid value in files specification');
            }
        }

        return $normalized;
    }

    /**
     * Create and return an UploadedFile instance from a $_FILES specification.
     *
     * If the specification represents an array of values, this method will
     * delegate to normalizeNestedFileSpec() and return that return value.
     *
     * @param array $value $_FILES struct
     *
     * @return array|UploadedFileInterface
     */
    private function createUploadedFileFromSpec(array $value)
    {
        if (\is_array($value['tmp_name'])) {
            return $this->normalizeNestedFileSpec($value);
        }

        if (UPLOAD_ERR_OK !== $value['error']) {
            $stream = $this->streamFactory->createStream();
        } else {
            try {
                $stream = $this->streamFactory->createStreamFromFile($value['tmp_name']);
            } catch (\RuntimeException $e) {
                $stream = $this->streamFactory->createStream();
            }
        }

        return $this->uploadedFileFactory->createUploadedFile(
            $stream,
            (int) $value['size'],
            (int) $value['error'],
            $value['name'],
            $value['type']
        );
    }

    /**
     * Normalize an array of file specifications.
     *
     * Loops through all nested files and returns a normalized array of
     * UploadedFileInterface instances.
     *
     * @return UploadedFileInterface[]
     */
    private function normalizeNestedFileSpec(array $files = []): array
    {
        $normalizedFiles = [];

        foreach (\array_keys($files['tmp_name']) as $key) {
            $spec = [
                'tmp_name' => $files['tmp_name'][$key],
                'size' => $files['size'][$key],
                'error' => $files['error'][$key],
                'name' => $files['name'][$key],
                'type' => $files['type'][$key],
            ];
            $normalizedFiles[$key] = $this->createUploadedFileFromSpec($spec);
        }

        return $normalizedFiles;
    }

    /**
     * Create a new uri from server variable.
     *
     * @param array $server typically $_SERVER or similar structure
     */
    private function createUriFromArray(array $server): UriInterface
    {
        $uri = $this->uriFactory->createUri('');

        if (isset($server['HTTP_X_FORWARDED_PROTO'])) {
            $uri = $uri->withScheme($server['HTTP_X_FORWARDED_PROTO']);
        } else {
            if (isset($server['REQUEST_SCHEME'])) {
                $uri = $uri->withScheme($server['REQUEST_SCHEME']);
            } elseif (isset($server['HTTPS'])) {
                $uri = $uri->withScheme('on' === $server['HTTPS'] ? 'https' : 'http');
            }

            if (isset($server['SERVER_PORT'])) {
                $uri = $uri->withPort($server['SERVER_PORT']);
            }
        }

        if (isset($server['HTTP_HOST'])) {
            if (1 === \preg_match('/^(.+)\:(\d+)$/', $server['HTTP_HOST'], $matches)) {
                $uri = $uri->withHost($matches[1])->withPort($matches[2]);
            } else {
                $uri = $uri->withHost($server['HTTP_HOST']);
            }
        } elseif (isset($server['SERVER_NAME'])) {
            $uri = $uri->withHost($server['SERVER_NAME']);
        }

        if (isset($server['REQUEST_URI'])) {
            $uri = $uri->withPath(\current(\explode('?', $server['REQUEST_URI'])));
        }

        if (isset($server['QUERY_STRING'])) {
            $uri = $uri->withQuery($server['QUERY_STRING']);
        }

        return $uri;
    }
}
