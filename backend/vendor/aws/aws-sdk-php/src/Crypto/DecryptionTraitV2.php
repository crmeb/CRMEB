<?php
namespace Aws\Crypto;

use Aws\Exception\CryptoException;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\LimitStream;
use Psr\Http\Message\StreamInterface;

trait DecryptionTraitV2
{
    /**
     * Dependency to reverse lookup the openssl_* cipher name from the AESName
     * in the MetadataEnvelope.
     *
     * @param $aesName
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
    abstract protected function buildCipherMethod($cipherName, $iv, $keySize);

    /**
     * Builds an AesStreamInterface using cipher options loaded from the
     * MetadataEnvelope and MaterialsProvider. Can decrypt data from both the
     * legacy and V2 encryption client workflows.
     *
     * @param string $cipherText Plain-text data to be encrypted using the
     *                           materials, algorithm, and data provided.
     * @param MaterialsProviderInterfaceV2 $provider A provider to supply and encrypt
     *                                             materials used in encryption.
     * @param MetadataEnvelope $envelope A storage envelope for encryption
     *                                   metadata to be read from.
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
        $cipherText,
        MaterialsProviderInterfaceV2 $provider,
        MetadataEnvelope $envelope,
        array $options = []
    ) 
    {
        $commitmentPolicy = $this->getKeyCommitmentPolicy($options);
        unset($options['@CommitmentPolicy']);
        if (isset($envelope[MetadataEnvelope::ENCRYPTED_DATA_KEY_V3])) {
            if ($commitmentPolicy !== "FORBID_ENCRYPT_ALLOW_DECRYPT") {
                throw new CryptoException(
                    "The requested item is encrypted"
                    . " with Key Commitment. The current policy {$commitmentPolicy}"
                    . " conflicts with the object metadata. Select an appropriate"
                    . " '@CommitmentPolicy' to decrypt this item."
                );
            }
            $algorithmSuite = AlgorithmSuite::ALG_AES_256_GCM_HKDF_SHA512_COMMIT_KEY;
            $options['@CipherOptions'] = $options['@CipherOptions'] ?? [];
            $options['@CipherOptions']['Iv'] = str_repeat("\1", 12);
            $options['@CipherOptions']['TagLength'] = $algorithmSuite->getCipherTagLengthInBytes();
            $materialDescription = $this->decodeMaterialsDescription(
                $envelope[MetadataEnvelope::ENCRYPTION_CONTEXT_V3]
            );


            $cek = $provider->decryptCek(
                base64_decode($envelope[MetadataEnvelope::ENCRYPTED_DATA_KEY_V3]),
                $materialDescription,
                $options
            );

            $options['@CipherOptions']['KeySize'] = strlen($cek) * 8;
            $options['@CipherOptions']['Cipher'] = $this->getCipherFromAesName(
                $this->numericalContenCipherToAesName($envelope)
            );
            $this->validateOptionsAndEnvelope($options, $envelope);

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

            $this->validateOptionsAndEnvelope($options, $envelope);

            $decryptionStream = $this->getDecryptingStream(
                $cipherText,
                $cek,
                $options['@CipherOptions']
            );
            unset($cek);

            return $decryptionStream;
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
                return ['aws:x-amz-cek-alg' => '115'];
            default:
                throw new CryptoException(
                    "Unknown Encrypted Data Key "
                    . "wrapping algorithm found: "
                    . "{$envelope[MetadataEnvelope::ENCRYPTED_DATA_KEY_ALGORITHM_V3]}"
                );
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

    private function getTagFromCiphertextStream(
        StreamInterface $cipherText,
        $tagLength
    ) 
    {
        $cipherTextSize = $cipherText->getSize();
        if ($cipherTextSize == null || $cipherTextSize <= 0) {
            throw new \RuntimeException('Cannot decrypt a stream of unknown'
                . ' size.');
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
    ) 
    {
        $cipherTextSize = $cipherText->getSize();
        if ($cipherTextSize == null || $cipherTextSize <= 0) {
            throw new \RuntimeException('Cannot decrypt a stream of unknown'
                . ' size.');
        }
        return new LimitStream(
            $cipherText,
            $cipherTextSize - $tagLength,
            0
        );
    }

    private function validateOptionsAndEnvelope($options, $envelope): void
    {
        $allowedCiphers = AbstractCryptoClientV2::$supportedCiphers;
        $allowedKeywraps = AbstractCryptoClientV2::$supportedKeyWraps;
        if ($options['@SecurityProfile'] == 'V2_AND_LEGACY') {
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
            . " client configuration @SecurityProfile=V2. Retry with"
            . " V2_AND_LEGACY enabled or reencrypt the object.");

        if (!in_array($options['@CipherOptions']['Cipher'], $allowedCiphers)) {
            if (in_array($options['@CipherOptions']['Cipher'], AbstractCryptoClient::$supportedCiphers)) {
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
    protected function getDecryptingStream(
        $cipherText,
        $cek,
        $cipherOptions
    ) 
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
                    $cipherOptions['Aad'] = $cipherOptions['Aad'] ?? '',
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
        $derivedEncryptionKey = hash_hkdf(
            $algorithmSuite->getHashingAlgorithm(),
            $cek,
            $algorithmSuite->getDerivationOutputKeyLengthBytes(),
            $derivedEncryptionKeyInfo,
            $messageId
        );
        $calculatedCommitmentKey = hash_hkdf(
            $algorithmSuite->getHashingAlgorithm(),
            $cek,
            $algorithmSuite->getCommitmentOutputKeyLengthBytes(),
            $commitmentKeyInfo,
            $messageId
        );

        if ($commitmentKey != $calculatedCommitmentKey) {
            throw new CryptoException("Calculated commitment key does "
                . "not match expected commitment key value ");
        }

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
