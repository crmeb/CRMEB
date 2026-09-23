<?php
namespace OSS\Credentials;

use OSS\Core\OssException;

class EnvironmentVariableCredentialsProvider implements CredentialsProvider
{

    /**
     * @return Credentials
     * @throws OssException
     */
    public function getCredentials()
    {
        $ak= getenv('OSS_ACCESS_KEY_ID');
        $sk = getenv('OSS_ACCESS_KEY_SECRET');
        $token =  getenv('OSS_SESSION_TOKEN');
        return new Credentials($ak, $sk, $token);
    }
}
