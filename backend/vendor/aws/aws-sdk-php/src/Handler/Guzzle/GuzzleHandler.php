<?php
namespace Aws\Handler\Guzzle;

use Aws\Handler\HttpHandlerError;
use Aws\Handler\HttpTransportSharing;
use GuzzleHttp\Utils;
use GuzzleHttp\Promise;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\RequestInterface as Psr7Request;

/**
 * A request handler that sends PSR-7-compatible requests with Guzzle.
 */
class GuzzleHandler
{
    /** @var ClientInterface */
    private $client;

    /**
     * @param ClientInterface|null $client
     * @param string|null          $transportSharing
     */
    public function __construct(
        ?ClientInterface $client = null,
        ?string $transportSharing = null
    ) {
        if ($client !== null && HttpTransportSharing::isRequired($transportSharing)) {
            throw new \InvalidArgumentException('The provided transport'
                . ' sharing mode cannot require sharing when a client is'
                . ' provided. Configure the "transport_sharing" option on'
                . ' the provided client instead.');
        }

        $this->client = $client ?: new Client(
            HttpTransportSharing::toClientConfig($transportSharing)
        );
    }

    /**
     * @param Psr7Request $request
     * @param array       $options
     *
     * @return Promise\PromiseInterface
     */
    public function __invoke(Psr7Request $request, array $options = [])
    {
        $request = $request->withHeader(
            'User-Agent',
            $request->getHeaderLine('User-Agent')
                . ' ' . Utils::defaultUserAgent()
        );

        return $this->client->sendAsync($request, $this->parseOptions($options))
            ->otherwise(
                static function (\Throwable $e) {
                    return new Promise\RejectedPromise([
                        'exception'        => $e,
                        'connection_error' => HttpHandlerError::isConnectionError($e),
                        'response'         => HttpHandlerError::getResponse($e),
                    ]);
                }
            );
    }

    private function parseOptions(array $options)
    {
        if (isset($options['http_stats_receiver'])) {
            $fn = $options['http_stats_receiver'];
            unset($options['http_stats_receiver']);

            $prev = isset($options['on_stats'])
                ? $options['on_stats']
                : null;

            $options['on_stats'] = static function (
                TransferStats $stats
            ) use ($fn, $prev) {
                if (is_callable($prev)) {
                    $prev($stats);
                }
                $transferStats = ['total_time' => $stats->getTransferTime()];
                $transferStats += $stats->getHandlerStats();
                $fn($transferStats);
            };
        }

        return $options;
    }
}
