<?php

namespace AlibabaCloud\Credentials\Request;

use AlibabaCloud\Credentials\Credentials;
use AlibabaCloud\Credentials\EcsRamRoleCredential;
use AlibabaCloud\Credentials\Helper;
use AlibabaCloud\Credentials\RamRoleArnCredential;
use AlibabaCloud\Credentials\Signature\ShaHmac1Signature;
use AlibabaCloud\Credentials\Signature\ShaHmac256WithRsaSignature;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Uri;
use AlibabaCloud\Tea\Response;
use Psr\Http\Message\ResponseInterface;

/**
 * RESTful RPC Request.
 */
class Request
{

    /**
     * Request Connect Timeout
     */
    const CONNECT_TIMEOUT = 5;

    /**
     * Request Timeout
     */
    const TIMEOUT = 10;

    /**
     * @var array
     */
    private static $config = [];

    /**
     * @var array
     */
    public $options = [];

    /**
     * @var Uri
     */
    public $uri;

    /**
     * @var EcsRamRoleCredential|RamRoleArnCredential
     */
    protected $credential;

    /**
     * @var ShaHmac256WithRsaSignature|ShaHmac1Signature
     */
    protected $signature;

    /**
     * Request constructor.
     */
    public function __construct()
    {
        $this->uri                        = (new Uri())->withScheme('https');
        $this->options['http_errors']     = false;
        $this->options['connect_timeout'] = self::CONNECT_TIMEOUT;
        $this->options['timeout']         = self::TIMEOUT;

        // Turn on debug mode based on environment variable.
        if (strtolower(Helper::env('DEBUG')) === 'sdk') {
            $this->options['debug'] = true;
        }
    }

    /**
     * @return ResponseInterface
     * @throws Exception
     */
    public function request()
    {
        $this->options['query']['Format']           = 'JSON';
        $this->options['query']['SignatureMethod']  = $this->signature->getMethod();
        $this->options['query']['SignatureVersion'] = $this->signature->getVersion();
        $this->options['query']['SignatureNonce']   = self::uuid(json_encode($this->options['query']));
        $this->options['query']['Timestamp']        = gmdate('Y-m-d\TH:i:s\Z');
        $this->options['query']['Signature']        = $this->signature->sign(
            self::signString('GET', $this->options['query']),
            $this->credential->getOriginalAccessKeySecret() . '&'
        );
        return self::createClient()->request('GET', (string)$this->uri, $this->options);
    }

    /**
     * @param string $salt
     *
     * @return string
     */
    public static function uuid($salt)
    {
        return md5($salt . uniqid(md5(microtime(true)), true));
    }

    /**
     * @param string $method
     * @param array  $parameters
     *
     * @return string
     */
    public static function signString($method, array $parameters)
    {
        ksort($parameters);
        $canonicalized = '';
        foreach ($parameters as $key => $value) {
            $canonicalized .= '&' . self::percentEncode($key) . '=' . self::percentEncode($value);
        }

        return $method . '&%2F&' . self::percentEncode(substr($canonicalized, 1));
    }

    /**
     * @param string $string
     *
     * @return null|string|string[]
     */
    private static function percentEncode($string)
    {
        $result = rawurlencode($string);
        $result = str_replace(['+', '*'], ['%20', '%2A'], $result);
        $result = preg_replace('/%7E/', '~', $result);

        return $result;
    }

    /**
     * @return Client
     * @throws Exception
     */
    public static function createClient()
    {
        if (Credentials::hasMock()) {
            $stack = HandlerStack::create(Credentials::getMock());
        } else {
            $stack = HandlerStack::create();
        }

        $stack->push(Middleware::mapResponse(static function (ResponseInterface $response) {
            return new Response($response);
        }));

        self::$config['handler'] = $stack;

        return new Client(self::$config);
    }
}
