<?php
namespace Aws\Api\Serializer;

use Aws\Api\ListShape;
use Aws\Api\MapShape;
use Aws\Api\Service;
use Aws\Api\Operation;
use Aws\Api\Shape;
use Aws\Api\StructureShape;
use Aws\Api\TimestampShape;
use Aws\CommandInterface;
use Aws\EndpointV2\EndpointV2SerializerTrait;
use Aws\EndpointV2\Ruleset\RulesetEndpoint;
use DateTimeInterface;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Serializes HTTP locations like header, uri, payload, etc...
 * @internal
 */
abstract class RestSerializer
{
    private const TEMPLATE_STRING_REGEX = '/\{([^\}]+)\}/';

    private static array $excludeContentType = [
        's3' => true,
        'glacier' => true
    ];

    /** @var Service */
    private Service $api;

    /** @var Uri */
    private $endpoint;

    /** @var bool */
    private $isUseEndpointV2;

    use EndpointV2SerializerTrait;

    /**
     * @param Service $api Service API description
     * @param string $endpoint Endpoint to connect to
     */
    public function __construct(Service $api, $endpoint)
    {
        $this->api = $api;
        $this->endpoint = Psr7\Utils::uriFor($endpoint);
    }

    /**
     * @param CommandInterface $command Command to serialize into a request.
     * @param mixed|null $endpoint
     * @return RequestInterface
     */
    public function __invoke(
        CommandInterface $command,
        mixed $endpoint = null
    )
    {
        $operation = $this->api->getOperation($command->getName());
        $commandArgs = $command->toArray();
        $opts = $this->serialize($operation, $commandArgs);
        $headers = $opts['headers'] ?? [];

        if ($endpoint instanceof RulesetEndpoint) {
            $this->isUseEndpointV2 = true;
            $this->setEndpointV2RequestOptions($endpoint, $headers);
        }

        $uri = $this->buildEndpoint($operation, $commandArgs, $opts);

        return new Request(
            $operation['http']['method'],
            $uri,
            $headers,
            $opts['body'] ?? null
        );
    }

    /**
     * Modifies a hash of request options for a payload body.
     *
     * @param StructureShape $member Member to serialize
     * @param array $value Value to serialize
     * @param array $opts Request options to modify.
     */
    abstract protected function payload(
        StructureShape $member,
        array $value,
        array &$opts
    );

    private function serialize(Operation $operation, array $args)
    {
        $opts = [];
        $input = $operation->getInput();

        // Apply the payload trait if present
        if ($payload = $input['payload']) {
            $this->applyPayload($input, $payload, $args, $opts);
        }

        foreach ($args as $name => $value) {
            if ($input->hasMember($name)) {
                $member = $input->getMember($name);
                $location = $member['location'];
                if (!$payload && !$location) {
                    $bodyMembers[$name] = $value;
                } elseif ($location === 'header') {
                    $this->applyHeader($name, $member, $value, $opts);
                } elseif ($location === 'querystring') {
                    $this->applyQuery($name, $member, $value, $opts);
                } elseif ($location === 'headers') {
                    $this->applyHeaderMap($name, $member, $value, $opts);
                }
            }
        }

        if (isset($bodyMembers)) {
            $this->payload($input, $bodyMembers, $opts);
        } else if (!isset($opts['body']) && $this->hasPayloadParam($input, $payload)) {
            $this->payload($input, [], $opts);
        }

        return $opts;
    }

    private function applyPayload(StructureShape $input, $name, array $args, array &$opts)
    {
        if (!isset($args[$name])) {
            return;
        }

        $m = $input->getMember($name);

        $type = $m->getType();
        if ($m['streaming'] ||
           ($type === 'string' || $type === 'blob')
        ) {
            // This path skips setting the content-type header usually done in
            // RestJsonSerializer and RestXmlSerializer.certain S3 and glacier
            // operations determine content type in Middleware::ContentType()
            if (!isset(self::$excludeContentType[$this->api->getServiceName() ?? ''])) {
                switch ($type) {
                    case 'string':
                        $opts['headers']['Content-Type'] = 'text/plain';
                        break;
                    case 'blob':
                        $opts['headers']['Content-Type'] = 'application/octet-stream';
                        break;
                }
            }

            $body = $args[$name];
            if (!$m['streaming'] && is_string($body)) {
                $opts['headers']['Content-Length'] = (string) strlen($body);
            }

            // Streaming bodies or payloads that are strings are
            // always just a stream of data.
            $stream = Psr7\Utils::streamFor($body);
            // User-owned resource which should be detached instead of closed
            // during garbage-collection
            if (is_resource($body)) {
                $stream = \Aws\detach_on_close_stream($stream);
            }
            $opts['body'] = $stream;
            return;
        }

        $this->payload($m, $args[$name], $opts);
    }

    private function applyHeader($name, Shape $member, $value, array &$opts)
    {
        if ($value === null) {
            return;
        }

        // Handle lists by applying header logic to each element
        if ($member instanceof ListShape) {
            if (!is_array($value)) {
                throw new \InvalidArgumentException('Header values must be scalar or an array of scalars.');
            }

            $listMember = $member->getMember();
            $headerValues = [];

            foreach ($value as $listValue) {
                if ($listValue === null) {
                    throw new \InvalidArgumentException('Header values must be scalar or an array of scalars.');
                }

                $tempOpts = ['headers' => []];
                $this->applyHeader('temp', $listMember, $listValue, $tempOpts);
                if (!array_key_exists('temp', $tempOpts['headers'])) {
                    throw new \InvalidArgumentException('Header values must be scalar or an array of scalars.');
                }

                $convertedValue = $tempOpts['headers']['temp'];
                $headerValues[] = $convertedValue;
            }

            $value = $headerValues;
        } else {
            switch ($member->getType()) {
                case 'timestamp':
                    $timestampFormat = $member['timestampFormat'] ?? 'rfc822';
                    $value = $this->formatTimestamp($value, $timestampFormat);
                    break;
                case 'boolean':
                    $value = $this->formatBoolean($value);
                    break;
            }
        }

        if ($member['jsonvalue']) {
            $value = json_encode($value);
            if (empty($value) && JSON_ERROR_NONE !== json_last_error()) {
                throw new \InvalidArgumentException('Unable to encode the provided value'
                    . ' with \'json_encode\'. ' . json_last_error_msg());
            }

            $value = base64_encode($value);
        }

        $opts['headers'][$member['locationName'] ?: $name] = self::prepareHeaderValue($value);
    }

    /**
     * Note: This is currently only present in the Amazon S3 model.
     */
    private function applyHeaderMap($name, Shape $member, array $value, array &$opts)
    {
        $prefix = $member['locationName'];
        foreach ($value as $k => $v) {
            if ($v === null) {
                continue;
            }

            $opts['headers'][$prefix . $k] = self::prepareHeaderValue($v);
        }
    }

    /**
     * @return string|string[]
     */
    private static function prepareHeaderValue($value)
    {
        if (is_scalar($value)) {
            return (string) $value;
        }

        if (is_array($value)) {
            if ($value === []) {
                return '';
            }

            foreach ($value as $key => $item) {
                if (!is_scalar($item)) {
                    throw new \InvalidArgumentException('Header values must be scalar or an array of scalars.');
                }

                $value[$key] = (string) $item;
            }

            return $value;
        }

        throw new \InvalidArgumentException('Header values must be scalar or an array of scalars.');
    }

    private function applyQuery($name, Shape $member, $value, array &$opts)
    {
        if ($member instanceof MapShape) {
            $opts['query'] = isset($opts['query']) && is_array($opts['query'])
                ? $opts['query'] + $value
                : $value;
        } elseif ($member instanceof ListShape) {
            $listMember = $member->getMember();
            $paramName = $member['locationName'] ?: $name;

            foreach ($value as $listValue) {
                // Recursively call applyQuery for each list element
                $tempOpts = ['query' => []];
                $this->applyQuery('temp', $listMember, $listValue, $tempOpts);
                $opts['query'][$paramName][] = $tempOpts['query']['temp'];
            }
        } elseif (!is_null($value)) {
            switch ($member->getType()) {
                case 'timestamp':
                    $timestampFormat = $member['timestampFormat'] ?? 'iso8601';
                    $value = $this->formatTimestamp($value, $timestampFormat);
                    break;
                case 'boolean':
                    $value = $this->formatBoolean($value);
                    break;
            }

            $opts['query'][$member['locationName'] ?: $name] = $value;
        }
    }

    private function buildEndpoint(
        Operation $operation,
        array $args,
        array $opts
    ): UriInterface
    {
        // Expand `requestUri` field members
        $relativeUri = $this->expandUriTemplate($operation, $args);

        // Add query members to relativeUri
        if (!empty($opts['query'])) {
            $relativeUri = $this->appendQuery($opts['query'], $relativeUri);
        }

        // Special case - S3 keys that need path preservation
        if ($this->api->getServiceName() === 's3'
            && isset($args['Key'])
            && $this->shouldPreservePath($args['Key'])
        ) {
            return new Uri($this->endpoint . $relativeUri);
        }

        return $this->resolveUri($relativeUri, $opts);
    }

    /**
     * Expands `requestUri` members
     *
     * @param Operation $operation
     * @param array $args
     *
     * @return string
     */
    private function expandUriTemplate(Operation $operation, array $args): string
    {
        $varDefinitions = $this->getVarDefinitions($operation, $args);

        return preg_replace_callback(
            self::TEMPLATE_STRING_REGEX,
            static function (array $matches) use ($varDefinitions) {
                $isGreedy = str_ends_with($matches[1], '+');
                $varName = $isGreedy ? substr($matches[1], 0, -1) : $matches[1];

                if (!isset($varDefinitions[$varName])) {
                    return '';
                }

                $value = $varDefinitions[$varName];

                if ($isGreedy) {
                    return str_replace('%2F', '/', rawurlencode($value));
                }

                return rawurlencode($value);
            },
            $operation['http']['requestUri']
        );
    }

    /**
     * Checks for path-like key names. If detected, traditional
     * URI resolution is bypassed.
     *
     * @param string $key
     * @return bool
     */
    private function shouldPreservePath(string $key): bool
    {
        // Keys with dot segments
        if (str_contains($key, '.')) {
            $segments = explode('/', $key);
            foreach ($segments as $segment) {
                if ($segment === '.' || $segment === '..') {
                    return true;
                }
            }
        }

        // Keys starting with slash
        if (str_starts_with($key, '/')) {
            return true;
        }

        return false;
    }

    /**
     * @param string $relativeUri
     * @param array $opts
     *
     * @return UriInterface
     */
    private function resolveUri(string $relativeUri, array $opts): UriInterface
    {
        $basePath = $this->endpoint->getPath();

        // Only process if we have a non-empty base path
        if (!empty($basePath) && $basePath !== '/') {
            // if relative is just '/', we want just the base path without trailing slash
            if ($relativeUri === '/' || empty($relativeUri)) {
                // Remove trailing slash if present
                return $this->endpoint->withPath(rtrim($basePath, '/'));
            }

            // if relative is '/?query', we want base path without trailing slash + query
            // for now, this is only seen with S3 GetBucketLocation after processing the model
            if (empty($opts['query'])
                && str_starts_with($relativeUri, '/?')
            ) {
                $query = substr($relativeUri, 2); // Remove '/?'
                return $this->endpoint->withQuery($query);
            }

            // Ensure base path has trailing slash
            if (!str_ends_with($basePath, '/')) {
                $this->endpoint = $this->endpoint->withPath($basePath . '/');
            }

            // Remove leading slash from relative path to make it relative
            if (str_starts_with($relativeUri, '/')) {
                $relativeUri = substr($relativeUri, 1);
            }
        }

        return UriResolver::resolve($this->endpoint, new Uri($relativeUri));
    }

    /**
     * @param StructureShape $input
     * @param $payload
     *
     * @return bool
     */
    private function hasPayloadParam(StructureShape $input, $payload)
    {
        if ($payload) {
            $potentiallyEmptyTypes = ['blob','string'];
            if ($this->api->getProtocol() === 'rest-xml') {
                $potentiallyEmptyTypes[] = 'structure';
            }

            $payloadMember = $input->getMember($payload);
            //unions may also be empty/unset
            if (!empty($payloadMember['union'])
                || in_array($payloadMember['type'], $potentiallyEmptyTypes)
            ) {
                return false;
            }
        }

        foreach ($input->getMembers() as $member) {
            if (!isset($member['location'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $query
     * @param $relativeUri
     *
     * @return string
     */
    private function appendQuery($query, $relativeUri): string
    {
        $append = Psr7\Query::build($query);
        return $relativeUri
            . (str_contains($relativeUri, '?') ? "&{$append}" : "?{$append}");
    }

    /**
     * @param CommandInterface $command
     * @param array $args
     *
     * @return array
     */
    private function getVarDefinitions(
        Operation $operation,
        array $args
    ): array
    {
        $varDefinitions = [];

        foreach ($operation->getInput()->getMembers() as $name => $member) {
            if ($member['location'] === 'uri') {
                $value = $args[$name] ?? null;
                if (!is_null($value)) {
                    switch ($member->getType()) {
                        case 'timestamp':
                            $timestampFormat = $member['timestampFormat'] ?? 'iso8601';
                            $value = $this->formatTimestamp($value, $timestampFormat);
                            break;
                        case 'boolean':
                            $value = $this->formatBoolean($value);
                            break;
                    }
                }

                $varDefinitions[$member['locationName'] ?: $name] = $value;
            }
        }

        return $varDefinitions;
    }

    /**
     * @param DateTimeInterface|string|int $value
     * @param string $timestampFormat
     *
     * @return string
     */
    private function formatTimestamp(
        DateTimeInterface|string|int $value,
        string $timestampFormat
    ): string
    {
        return TimestampShape::format($value, $timestampFormat);
    }

    /**
     * @param $value
     *
     * @return string
     */
    private function formatBoolean($value): string
    {
        return $value ? 'true' : 'false';
    }
}
