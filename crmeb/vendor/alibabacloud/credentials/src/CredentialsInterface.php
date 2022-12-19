<?php

namespace AlibabaCloud\Credentials;

use AlibabaCloud\Credentials\Signature\SignatureInterface;

/**
 * Interface CredentialsInterface
 *
 * @codeCoverageIgnore
 */
interface CredentialsInterface
{
    /**
     * @return string
     */
    public function __toString();

    /**
     * @return SignatureInterface
     */
    public function getSignature();
}
