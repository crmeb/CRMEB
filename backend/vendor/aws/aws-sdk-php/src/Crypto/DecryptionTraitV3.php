<?php

namespace Aws\Crypto;

use Aws\Crypto\Cipher\CipherMethod;
use Aws\Exception\CryptoException;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\LimitStream;
use PHPUnit\Framework\Constraint\IsEmpty;
use Psr\Http\Message\StreamInterface;

trait DecryptionTraitV3
{
    /**
     * Dependency to reverse lookup the openssl_* cipher name from the AESName
     * in the MetadataEnvelope.
     *
     * @param string $aesName
     *
     * @return string
     *
     * @internal
     */
    abstract protected function getCipherFromAesName($aesName);

    /**
     * Dependency to generate a CipherMethod from a set of inputs for loading
     * in to an AesDecryptingStream.
     *
     * @param string $cipherName Name of the cipher to generate for decrypting.
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
     * Builds an AesStreamInterface using cipher options loaded from the
     * MetadataEnvelope and MaterialsProvider. Can decrypt data from both the
     * legacy and V3 encryption client workflows.
     *
     * @param string $cipherText Plain-text data to be encrypted using the
     *                           materials, algorithm, and data provided.
     * @param MaterialsProviderInterfaceV3 $provider A provider to supply and encrypt
     *                                             materials used in encryption.
     * @param MetadataEnvelope $envelope A storage envelope for encryption
     *                                   metadata to be read from.
     * @param string $commitmentPolicy Commitment Policy to use for decrypting objects.
     * @param array $options Options used for decryption.
     *
     * @return AesStreamInterface
     *
     * @throws \InvalidArgumentException Thrown when a value in $cipherOptions
     *                                   is not valid.
     *
     * @internal
     */
    public function decrypt(
        string $cipherText,
        MaterialsProviderInterfaceV3 $provider,
        MetadataEnvelope $envelope,
        string $commitmentPolicy,
        array $options = []
    ): AesStreamInterface 
    {
        if (isset($envelope[MetadataEnvelope::ENCRYPTED_DATA_KEY_V3])) {
            $this->checkEnvelopeForExclusiveMapKeys(
                $envelope,
                MetadataEnvelope::getV2Fields(),
                "Expected V3 only fields but found V2 fields in header metadata."
            );
            // PHP only supports one commiting algorithm suite
            $algorithmSuite = AlgorithmSuite::ALG_AES_256_GCM_HKDF_SHA512_COMMIT_KEY;
            $options['@CipherOptions'] = $options['@CipherOptions'] ?? [];
            $options['@CipherOptions']['Iv'] = str_repeat("\1", 12);
            $options['@CipherOptions']['TagLength'] = $algorithmSuite->getCipherTagLengthInBytes();

            //= ../specification/s3-encryption/data-format/content-metadata.md#v3-only
            //= type=implication
            //# - The wrapping algorithm value "12" MUST be translated to kms+context upon retrieval, and vice versa on write.
            $materialDescription = $this->buildMaterialDescription($envelope);

            $cek = $provider->decryptCek(
                base64_decode($envelope[MetadataEnvelope::ENCRYPTED_DATA_KEY_V3]),
                $materialDescription,
                $options
            );

            $options['@CipherOptions']['KeySize'] = strlen($cek) * 8;
            $options['@CipherOptions']['Cipher'] = $this->getCipherFromAesName(
                $this->numericalContenCipherToAesName($envelope)
            );
            $this->validateOptionsAndEnvelope($options, $envelope, $commitmentPolicy);

            $messageId = base64_decode($envelope[MetadataEnvelope::MESSAGE_ID_V3]);
            $commitmentKey = base64_decode($envelope[MetadataEnvelope::KEY_COMMITMENT_V3]);
            
            if (strlen($messageId) !== ($algorithmSuite->getKeyCommitmentSaltLengthBits()) / 8) {
                throw new CryptoException("Invalid MessageId length found in object envelope.");
            }

            if (strlen($commitmentKey) !== $algorithmSuite->getCommitmentOutputKeyLengthBytes()) {
                throw new CryptoException("Invalid Commitment Key length found in object envelope.");
            }

            $decryptionStream = $this->getCommitingDecryptingStream(
                $cipherText,
                $cek,
                $options['@CipherOptions'],
                $messageId,
                $commitmentKey,
                $algorithmSuite
            );
            unset($cek);

            return $decryptionStream;
        } else {
            //= ../specification/s3-encryption/key-commitment.md#commitment-policy
            //# When the commitment policy is REQUIRE_ENCRYPT_REQUIRE_DECRYPT,
            //# the S3EC MUST NOT allow decryption using algorithm suites which do not support key commitment.
            if ($commitmentPolicy == "REQUIRE_ENCRYPT_REQUIRE_DECRYPT") {
                //= ../specification/s3-encryption/client.md#key-commitment
                //# If the configured Encryption Algorithm is incompatible with
                //# the key commitment policy, then it MUST throw an exception.
                throw new CryptoException("Message is encrypted with a "
                    . "non commiting algorithm but commitment policy is set "
                    . "to {$commitmentPolicy}. Select a valid commitment "
                    . "policy to decrypt this object. ");
            }
            $this->checkEnvelopeForExclusiveMapKeys(
                $envelope,
                MetadataEnvelope::getV3Fields(),
                "Expected V2 only fields but found V3 fields in header metadata."
            );
            $options['@CipherOptions'] = $options['@CipherOptions'] ?? [];
            $options['@CipherOptions']['Iv'] = base64_decode(
                $envelope[MetadataEnvelope::IV_HEADER]
            );

            $options['@CipherOptions']['TagLength'] =
                $envelope[MetadataEnvelope::CRYPTO_TAG_LENGTH_HEADER] / 8;

            $cek = $provider->decryptCek(
                base64_decode(
                    $envelope[MetadataEnvelope::CONTENT_KEY_V2_HEADER]
                ),
                $this->decodeMaterialsDescription(
                    $envelope[MetadataEnvelope::MATERIALS_DESCRIPTION_HEADER]
                ),
                $options
            );
            $options['@CipherOptions']['KeySize'] = strlen($cek) * 8;
            $options['@CipherOptions']['Cipher'] = $this->getCipherFromAesName(
                $envelope[MetadataEnvelope::CONTENT_CRYPTO_SCHEME_HEADER]
            );

            //= ../specification/s3-encryption/decryption.md#key-commitment
            //= type=implication
            //# The S3EC MUST validate the algorithm suite used for decryption
            //# against the key commitment policy before attempting to decrypt
            //# the content ciphertext.
            $this->validateOptionsAndEnvelope($options, $envelope, $commitmentPolicy);

            $decryptionStream = $this->getNonCommitingDecryptingStream(
                $cipherText,
                $cek,
                $options['@CipherOptions']
            );
            unset($cek);

            return $decryptionStream;
        }
    }

    private function checkEnvelopeForExclusiveMapKeys(
        MetadataEnvelope $envelope,
        array $exclusiveKeys,
        string $errorMessage
    ): void
    {
        foreach ($exclusiveKeys as $exclusiveKey) {
            //= ../specification/s3-encryption/data-format/content-metadata.md#determining-s3ec-object-status
            //# If there are multiple mapkeys which are meant to be exclusive, such as "x-amz-key", "x-amz-key-v2", and "x-amz-3" then the S3EC SHOULD throw an exception.
            if (isset($envelope[$exclusiveKey])) {
                throw new CryptoException($errorMessage);
            }
        }
    }

    private function numericalContenCipherToAesName(
        MetadataEnvelope $envelope
    ): string
    {
        switch ($envelope[MetadataEnvelope::CONTENT_CIPHER_V3]) {
            case 115:
                return 'AES/GCM/NoPadding';
            default:
                throw new CryptoException(
                    "Unknown Encrypted Data Key "
                    . "wrapping algorithm found: "
                    . "{$envelope[MetadataEnvelope::ENCRYPTED_DATA_KEY_ALGORITHM_V3]}"
                );
        }

    }

    // Decodes the material description, rejecting a malformed value.
    private function decodeMaterialsDescription($materialsDescription): array
    {
        if (!is_string($materialsDescription)) {
            throw new CryptoException('Unable to decode the material description.');
        }

        $decoded = json_decode($materialsDescription, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new CryptoException(
                'Unable to decode the material description: ' . json_last_error_msg()
            );
        }
        if (!is_array($decoded)) {
            throw new CryptoException('Unable to decode the material description.');
        }

        return $decoded;
    }

    private function buildMaterialDescription(
        MetadataEnvelope $envelope
    ): array
    {
        switch ($envelope[MetadataEnvelope::ENCRYPTED_DATA_KEY_ALGORITHM_V3]) {
            case 12:
                return $this->decodeMaterialsDescription(
                    $envelope[MetadataEnvelope::ENCRYPTION_CONTEXT_V3]
                );
            default:
                throw new CryptoException(
                    "Unknown Encrypted Data Key "
                    . "wrapping algorithm found: "
                    . "{$envelope[MetadataEnvelope::ENCRYPTED_DATA_KEY_ALGORITHM_V3]}"
                );
        }
    }

    private function getTagFromCiphertextStream(
        StreamInterface $cipherText,
        $tagLength
    ): string
    {
        $cipherTextSize = $cipherText->getSize();
        if ($cipherTextSize == null || $cipherTextSize <= 0) {
            throw new \RuntimeException('Cannot decrypt a stream of unknown size');
        }

        return (string) new LimitStream(
            $cipherText,
            $tagLength,
            $cipherTextSize - $tagLength
        );
    }

    private function getStrippedCiphertextStream(
        StreamInterface $cipherText,
        $tagLength
    ): LimitStream 
    {
        $cipherTextSize = $cipherText->getSize();
        if ($cipherTextSize == null || $cipherTextSize <= 0) {
            throw new \RuntimeException('Cannot decrypt a stream of unknown size');
        }

        return new LimitStream(
            $cipherText,
            $cipherTextSize - $tagLength,
            0
        );
    }

    private function validateOptionsAndEnvelope(
        array $options,
        MetadataEnvelope $envelope,
        string $commitmentPolicy
    ): void 
    {
        //= ../specification/s3-encryption/key-commitment.md#commitment-policy
        //# When the commitment policy is REQUIRE_ENCRYPT_ALLOW_DECRYPT,
        //# the S3EC MUST allow decryption using algorithm suites which do not support key commitment.

        //= ../specification/s3-encryption/key-commitment.md#commitment-policy
        //# When the commitment policy is FORBID_ENCRYPT_ALLOW_DECRYPT,
        //# the S3EC MUST allow decryption using algorithm suites which do not support key commitment.

        $allowedCiphers = AbstractCryptoClientV3::$supportedCiphers;
        $allowedKeywraps = AbstractCryptoClientV3::$supportedKeyWraps;
        //= ../specification/s3-encryption/client.md#enable-legacy-wrapping-algorithms
        //# When enabled, the S3EC MUST be able to decrypt objects encrypted with all
        //# supported wrapping algorithms (both legacy and fully supported).
        if ($options['@SecurityProfile'] == 'V3_AND_LEGACY') {
            $allowedCiphers = array_unique(array_merge(
                $allowedCiphers,
                AbstractCryptoClient::$supportedCiphers
            ));
            $allowedKeywraps = array_unique(array_merge(
                $allowedKeywraps,
                AbstractCryptoClient::$supportedKeyWraps
            ));
        }

        $v1SchemaException = new CryptoException("The requested object is encrypted"
            . " with V1 encryption schemas that have been disabled by"
            . " client configuration @SecurityProfile=V3. Retry with"
            . " V3_AND_LEGACY enabled or reencrypt the object.");

        if (!in_array($options['@CipherOptions']['Cipher'], $allowedCiphers)) {
            //= ../specification/s3-encryption/client.md#enable-legacy-wrapping-algorithms
            //= type=implication
            //# When disabled, the S3EC MUST NOT decrypt objects encrypted using legacy wrapping algorithms;
            //# it MUST throw an exception when attempting to decrypt an object encrypted with a legacy wrapping algorithm.
            
            //= ../specification/s3-encryption/decryption.md#legacy-decryption
            //# The S3EC MUST NOT decrypt objects encrypted using legacy unauthenticated algorithm suites unless specifically configured to do so.
            if (in_array($options['@CipherOptions']['Cipher'], AbstractCryptoClient::$supportedCiphers)) {
                //= ../specification/s3-encryption/decryption.md#legacy-decryption
                //# If the S3EC is not configured to enable legacy unauthenticated content decryption, the client MUST throw an exception when attempting to decrypt an object encrypted with a legacy unauthenticated algorithm suite.
                throw $v1SchemaException;
            }

            throw new CryptoException("The requested object is encrypted with"
                . " the cipher '{$options['@CipherOptions']['Cipher']}', which is not"
                . " supported for decryption with the selected security profile."
                . " This profile allows decryption with: "
                . implode(", ", $allowedCiphers));
        }

        if (isset($envelope[MetadataEnvelope::ENCRYPTED_DATA_KEY_V3])) {
            if ($envelope[MetadataEnvelope::ENCRYPTED_DATA_KEY_ALGORITHM_V3] !== '12') {
                throw new CryptoException("The requested object is encrypted with"
                    . " the keywrap schema '{$envelope[MetadataEnvelope::ENCRYPTED_DATA_KEY_ALGORITHM_V3]}',"
                    . " which is not supported for decryption with the current security"
                    . " profile.");
            }
        } else {
            if (!in_array($envelope[MetadataEnvelope::KEY_WRAP_ALGORITHM_HEADER], $allowedKeywraps)) {
                if (in_array($envelope[MetadataEnvelope::KEY_WRAP_ALGORITHM_HEADER], AbstractCryptoClient::$supportedKeyWraps)) {
                    throw $v1SchemaException;
                }

                throw new CryptoException("The requested object is encrypted with"
                    . " the keywrap schema '{$envelope[MetadataEnvelope::KEY_WRAP_ALGORITHM_HEADER]}',"
                    . " which is not supported for decryption with the current security"
                    . " profile.");
            }
            $matdesc = json_decode(
                $envelope[MetadataEnvelope::MATERIALS_DESCRIPTION_HEADER],
                true
            );
            if (isset($matdesc['aws:x-amz-cek-alg'])
                && ($envelope[MetadataEnvelope::CONTENT_CRYPTO_SCHEME_HEADER]
                    !== $matdesc['aws:x-amz-cek-alg'])
            ) {
                throw new CryptoException("There is a mismatch in specified content"
                    . " encryption algrithm between the materials description value"
                    . " and the metadata envelope value: {$matdesc['aws:x-amz-cek-alg']}"
                    . " vs. {$envelope[MetadataEnvelope::CONTENT_CRYPTO_SCHEME_HEADER]}.");
            }
        }
        //= ../specification/s3-encryption/data-format/content-metadata.md#determining-s3ec-object-status
        //= type=implication
        //# - If the metadata contains "x-amz-3" and "x-amz-d" and "x-amz-i" then the object MUST be considered an S3EC-encrypted object using the V3 format.
        if (!MetadataEnvelope::isV3Envelope($envelope)
            && $commitmentPolicy == "REQUIRE_ENCRYPT_REQUIRE_DECRYPT"
        ) {
            //= ../specification/s3-encryption/decryption.md#key-commitment
            //# If the commitment policy requires decryption using a committing algorithm suite
            //# and the algorithm suite associated with the object does not support key commitment,
            //# then the S3EC MUST throw an exception.
            throw new CryptoException("There is a mismatch in specified"
                . "commitment policy value {$commitmentPolicy} and"
                . "Metadata Envelope found in object.");
        }

    }

    /**
     * Generates a stream that wraps the cipher text with the proper cipher and
     * uses the content encryption key (CEK) to decrypt the data when read.
     *
     * @param string $cipherText Plain-text data to be encrypted using the
     *                           materials, algorithm, and data provided.
     * @param string $cek A content encryption key for use by the stream for
     *                    encrypting the plaintext data.
     * @param array $cipherOptions Options for use in determining the cipher to
     *                             be used for encrypting data.
     *
     * @return AesStreamInterface
     *
     * @internal
     */
    protected function getNonCommitingDecryptingStream(
        string $cipherText,
        string $cek,
        array $cipherOptions
    ): AesStreamInterface
    {
        $cipherTextStream = Psr7\Utils::streamFor($cipherText);
        switch ($cipherOptions['Cipher']) {
            case 'gcm':
                $cipherOptions['Tag'] = $this->getTagFromCiphertextStream(
                    $cipherTextStream,
                    $cipherOptions['TagLength']
                );

                return new AesGcmDecryptingStream(
                    $this->getStrippedCiphertextStream(
                        $cipherTextStream,
                        $cipherOptions['TagLength']
                    ),
                    $cek,
                    $cipherOptions['Iv'],
                    $cipherOptions['Tag'],
                    $cipherOptions['Aad'] = isset($cipherOptions['Aad'])
                    ? $cipherOptions['Aad']
                    : '',
                    $cipherOptions['TagLength'] ?: null,
                    $cipherOptions['KeySize']
                );
            default:
                $cipherMethod = $this->buildCipherMethod(
                    $cipherOptions['Cipher'],
                    $cipherOptions['Iv'],
                    $cipherOptions['KeySize']
                );

                return new AesDecryptingStream(
                    $cipherTextStream,
                    $cek,
                    $cipherMethod
                );
        }
    }

    /**
     * Generates a stream that wraps the cipher text with the proper cipher and
     * uses the content encryption key (CEK) to derive both a derived content encryption key
     * and a commitment key to decrypt the data when read.
     *
     * @param string $cipherText Plain-text data to be encrypted using the
     *                           materials, algorithm, and data provided.
     * @param string $cek A content encryption key for use by the stream for
     *                    encrypting the plaintext data.
     * @param array $cipherOptions Options for use in determining the cipher to
     *                             be used for encrypting data.
     * @param string $messageId a string value used to calculate both a commitment
     *                          key and derived content encryption key
     * @param string $commitmentKey a string value to compare with the calculated commitment
     *                              key value, if the values don't match an exception is raised.
     *
     * @return AesStreamInterface | CryptoException
     *
     * @internal
     */
    protected function getCommitingDecryptingStream(
        string $cipherText,
        string $cek,
        array $cipherOptions,
        string $messageId,
        string $commitmentKey,
        AlgorithmSuite $algorithmSuite
    ): AesStreamInterface|CryptoException
    {
        $algorithmSuiteIdAsBytes = pack('n', $algorithmSuite->getId());
        $derivedEncryptionKeyInfo = $algorithmSuiteIdAsBytes . "DERIVEKEY";
        $commitmentKeyInfo = $algorithmSuiteIdAsBytes . "COMMITKEY";
        $calculatedCommitmentKey = hash_hkdf(
            $algorithmSuite->getHashingAlgorithm(),
            $cek,
            $algorithmSuite->getCommitmentOutputKeyLengthBytes(),
            $commitmentKeyInfo,
            $messageId
        );
        //= ../specification/s3-encryption/decryption.md#decrypting-with-commitment
        //= type=implication
        //# When using an algorithm suite which supports key commitment, the client MUST verify that the [derived key commitment](./key-derivation.md#hkdf-operation) contains the same bytes as the stored key commitment retrieved from the stored object's metadata.
        if (
            //= ../specification/s3-encryption/decryption.md#decrypting-with-commitment
            //= type=implication
            //# When using an algorithm suite which supports key commitment,
            //# the verification of the derived key commitment value MUST be done in constant time.
            !hash_equals($commitmentKey, $calculatedCommitmentKey)
        ) {
            //= ../specification/s3-encryption/decryption.md#decrypting-with-commitment
            //= type=implication
            //# When using an algorithm suite which supports key commitment, the client MUST
            //# throw an exception when the derived key commitment value
            //# and stored key commitment value do not match.
            throw new CryptoException("Calculated commitment key does "
                . "not match expected commitment key value ");
        }
        //= ../specification/s3-encryption/decryption.md#decrypting-with-commitment
        //= type=implication
        //# When using an algorithm suite which supports key commitment,
        //# the client MUST verify the key commitment values match before
        //# deriving the [derived encryption key](./key-derivation.md#hkdf-operation).
        $derivedEncryptionKey = hash_hkdf(
            $algorithmSuite->getHashingAlgorithm(),
            $cek,
            $algorithmSuite->getDerivationOutputKeyLengthBytes(),
            $derivedEncryptionKeyInfo,
            $messageId
        );

        $cipherTextStream = Psr7\Utils::streamFor($cipherText);
        switch ($cipherOptions['Cipher']) {
            case 'gcm':
                $cipherOptions['Tag'] = $this->getTagFromCiphertextStream(
                    $cipherTextStream,
                    $cipherOptions['TagLength']
                );
                $cipherOptions['Aad'] = isset($cipherOptions['Aad'])
                    ? $cipherOptions['Aad'] . $algorithmSuiteIdAsBytes
                    : $algorithmSuiteIdAsBytes;

                return new AesGcmDecryptingStream(
                    $this->getStrippedCiphertextStream(
                        $cipherTextStream,
                        $cipherOptions['TagLength']
                    ),
                    $derivedEncryptionKey,
                    $cipherOptions['Iv'],
                    $cipherOptions['Tag'],
                    $cipherOptions['Aad'],
                    $cipherOptions['TagLength'] ?: null,
                    $cipherOptions['KeySize']
                );
            default: 
                throw new CryptoException("Unsupported Cipher used for key commitment messages."
                    . " Found {$cipherOptions["Cipher"]}. Only 'gcm' is supported.");
        }
    }
}
