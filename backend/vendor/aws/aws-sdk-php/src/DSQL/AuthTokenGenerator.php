<?php

namespace Aws\DSQL;

use Aws\Credentials\CredentialsInterface;
use Aws\Credentials\Credentials;
use Aws\Signature\SignatureV4;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Promise;
use Aws;

/**
 * Generates auth tokens to connect to DSQL database clusters.
 */
class AuthTokenGenerator
{
    private const DB_CONNECT = 'DbConnect';
    private const DB_CONNECT_ADMIN = 'DbConnectAdmin';
    private const SIGNING_NAME = 'dsql';
    private const DEFAULT_EXPIRATION_TIME_SECONDS = 900;

    /**
     * @var Credentials|callable
     */
    private $credentialProvider;

    /**
     * The constructor takes an instance of Credentials or a CredentialProvider
     *
     * @param Credentials|callable $creds
     */
    public function __construct(Credentials | callable $creds)
    {
        if ($creds instanceof CredentialsInterface) {
            $promise = new Promise\FulfilledPromise($creds);
            $this->credentialProvider = Aws\constantly($promise);
        } else {
            $this->credentialProvider = $creds;
        }
    }

    /**
     * @param string $endpoint
     * @param string $region
     * @param int $expiration
     *
     * @return string
     */
    public function generateDbConnectAuthToken(
        string $endpoint,
        string $region,
        int $expiration = self::DEFAULT_EXPIRATION_TIME_SECONDS
    ): string
    {
        return $this->createToken($endpoint, $region, self::DB_CONNECT, $expiration);
    }

    /**
     * @param string $endpoint
     * @param string $region
     * @param int $expiration
     *
     * @return string
     */
    public function generateDbConnectAdminAuthToken(
        string $endpoint,
        string $region,
        int $expiration = self::DEFAULT_EXPIRATION_TIME_SECONDS
    ): string
    {
        return $this->createToken($endpoint, $region, self::DB_CONNECT_ADMIN, $expiration);
    }

    /**
     * Creates token for database connection
     *
     * @param string $endpoint The database hostname
     * @param string $region The region where the database is located
     * @param string $action Db action to perform
     * @param int $expiration The expiration of the token in seconds
     *
     * @return string Generated token
     */
    private function createToken(
        string $endpoint,
        string $region,
        string $action,
        int $expiration
    ): string
    {
        if ($expiration <= 0) {
            throw new \InvalidArgumentException(
                "Lifetime must be a positive number, was {$expiration}"
            );
        }

        if (empty($region)) {
            throw new \InvalidArgumentException('Region must be a non-empty string.');
        }

        if (empty($endpoint)) {
            throw new \InvalidArgumentException('Endpoint must be a non-empty string.');
        }

        $uri = new Uri($endpoint);
        if (empty($uri->getHost())) {
            $uri = $uri->withHost($endpoint);
        }
        $uri = $uri->withPath('/')->withQuery('Action=' . $action);

        $request = new Request('GET', $uri);
        $signer = new SignatureV4(self::SIGNING_NAME, $region);
        $provider = $this->credentialProvider;

        $url = (string) $signer->presign(
            $request,
            $provider()->wait(),
            '+' . $expiration . ' seconds'
        )->getUri();

        // Remove 2 extra slash from the presigned url result
        return substr($url, 2);
    }
}

