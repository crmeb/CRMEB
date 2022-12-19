<?php

namespace AlibabaCloud\Credentials\Providers;

use AlibabaCloud\Credentials\CredentialsInterface;
use AlibabaCloud\Credentials\EcsRamRoleCredential;
use AlibabaCloud\Credentials\RamRoleArnCredential;
use AlibabaCloud\Credentials\RsaKeyPairCredential;

abstract class Provider
{
    /**
     * For TSC Duration Seconds
     */
    const DURATION_SECONDS = 3600;

    /**
     * @var array
     */
    protected static $credentialsCache = [];

    /**
     * Expiration time slot for temporary security credentials.
     *
     * @var int
     */
    protected $expirationSlot = 180;

    /**
     * @var RamRoleArnCredential|RsaKeyPairCredential|EcsRamRoleCredential
     */
    protected $credential;

    /**
     * @var string
     */
    protected $error = 'Result contains no credentials';

    /**
     * @var array
     */
    protected $config = [];

    /**
     * CredentialTrait constructor.
     *
     * @param CredentialsInterface $credential
     * @param array                $config
     */
    public function __construct(CredentialsInterface $credential, $config = [])
    {
        $this->credential = $credential;
        $this->config     = $config;
    }

    /**
     * Get the credentials from the cache in the validity period.
     *
     * @return array|null
     */
    public function getCredentialsInCache()
    {
        if (isset(self::$credentialsCache[(string)$this->credential])) {
            $result = self::$credentialsCache[(string)$this->credential];
            if (\strtotime($result['Expiration']) - \time() >= $this->expirationSlot) {
                return $result;
            }
        }

        return null;
    }

    /**
     * Cache credentials.
     *
     * @param array $credential
     */
    protected function cache(array $credential)
    {
        self::$credentialsCache[(string)$this->credential] = $credential;
    }
}
