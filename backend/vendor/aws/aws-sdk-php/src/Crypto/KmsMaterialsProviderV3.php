<?php
namespace Aws\Crypto;

use Aws\Exception\CryptoException;
use Aws\Kms\KmsClient;

/**
 * Uses KMS to supply materials for encrypting and decrypting data. This
 * V2 implementation should be used with the V2 encryption clients (i.e.
 * S3EncryptionClientV2).
 */
class KmsMaterialsProviderV3 extends MaterialsProviderV3 implements MaterialsProviderInterfaceV3
{
    const WRAP_ALGORITHM_NAME = 'kms+context';
    private KmsClient $kmsClient;
    private ?string $kmsKeyId;

    /**
     * @param KmsClient $kmsClient A KMS Client for use encrypting and
     *                             decrypting keys.
     * @param string $kmsKeyId The private KMS key id to be used for encrypting
     *                         and decrypting keys.
     */
    public function __construct(
        KmsClient $kmsClient,
        ?string $kmsKeyId = null
    ) {
        $this->kmsClient = $kmsClient;
        $this->kmsKeyId = $kmsKeyId;
    }

    /**
     * @inheritDoc
     */
    public function getWrapAlgorithmName(): string
    {
        return self::WRAP_ALGORITHM_NAME;
    }

    /**
     * @inheritDoc
     */
    public function decryptCek(
        string $encryptedCek,
        array $materialDescription,
        array $options
    ): string 
    {
        $options = array_change_key_case($options);
        $encryptionContext = null;

        // no encryption context was provided we will use the one in the metadata
        if (!isset($options['@kmsencryptioncontext'])) {
            $encryptionContext = $materialDescription;
        } else {
            // encryption context was passed in, we have to make sure it is an array
            // and that the reserved keywords were not used
            if (!is_array($options['@kmsencryptioncontext'])) {
                throw new CryptoException("'When using @KmsMaterialsProviderV3, it"
                    . " must be an associative array (or empty array).");
            }

            if (isset($options['@kmsencryptioncontext']['aws:x-amz-cek-alg'])) {
                throw new CryptoException("Conflict in reserved @KmsEncryptionContext"
                    . " key aws:x-amz-cek-alg. This value is reserved for the S3"
                    . " Encryption Client and cannot be set by the user.");
            }

            if (isset($options['@kmsencryptioncontext']['kms_cmk_id'])) {
                throw new CryptoException("Conflict in reserved @KmsEncryptionContext"
                    . " key kms_cmk_id. This value is reserved for the S3"
                    . " Encryption Client and cannot be set by the user.");
            }
            //= specification/s3-encryption/materials/s3-kms-keyring.md#kms-context
            //= type=implication
            //# When decrypting using Kms+Context mode, the KmsKeyring MUST validate the provided (request) encryption context with the stored (materials) encryption context.
            // We are validating the encryption context to match S3EC V2 behavior
            // Refer to KMSMaterialsHandler in the V2 client for details
            $materialsDescriptionContextCopy = $materialDescription;
            unset($materialsDescriptionContextCopy["aws:x-amz-cek-alg"]);
            unset($materialsDescriptionContextCopy["kms_cmk_id"]);

            $requestEncryptionContext = $options['@kmsencryptioncontext'];
            //= specification/s3-encryption/materials/s3-kms-keyring.md#kms-context
            //= type=implication
            //# The stored encryption context with the two reserved keys removed MUST match the provided encryption context.
            if ($materialsDescriptionContextCopy !== $requestEncryptionContext) {
                //= specification/s3-encryption/materials/s3-kms-keyring.md#kms-context
                //= type=implication
                //# If the stored encryption context with the two reserved keys removed does not match the provided encryption context, the KmsKeyring MUST throw an exception.
                throw new CryptoException("Provided encryption context does not match information retrieved from S3");
            }
            $encryptionContext = $materialDescription;
        }
        $params = [
            'CiphertextBlob' => $encryptedCek,
            'EncryptionContext' => $encryptionContext
        ];

        if (empty($options['@kmsallowdecryptwithanycmk'])) {
            if (empty($this->kmsKeyId)) {
                throw new CryptoException('KMS CMK ID was not specified and the'
                    . ' operation is not opted-in to attempting to use any valid'
                    . ' CMK it discovers. Please specify a CMK ID, or explicitly'
                    . ' enable attempts to use any valid KMS CMK with the'
                    . ' @KmsAllowDecryptWithAnyCmk option.');
            }
            $params['KeyId'] = $this->kmsKeyId;
        }

        $result = $this->kmsClient->decrypt($params);

        return $result['Plaintext'];
    }

    /**
     * @inheritDoc
     */
    public function generateCek($keySize, $context, $options): array
    {
        if (empty($this->kmsKeyId)) {
            throw new CryptoException('A KMS key id is required for encryption'
                . ' with KMS keywrap. Use a KmsMaterialsProviderV2 that has been'
                . ' instantiated with a KMS key id.');
        }
        $options = array_change_key_case($options);
        if (!isset($options['@kmsencryptioncontext'])
            || !is_array($options['@kmsencryptioncontext'])
        ) {
            throw new CryptoException("'@KmsEncryptionContext' is a"
                . " required argument when using KmsMaterialsProviderV3, and"
                . " must be an associative array (or empty array).");
        }

        if (isset($options['@kmsencryptioncontext']['aws:x-amz-cek-alg'])) {
            throw new CryptoException("Conflict in reserved @KmsEncryptionContext"
                . " key aws:x-amz-cek-alg. This value is reserved for the S3"
                . " Encryption Client and cannot be set by the user.");
        }

        if (isset($options['@kmsencryptioncontext']['kms_cmk_id'])) {
            throw new CryptoException("Conflict in reserved @KmsEncryptionContext"
                . " key kms_cmk_id. This value is reserved for the S3"
                . " Encryption Client and cannot be set by the user.");
        }
        $context = array_merge($options['@kmsencryptioncontext'], $context);
        $result = $this->kmsClient->generateDataKey([
            'KeyId' => $this->kmsKeyId,
            'KeySpec' => "AES_{$keySize}",
            'EncryptionContext' => $context
        ]);

        return [
            'Plaintext' => $result['Plaintext'],
            'Ciphertext' => base64_encode($result['CiphertextBlob']),
            'UpdatedContext' => $context
        ];
    }
}
