<?php

namespace AlibabaCloud\Credentials;

use AlibabaCloud\Credentials\Credential\CredentialModel;
use AlibabaCloud\Credentials\Signature\SignatureInterface;

/**
 * @internal This class is intended for internal use within the package. 
 * Interface CredentialsInterface
 *
 * @codeCoverageIgnore
 */
interface CredentialsInterface
{
    /**
     * @deprecated
     * @return string
     */
    public function __toString();

    /**
     * @deprecated
     * @return SignatureInterface
     */
    public function getSignature();

    /**
     * @return CredentialModel
     */
    public function getCredential();
}
