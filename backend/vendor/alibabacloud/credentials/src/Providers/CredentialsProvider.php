<?php

namespace AlibabaCloud\Credentials\Providers;


/**
 * @internal This class is intended for internal use within the package. 
 * Interface CredentialsInterface
 *
 * @codeCoverageIgnore
 */
interface CredentialsProvider
{

    /**
     * @return Credentials
     */
    public function getCredentials();

    /**
     * @return string
     */
    public function getProviderName();
}
