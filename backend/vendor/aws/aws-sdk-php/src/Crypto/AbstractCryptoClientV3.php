<?php

namespace Aws\Crypto;

use Aws\Crypto\Cipher\CipherMethod;
use GuzzleHttp\Psr7\AppendStream;
use GuzzleHttp\Psr7\Stream;

/**
 * @internal
 */
abstract class AbstractCryptoClientV3
{
    const SUPPORTED_SECURITY_PROFILES = ['V3', 'V3_AND_LEGACY'];

    const LEGACY_SECURITY_PROFILES = ['V3_AND_LEGACY'];

    const KEY_COMMITMENT_POLICIES = [
        'FORBID_ENCRYPT_ALLOW_DECRYPT',
        'REQUIRE_ENCRYPT_ALLOW_DECRYPT',
        'REQUIRE_ENCRYPT_REQUIRE_DECRYPT'
    ];

    public static array $supportedCiphers = ['gcm'];

    public static array $supportedKeyWraps = [
        KmsMaterialsProviderV3::WRAP_ALGORITHM_NAME
    ];

    /**
     * Returns if the passed policy name is supported for encryption by the SDK.
     *
     * @param string $policy The name of a key commitment policy to verify is registered.
     *
     * @return bool If the key commitment policy passed is in our supported list.
     */
    public static function isSupportedKeyCommitmentPolicy(string $policy): bool
    {
        return in_array($policy, AbstractCryptoClientV3::KEY_COMMITMENT_POLICIES, strict: true);
    }

    /**
     * Returns if the passed cipher name is supported for encryption by the SDK.
     *
     * @param string $cipherName The name of a cipher to verify is registered.
     *
     * @return bool If the cipher passed is in our supported list.
     */
    public static function isSupportedCipher(string $cipherName): bool
    {
        return in_array($cipherName, self::$supportedCiphers, true);
    }

    /**
     * Returns an identifier recognizable by `openssl_*` functions, such as
     * `aes-256-gcm`
     *
     * @param string $cipherName Name of the cipher being used for encrypting
     *                           or decrypting.
     * @param int $keySize Size of the encryption key, in bits, that will be
     *                     used.
     *
     * @return string
     */
    abstract protected function getCipherOpenSslName(
        $cipherName,
        $keySize
    );

    /**
     * Constructs a CipherMethod for the given name, initialized with the other
     * data passed for use in encrypting or decrypting.
     *
     * @param string $cipherName Name of the cipher to generate for encrypting.
     * @param string $iv Base Initialization Vector for the cipher.
     * @param int $keySize Size of the encryption key, in bits, that will be
     *                     used.
     *
     * @return CipherMethod
     *
     * @internal
     */
    abstract protected function buildCipherMethod(
        $cipherName,
        $iv,
        $keySize
    );

    /**
     * Performs a reverse lookup to get the openssl_* cipher name from the
     * AESName passed in from the MetadataEnvelope.
     *
     * @param string $aesName
     *
     * @return string
     *
     * @internal
     */
    abstract protected function getCipherFromAesName($aesName);

    /**
     * Dependency to provide an interface for building an encryption stream for
     * data given cipher details, metadata, and materials to do so.
     *
     * @param Stream $plaintext Plain-text data to be encrypted using the
     *                          materials, algorithm, and data provided.
     * @param AlgorithmSuite $algorithmSuite AlgorithmSuite for use in encryption.
     * @param array $options    Options for use in encryption, including cipher
     *                          options, and encryption context.
     * @param MaterialsProviderV3 $provider A provider to supply and encrypt
     *                                      materials used in encryption.
     * @param MetadataEnvelope $envelope A storage envelope for encryption
     *                                   metadata to be added to.
     *
     * @return AppendStream
     *
     * @internal
     */
    abstract public function encrypt(
        Stream $plaintext,
        AlgorithmSuite $algorithmSuite,
        array $options,
        MaterialsProviderV3 $provider,
        MetadataEnvelope $envelope
    ): AppendStream;

    /**
     * Dependency to provide an interface for building a decryption stream for
     * cipher text given metadata and materials to do so.
     *
     * @param string $cipherText Plain-text data to be decrypted using the
     *                           materials, algorithm, and data provided.
     * @param MaterialsProviderInterface $provider A provider to supply and encrypt
     *                                             materials used in encryption.
     * @param MetadataEnvelope $envelope A storage envelope for encryption
     *                                   metadata to be read from.
     * @param string $commitmentPolicy Commitment Policy to use for decrypting objects.
     * @param array $options Options used for decryption.
     *
     * @return AesStreamInterface
     *
     * @internal
     */
    abstract public function decrypt(
        string $cipherText,
        MaterialsProviderInterfaceV3 $provider,
        MetadataEnvelope $envelope,
        string $commitmentPolicy,
        array $options = []
    ): AesStreamInterface;
}
