<?php
/*
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Auth;

use phpseclib\Crypt\RSA;

/**
 * Sign a string using a Service Account private key.
 */
trait ServiceAccountSignerTrait
{
    /**
     * Sign a string using the service account private key.
     *
     * @param string $stringToSign
     * @param bool $forceOpenssl Whether to use OpenSSL regardless of
     *        whether phpseclib is installed. **Defaults to** `false`.
     * @return string
     */
    public function signBlob($stringToSign, $forceOpenssl = false)
    {
        $privateKey = $this->auth->getSigningKey();

        $signedString = '';
        if (class_exists('\\phpseclib\\Crypt\\RSA') && !$forceOpenssl) {
            $rsa = new RSA();
            $rsa->loadKey($privateKey);
            $rsa->setSignatureMode(RSA::SIGNATURE_PKCS1);
            $rsa->setHash('sha256');

            $signedString = $rsa->sign($stringToSign);
        } elseif (extension_loaded('openssl')) {
            openssl_sign($stringToSign, $signedString, $privateKey, 'sha256WithRSAEncryption');
        } else {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('OpenSSL is not installed.');
        }
        // @codeCoverageIgnoreEnd

        return base64_encode($signedString);
    }
}
