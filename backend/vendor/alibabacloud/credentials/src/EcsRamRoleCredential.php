<?php

namespace AlibabaCloud\Credentials;

use AlibabaCloud\Credentials\Providers\EcsRamRoleCredentialsProvider;
use AlibabaCloud\Credentials\Credential\CredentialModel;
use AlibabaCloud\Credentials\Signature\ShaHmac1Signature;
use AlibabaCloud\Credentials\Request\Request;
use AlibabaCloud\Credentials\Utils\Filter;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use RuntimeException;

/**
 * @deprecated
 * Use the RAM role of an ECS instance to complete the authentication.
 */
class EcsRamRoleCredential implements CredentialsInterface
{

    /**
     * @var string
     */
    private $roleName;

    /**
     * @var boolean
     */
    private $disableIMDSv1;

    /**
     * @var int
     */
    private $metadataTokenDuration;


    /**
     * EcsRamRoleCredential constructor.
     *
     * @param $role_name
     */
    public function __construct($role_name = null, $disable_imdsv1 = false, $metadata_token_duration = 21600)
    {
        Filter::roleName($role_name);

        $this->roleName = $role_name;

        Filter::disableIMDSv1($disable_imdsv1);

        $this->disableIMDSv1 = $disable_imdsv1;

        $this->metadataTokenDuration = $metadata_token_duration;
    }

    /**
     * @return string
     * @throws GuzzleException
     * @throws Exception
     */
    public function getRoleName()
    {
        if ($this->roleName !== null) {
            return $this->roleName;
        }

        $this->roleName = $this->getRoleNameFromMeta();

        return $this->roleName;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getRoleNameFromMeta()
    {
        $options = [
            'http_errors' => false,
            'timeout' => 1,
            'connect_timeout' => 1,
        ];

        $result = Request::createClient()->request(
            'GET',
            'http://100.100.100.200/latest/meta-data/ram/security-credentials/',
            $options
        );

        if ($result->getStatusCode() === 404) {
            throw new InvalidArgumentException('The role name was not found in the instance');
        }

        if ($result->getStatusCode() !== 200) {
            throw new RuntimeException('Error retrieving credentials from result: ' . $result->getBody());
        }

        $role_name = (string) $result;
        if (!$role_name) {
            throw new RuntimeException('Error retrieving credentials from result is empty');
        }

        return $role_name;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "roleName#$this->roleName";
    }

    /**
     * @return ShaHmac1Signature
     */
    public function getSignature()
    {
        return new ShaHmac1Signature();
    }

    /**
     * @return string
     * @throws Exception
     * @throws GuzzleException
     */
    public function getAccessKeyId()
    {
        return $this->getSessionCredential()->getAccessKeyId();
    }

    /**
     * @return AlibabaCloud\Credentials\Providers\Credentials
     * @throws Exception
     * @throws GuzzleException
     */
    protected function getSessionCredential()
    {
        $params = [
            "roleName" => $this->roleName,
            'disableIMDSv1' => $this->disableIMDSv1,
            'metadataTokenDuration' => $this->metadataTokenDuration,
        ];
        return (new EcsRamRoleCredentialsProvider($params))->getCredentials();
    }

    /**
     * @return string
     * @throws Exception
     * @throws GuzzleException
     */
    public function getAccessKeySecret()
    {
        return $this->getSessionCredential()->getAccessKeySecret();
    }

    /**
     * @return string
     * @throws Exception
     * @throws GuzzleException
     */
    public function getSecurityToken()
    {
        return $this->getSessionCredential()->getSecurityToken();
    }

    /**
     * @return int
     * @throws Exception
     * @throws GuzzleException
     */
    public function getExpiration()
    {
        return $this->getSessionCredential()->getExpiration();
    }

    /**
     * @return bool
     */
    public function isDisableIMDSv1()
    {
        return $this->disableIMDSv1;
    }

    /**
     * @inheritDoc
     */
    public function getCredential()
    {
        $credentials = $this->getSessionCredential();
        return new CredentialModel([
            'accessKeyId' => $credentials->getAccessKeyId(),
            'accessKeySecret' => $credentials->getAccessKeySecret(),
            'securityToken' => $credentials->getSecurityToken(),
            'type' => 'ecs_ram_role',
        ]);
    }

}
