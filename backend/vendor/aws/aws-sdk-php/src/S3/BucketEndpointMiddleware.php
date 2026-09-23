<?php
namespace Aws\S3;

use Aws\CommandInterface;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;

/**
 * Used to update the host used for S3 requests in the case of using a
 * "bucket endpoint" or CNAME bucket.
 *
 * IMPORTANT: this middleware must be added after the "build" step.
 *
 * @internal
 */
class BucketEndpointMiddleware
{
    private static $exclusions = ['GetBucketLocation' => true];
    private $nextHandler;
    private bool $useEndpointV2;
    private ?string $endpoint;

    /**
     * Create a middleware wrapper function.
     *
     * @param bool $useEndpointV2
     * @param string|null $endpoint
     *
     * @return callable
     */
    public static function wrap(
        bool $useEndpointV2 = false,
        ?string $endpoint = null
    ): callable
    {
        return function (callable $handler) use ($useEndpointV2, $endpoint) {
            return new self($handler, $useEndpointV2, $endpoint);
        };
    }

    public function __construct(
        callable $nextHandler,
        bool $useEndpointV2,
        ?string $endpoint = null
    )
    {
        $this->nextHandler = $nextHandler;
        $this->useEndpointV2 = $useEndpointV2;
        $this->endpoint = $endpoint;
    }

    public function __invoke(CommandInterface $command, RequestInterface $request)
    {
        $nextHandler = $this->nextHandler;
        $bucket = $command['Bucket'];

        if ($bucket && !isset(self::$exclusions[$command->getName()])) {
            $request = $this->modifyRequest($request, $command);
        }

        return $nextHandler($command, $request);
    }

    /**
     * @param string $path
     * @param string $bucket
     *
     * @return string
     */
    private function removeBucketFromPath(string $path, string $bucket): string
    {
        $len = strlen($bucket) + 1;
        if (str_starts_with($path, "/{$bucket}")) {
            $path = substr($path, $len);
        }

        return $path ?: '/';
    }

    /**
     * @param RequestInterface $request
     * @param CommandInterface $command
     *
     * @return RequestInterface
     */
    private function modifyRequest(
        RequestInterface $request,
        CommandInterface $command
    ): RequestInterface
    {
        $uri = $request->getUri();
        $path = $uri->getPath();
        $host = $uri->getHost();
        $bucket = $command['Bucket'];

        if ($this->useEndpointV2 && !empty($this->endpoint)) {
            // V2 provider adds bucket name to host by default
            // preserve original host
            $host = (new Uri($this->endpoint))->getHost();
        }

        $path = $this->removeBucketFromPath($path, $bucket);

        // Modify the Key to make sure the key is encoded, but slashes are not.
        if ($command['Key']) {
            $path = S3Client::encodeKey(rawurldecode($path));
        }

        return $request->withUri($uri->withPath($path)->withHost($host));
    }
}
