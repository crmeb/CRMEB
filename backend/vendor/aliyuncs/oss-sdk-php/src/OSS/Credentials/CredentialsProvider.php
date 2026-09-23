<?php
namespace OSS\Credentials;

interface CredentialsProvider
{

    /**
     * @return Credentials
     */
    public function getCredentials();
}