<?php
namespace Aws\Crypto;

use Aws\Crypto\Cipher\CipherMethod;
use Aws\Exception\CryptoException;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\AppendStream;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;

trait EncryptionTraitV3
{
    private static array $allowedOptions = [
        'Cipher' => true,
        'KeySize' => true,
        'Aad' => true,
    ];

    private static array $encryptClasses = [
        'gcm' => AesGcmEncryptingStream::class
    ];

    /**
     * Dependency to generate a CipherMethod from a set of inputs for loading
     * in to an AesEncryptingStream.
     *
     * @param string $cipherName Name of the cipher to generate for encrypting.
     * @param string $iv Base Initialization Vector for the cipher.
     * @param int $keySize Size of the encryption key, in bits, that will be
     *                     used.
     *
     * @return Cipher\CipherMethod
     *
     * @internal
     */
    abstract protected function buildCipherMethod(
        $cipherName,
        $iv,
        $keySize
    );

    /**
     * Builds an AesStreamInterface and populates encryption metadata into the
     * supplied envelope.
     *
     * @param StreamInterface $plaintext Plain-text data to be encrypted using the
     *                          materials, algorithm, and data provided.
     * @param AlgorithmSuite $algorithmSuite Algorithm Suite for use in encryption
     * @param array $options    Options for use in encryption, including cipher
     *                          options, and encryption context.
     * @param MaterialsProviderV3 $provider A provider to supply and encrypt
     *                                      materials used in encryption.
     * @param MetadataEnvelope $envelope A storage envelope for encryption
     *                                   metadata to be added to.
     *
     * @return AppendStream
     *
     * @throws \InvalidArgumentException Thrown when a value in $options['@CipherOptions']
     *                                   is not valid.
     *s
     * @internal
     */
    public function encrypt(
        StreamInterface $plaintext,
        AlgorithmSuite $algorithmSuite,
        array $options,
        MaterialsProviderV3 $provider,
        MetadataEnvelope $envelope
    ): AppendStream 
    {
        $options = array_change_key_case($options);
        $cipherOptions = array_intersect_key(
            $options['@cipheroptions'],
            self::$allowedOptions
        );
        //= ../specification/s3-encryption/encryption.md#content-encryption
        //= type=implication
        //# The client MUST validate that the length of the plaintext bytes does not exceed
        //# the algorithm suite's cipher's maximum content length in bytes.
        if (strlen($plaintext) > $algorithmSuite->getCipherMaxContentLengthBytes()) {
            throw new \InvalidArgumentException("The contentLength of the object you are attempting"
                . " to encrypt exceeds the maximum length allowed for GCM encryption.");
        }
        $cipherOptions['Cipher'] = strtolower($cipherOptions['Cipher']);

        if (!self::isSupportedCipher($cipherOptions['Cipher'])) {
            throw new \InvalidArgumentException('The cipher requested is not'
                . ' supported by the SDK.');
        }

        if (empty($cipherOptions['KeySize'])) {
            $cipherOptions['KeySize'] = 256;
        }

        if (!is_int($cipherOptions['KeySize'])) {
            throw new \InvalidArgumentException('The cipher "KeySize" must be'
                . ' an integer.');
        }

        if (!MaterialsProviderV3::isSupportedKeySize($cipherOptions['KeySize'])) {
            throw new \InvalidArgumentException('The cipher "KeySize" requested'
                . ' is not supported by AES (256).');
        }

        $encryptClass = self::$encryptClasses[$algorithmSuite->getCipherName()];
        $aesName = $encryptClass::getStaticAesName();
        $materialsDescription = $algorithmSuite->isKeyCommitting()
            ? ['aws:x-amz-cek-alg' => '115']
            : ['aws:x-amz-cek-alg' => $aesName];

        $keys = $provider->generateCek(
            $algorithmSuite->getDataKeyLengthBits(),
            $materialsDescription,
            $options
        );

        // Some providers modify materials description based on options
        if (isset($keys['UpdatedContext'])) {
            $materialsDescription = $keys['UpdatedContext'];
        }

        if ($algorithmSuite->isKeyCommitting()) {
            return $this->encryptCommitingStream(
                $plaintext,
                $algorithmSuite,
                $cipherOptions,
                $keys,
                $materialsDescription,
                $provider,
                $envelope
            );
        } else {
            return $this->encryptNonCommitingStream(
                $plaintext,
                $cipherOptions,
                $keys,
                $materialsDescription,
                $aesName,
                $provider,
                $envelope
            );
        }
    }

    private function encryptNonCommitingStream(
        StreamInterface $plaintext,
        array &$cipherOptions,
        array $keys,
        array $materialsDescription,
        string $aesName,
        MaterialsProviderV3 $provider,
        MetadataEnvelope $envelope
    ): AppendStream
    {
        //= ../specification/s3-encryption/encryption.md#content-encryption
        //# The generated IV or Message ID MUST be set or returned from the
        //# encryption process such that it can be included in the content metadata.
        //= ../specification/s3-encryption/encryption.md#content-encryption
        //# The client MUST generate an IV or Message ID using the length of the IV or Message ID defined in the algorithm suite.
        $cipherOptions['Iv'] = $provider->generateIv(
            $this->getCipherOpenSslName(
                $cipherOptions['Cipher'],
                $cipherOptions['KeySize']
            )
        );
        // Some providers modify materials description based on options
        if (isset($keys['UpdatedContext'])) {
            $materialsDescription = $keys['UpdatedContext'];
        }

        $encryptingStream = $this->getNonCommittingEncryptingStream(
            $plaintext,
            $keys['Plaintext'],
            $cipherOptions
        );

        // Populate envelope data
        //= ../specification/s3-encryption/data-format/content-metadata.md#determining-s3ec-object-status
        //# - If the metadata contains "x-amz-iv" and "x-amz-metadata-x-amz-key-v2" then the object MUST be considered as an S3EC-encrypted object using the V2 format.
        $envelope[MetadataEnvelope::CONTENT_KEY_V2_HEADER] = $keys['Ciphertext'];
        unset($keys);

        $envelope[MetadataEnvelope::IV_HEADER] =
            base64_encode($cipherOptions['Iv']);
        $envelope[MetadataEnvelope::KEY_WRAP_ALGORITHM_HEADER] =
            $provider->getWrapAlgorithmName();
        $envelope[MetadataEnvelope::CONTENT_CRYPTO_SCHEME_HEADER] = $aesName;
        $envelope[MetadataEnvelope::UNENCRYPTED_CONTENT_LENGTH_HEADER] =
            (string) strlen($plaintext);
        $envelope[MetadataEnvelope::MATERIALS_DESCRIPTION_HEADER] =
            json_encode($materialsDescription);
        if (!empty($cipherOptions['Tag'])) {
            $envelope[MetadataEnvelope::CRYPTO_TAG_LENGTH_HEADER] =
                (string) (strlen($cipherOptions['Tag']) * 8);
        }
        if (!MetadataEnvelope::isV2Envelope($envelope)) {
            throw new CryptoException("Error while writing metadata envelope."
                . " Not all required fields were set.");
        }
        
        return $encryptingStream;
    }

    private function encryptCommitingStream(
        StreamInterface $plaintext,
        AlgorithmSuite $algorithmSuite,
        array &$options,
        array $keys,
        array $materialsDescription,
        MaterialsProviderV3 $provider,
        MetadataEnvelope $envelope
    ): AppendStream
    {
        //= ../specification/s3-encryption/encryption.md#content-encryption
        //# The generated IV or Message ID MUST be set or returned from the
        //# encryption process such that it can be included in the content metadata.
        //= ../specification/s3-encryption/key-derivation.md#hkdf-operation
        //= type=implication
        //# When encrypting or decrypting with ALG_AES_256_GCM_HKDF_SHA512_COMMIT_KEY,
        //# the IV used in the AES-GCM content encryption/decryption
        //# MUST consist entirely of bytes with the value 0x01.
        //= ../specification/s3-encryption/key-derivation.md#hkdf-operation
        //= type=implication
        //# The IV's total length MUST match the IV length defined by the algorithm suite.
        $options['Iv'] = str_repeat("\1", $algorithmSuite->getIvLengthBytes());
        $messageId = $provider->generateIv(
            $this->getCipherOpenSslName(
                $algorithmSuite->getCipherName(),
                //= ../specification/s3-encryption/encryption.md#content-encryption
                //# The client MUST generate an IV or Message ID using the length of
                //# the IV or Message ID defined in the algorithm suite.
                $algorithmSuite->getKeyCommitmentSaltLengthBits()
            )
        );
        // Some providers modify materials description based on options
        if (isset($keys['UpdatedContext'])) {
            $materialsDescription["aws:x-amz-cek-alg"] = (string) $algorithmSuite->getId();
        }
        $commitingEncryptingArray = $this->getCommitingEncryptionStream(
            $plaintext,
            $keys['Plaintext'],
            $options,
            $messageId,
            $algorithmSuite
        );
        //= ../specification/s3-encryption/encryption.md#alg-aes-256-gcm-hkdf-sha512-commit-key
        //= type=implication
        //# The derived key commitment value MUST be set or returned from the encryption process
        //# such that it can be included in the content metadata.
        $commitmentKey = $commitingEncryptingArray[0];
        $encryptionStream = $commitingEncryptingArray[1];

        // Populate envelope data
        $envelope[MetadataEnvelope::ENCRYPTED_DATA_KEY_V3] = $keys['Ciphertext'];
        unset($keys);

        $envelope[MetadataEnvelope::CONTENT_CIPHER_V3] = $algorithmSuite->getId();
        // we are able to always set the encryption context in the envelope because PHP
        // only supports a KMS Material Provider.
        $envelope[MetadataEnvelope::ENCRYPTION_CONTEXT_V3] =
            json_encode($materialsDescription);
        //= ../specification/s3-encryption/data-format/content-metadata.md#v3-only
        //# The Encryption Context value MUST be used for wrapping algorithm `kms+context` or `12`.
        $envelope[MetadataEnvelope::ENCRYPTED_DATA_KEY_ALGORITHM_V3] = '12';
        $envelope[MetadataEnvelope::KEY_COMMITMENT_V3] = $commitmentKey;
        $envelope[MetadataEnvelope::MESSAGE_ID_V3] = base64_encode($messageId);
        if (!MetadataEnvelope::isV3Envelope($envelope)) {
            throw new CryptoException("Error while writing metadata envelope."
                . " Not all required fields were set.");
        }

        return $encryptionStream;
    }

    /**
     * Generates a stream that wraps the plaintext with the proper cipher and
     * uses the content encryption key (CEK) to encrypt the data when read.
     *
     * @param StreamInterface $plaintext Plain-text data to be encrypted using the
     *                          materials, algorithm, and data provided.
     * @param string $cek A content encryption key for use by the stream for
     *                    encrypting the plaintext data.
     * @param array $cipherOptions Options for use in determining the cipher to
     *                             be used for encrypting data.
     *
     * @return AppendStream returns an AppendStream
     *
     * @internal
     */
    protected function getNonCommittingEncryptingStream(
        StreamInterface $plaintext,
        string $cek,
        array &$cipherOptions
    ): AppendStream
    {
        // Only 'gcm' is supported for encryption currently
        switch ($cipherOptions['Cipher']) {
            //= ../specification/s3-encryption/encryption.md#content-encryption
            //= type=implication
            //# The S3EC MUST use the encryption algorithm configured during [client](./client.md) initialization.
            case 'gcm':
                $cipherOptions['TagLength'] = 16;
                $encryptClass = self::$encryptClasses['gcm'];
                //= ../specification/s3-encryption/encryption.md#alg-aes-256-gcm-iv12-tag16-no-kdf
                //= type=implication
                //# The client MUST initialize the cipher, or call an AES-GCM encryption API,
                //# with the plaintext data key, the generated IV, and the tag length defined
                //# in the Algorithm Suite when encrypting with ALG_AES_256_GCM_IV12_TAG16_NO_KDF.
                $cipherTextStream = new $encryptClass(
                    $plaintext,
                    $cek,
                    $cipherOptions['Iv'],
                    $cipherOptions['Aad'] = isset($cipherOptions['Aad'])
                    ? $cipherOptions['Aad']
                    : '',
                    $cipherOptions['TagLength'],
                    $cipherOptions['KeySize']
                );

                if (!empty($cipherOptions['Aad'])) {
                    trigger_error("'Aad' has been supplied for content encryption"
                        . " with " . $cipherTextStream->getAesName() . ". The"
                        . " PHP SDK encryption client can decrypt an object"
                        . " encrypted in this way, but other AWS SDKs may not be"
                        . " able to.", E_USER_WARNING);
                }

                $appendStream = new AppendStream([
                    $cipherTextStream->createStream()
                ]);
                //= ../specification/s3-encryption/encryption.md#alg-aes-256-gcm-iv12-tag16-no-kdf
                //= type=implication
                //# The client MUST append the GCM auth tag to the ciphertext if the underlying
                //# crypto provider does not do so automatically.
                $cipherOptions['Tag'] = $cipherTextStream->getTag();
                $appendStream->addStream(Psr7\Utils::streamFor($cipherOptions['Tag']));

                return $appendStream;
            default: 
                throw new CryptoException("Unsupported Cipher used for key commitment messages."
                    . " Found {$cipherOptions["Cipher"]}. Only 'gcm' is supported.");
        }
    }

    /**
     * Generates a stream that wraps the plaintext with the proper cipher and
     * uses the content encryption key (CEK) to encrypt the data when read.
     *
     * @param StreamInterface $plaintext Plain-text data to be encrypted using the
     *                          materials, algorithm, and data provided.
     * @param string $cek A content encryption key for use by the stream for
     *                    encrypting the plaintext data.
     * @param array $cipherOptions Options for use in determining the cipher to
     *                             be used for encrypting data.
     * @param string $messageId salt value used for key extraction step in the key
     *                          derivation process.
     * @param AlgorithmSuite $algorithmSuite options used for key commitment
     *
     * @return array returns an array with two elements as follows: [commitmentKey, AppendStream]
     *
     * @internal
     */
    protected function getCommitingEncryptionStream(
        StreamInterface $plaintext,
        string $dek,
        array &$cipherOptions,
        string $messageId,
        AlgorithmSuite $algorithmSuite
    ): array
    {
        $algorithmSuiteIdAsBytes = pack('n', $algorithmSuite->getId());
        //= ../specification/s3-encryption/key-derivation.md#hkdf-operation
        //= type=implication
        //# - The input info MUST be a concatenation of the algorithm suite ID as bytes followed by the string DERIVEKEY as UTF8 encoded bytes.
        $derivedEncryptionKeyInfo = $algorithmSuiteIdAsBytes . "DERIVEKEY";
        //= ../specification/s3-encryption/key-derivation.md#hkdf-operation
        //= type=implication
        //# - The input info MUST be a concatenation of the algorithm suite ID as bytes followed by the string COMMITKEY as UTF8 encoded bytes.
        $commitmentKeyInfo = $algorithmSuiteIdAsBytes . "COMMITKEY";
        //= ../specification/s3-encryption/key-derivation.md#hkdf-operation
        //= type=implication
        //# - The length of the input keying material MUST equal the key derivation
        //# input length specified by the algorithm suite commit key derivation setting.
        if (strlen($dek) !== $algorithmSuite->getDerivationInputKeyLengthBytes()) {
            throw new CryptoException("Input Key Material length exceeds "
                . "key derivation input length specified by the algorithm suite.");
        }
        //= ../specification/s3-encryption/encryption.md#alg-aes-256-gcm-hkdf-sha512-commit-key
        //= type=implication
        //# The client MUST use HKDF to derive the key commitment value and
        //# the derived encrypting key as described in [Key Derivation](key-derivation.md).
        $cek = hash_hkdf(
            //= ../specification/s3-encryption/key-derivation.md#hkdf-operation
            //= type=implication
            //# - The hash function MUST be specified by the algorithm suite commitment settings.
            $algorithmSuite->getHashingAlgorithm(),
            //= ../specification/s3-encryption/key-derivation.md#hkdf-operation
            //= type=implication
            //# - The input keying material MUST be the plaintext data key (PDK) generated by the key provider.
            //= ../specification/s3-encryption/key-derivation.md#hkdf-operation
            //= type=implication
            //# - The DEK input pseudorandom key MUST be the output from the extract step.
            $dek,
            //= ../specification/s3-encryption/key-derivation.md#hkdf-operation
            //= type=implication
            //# - The length of the output keying material MUST equal the encryption key length specified by the algorithm suite encryption settings.
            $algorithmSuite->getDerivationOutputKeyLengthBytes(),
            $derivedEncryptionKeyInfo,
            //= ../specification/s3-encryption/key-derivation.md#hkdf-operation
            //= type=implication
            //# - The salt MUST be the Message ID with the length defined in the algorithm suite.
            $messageId
        );
        $commitmentKey = hash_hkdf(
            $algorithmSuite->getHashingAlgorithm(),
            //= ../specification/s3-encryption/key-derivation.md#hkdf-operation
            //= type=implication
            //# - The CK input pseudorandom key MUST be the output from the extract step.
            $dek,
            //= ../specification/s3-encryption/key-derivation.md#hkdf-operation
            //= type=implication
            //# - The length of the output keying material MUST equal the commit key length specified by the supported algorithm suites.
            $algorithmSuite->getCommitmentOutputKeyLengthBytes(),
            $commitmentKeyInfo,
            $messageId
        );

        switch ($cipherOptions['Cipher']) {
            // Only 'gcm' is supported for encryption currently
            case 'gcm':
                $cipherOptions['TagLength'] = $algorithmSuite->getCipherTagLengthInBytes();
                $encryptClass = self::$encryptClasses[$algorithmSuite->getCipherName()];

                if (!empty($cipherOptions['Aad'])) {
                    trigger_error("'Aad' has been supplied for content encryption"
                        . " with " . $encryptClass::getStaticAesName() . ". The"
                        . " PHP SDK encryption client can decrypt an object"
                        . " encrypted in this way, but other AWS SDKs may not be"
                        . " able to.", E_USER_NOTICE);
                }
                $cipherOptions['Aad'] = isset($cipherOptions['Aad'])
                    ? $cipherOptions['Aad'] . $algorithmSuiteIdAsBytes
                    : $algorithmSuiteIdAsBytes;
                //= ../specification/s3-encryption/key-derivation.md#hkdf-operation
                //= type=implication
                //# The client MUST initialize the cipher, or call an AES-GCM encryption API,
                //# with the derived encryption key, an IV containing only bytes with the value 0x01,
                //# and the tag length defined in the Algorithm Suite when encrypting or
                //# decrypting with ALG_AES_256_GCM_HKDF_SHA512_COMMIT_KEY.
                $cipherTextStream = new $encryptClass(
                    $plaintext,
                    $cek,
                    $cipherOptions['Iv'],
                    $cipherOptions['Aad'],
                    $cipherOptions['TagLength'],
                    $cipherOptions['KeySize']
                );

                $appendStream = new AppendStream([
                    $cipherTextStream->createStream()
                ]);
                //= ../specification/s3-encryption/encryption.md#alg-aes-256-gcm-hkdf-sha512-commit-key
                //= type=implication
                //# The client MUST append the GCM auth tag to the ciphertext if the underlying
                //# crypto provider does not do so automatically.
                $cipherOptions['Tag'] = $cipherTextStream->getTag();
                $appendStream->addStream(Psr7\Utils::streamFor($cipherOptions['Tag']));
                
                return [base64_encode($commitmentKey), $appendStream];
            default: 
                throw new CryptoException("Unsupported Cipher used for content encryption");
        }
    }
}
