<?php

namespace crmeb\services\easywechat\orderShipping;

use crmeb\exceptions\AdminException;

class Utility
{
    /**
     * @param int $padding
     */
    private static function paddingModeLimitedCheck(int $padding): void
    {
        if (!($padding === OPENSSL_PKCS1_OAEP_PADDING || $padding === OPENSSL_PKCS1_PADDING)) {
            throw new AdminException(sprintf("Doesn't supported padding mode(%d), here only support OPENSSL_PKCS1_OAEP_PADDING or OPENSSL_PKCS1_PADDING.", $padding));
        }
    }

    /**
     * 加密数据
     * @param string $plaintext
     * @param int $padding
     * @return string
     */
    public function encryptor(string $plaintext, int $padding = OPENSSL_PKCS1_OAEP_PADDING)
    {
        self::paddingModeLimitedCheck($padding);

        if (!openssl_public_encrypt($plaintext, $encrypted, $this->getPublicKey(), $padding)) {
            throw new AdminException('Encrypting the input $plaintext failed, please checking your $publicKey whether or nor correct.');
        }

        return base64_encode($encrypted);
    }

    public static function encryptTel($tel)
    {
        $new_tel = substr_replace($tel, '****', 3, 4);
        return $new_tel;
    }

}
