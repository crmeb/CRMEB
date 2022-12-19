<?php

namespace AlibabaCloud\Credentials\Providers;

use AlibabaCloud\Credentials\Request\Request;
use AlibabaCloud\Credentials\StsCredential;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use AlibabaCloud\Tea\Response;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * Class EcsRamRoleProvider
 *
 * @package AlibabaCloud\Credentials\Providers
 */
class EcsRamRoleProvider extends Provider
{

    /**
     * Expiration time slot for temporary security credentials.
     *
     * @var int
     */
    protected $expirationSlot = 10;

    /**
     * @var string
     */
    private $uri = 'http://100.100.100.200/latest/meta-data/ram/security-credentials/';

    /**
     * Get credential.
     *
     * @return StsCredential
     * @throws Exception
     * @throws GuzzleException
     */
    public function get()
    {
        $result = $this->getCredentialsInCache();

        if ($result === null) {
            $result = $this->request();

            if (!isset($result['AccessKeyId'], $result['AccessKeySecret'], $result['SecurityToken'])) {
                throw new RuntimeException($this->error);
            }

            $this->cache($result->toArray());
        }

        return new StsCredential(
            $result['AccessKeyId'],
            $result['AccessKeySecret'],
            strtotime($result['Expiration']),
            $result['SecurityToken']
        );
    }

    /**
     * Get credentials by request.
     *
     * @return ResponseInterface
     * @throws Exception
     * @throws GuzzleException
     */
    public function request()
    {
        $credential = $this->credential;
        $url        = $this->uri . $credential->getRoleName();

        $options = [
            'http_errors'     => false,
            'timeout'         => 1,
            'connect_timeout' => 1,
        ];

        $result = Request::createClient()->request('GET', $url, $options);

        if ($result->getStatusCode() === 404) {
            $message = 'The role was not found in the instance';
            throw new InvalidArgumentException($message);
        }

        if ($result->getStatusCode() !== 200) {
            throw new RuntimeException('Error retrieving credentials from result: ' . $result->toJson());
        }

        return $result;
    }
}
