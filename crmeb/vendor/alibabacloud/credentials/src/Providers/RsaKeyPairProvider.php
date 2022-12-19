<?php

namespace AlibabaCloud\Credentials\Providers;

use AlibabaCloud\Credentials\Request\GenerateSessionAccessKey;
use AlibabaCloud\Credentials\StsCredential;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

/**
 * Class RsaKeyPairProvider
 *
 * @package AlibabaCloud\Credentials\Providers
 */
class RsaKeyPairProvider extends Provider
{

    /**
     * Get credential.
     *
     *
     * @return StsCredential
     * @throws Exception
     * @throws GuzzleException
     */
    public function get()
    {
        $credential = $this->getCredentialsInCache();

        if ($credential === null) {
            $result = (new GenerateSessionAccessKey($this->credential))->request();

            if ($result->getStatusCode() !== 200) {
                throw new RuntimeException(isset($result['Message']) ? $result['Message'] : (string)$result->getBody());
            }

            if (!isset($result['SessionAccessKey']['SessionAccessKeyId'],
                $result['SessionAccessKey']['SessionAccessKeySecret'])) {
                throw new RuntimeException($this->error);
            }

            $credential = $result['SessionAccessKey'];
            $this->cache($credential);
        }

        return new StsCredential(
            $credential['SessionAccessKeyId'],
            $credential['SessionAccessKeySecret'],
            strtotime($credential['Expiration'])
        );
    }
}
