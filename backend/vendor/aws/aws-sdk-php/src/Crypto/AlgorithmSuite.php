<?php
namespace Aws\Crypto;

use Aws\S3\Crypto\S3EncryptionClientV3;

enum AlgorithmSuite: int
{
    case ALG_AES_256_GCM_HKDF_SHA512_COMMIT_KEY = 0x0073;
    case ALG_AES_256_GCM_IV12_TAG16_NO_KDF = 0x0072;
    case ALG_AES_256_CBC_IV16_NO_KDF = 0x0070;

    public function getId(): int
    {
        return $this->value;
    }

    public function isLegacy(): bool
    {
        return match ($this) {
            self::ALG_AES_256_CBC_IV16_NO_KDF => true,
            default => false,
        };
    }

    public function isKeyCommitting(): bool
    {
        return match ($this) {
            self::ALG_AES_256_GCM_HKDF_SHA512_COMMIT_KEY => true,
            default => false,
        };
    }

    public function getDataKeyAlgorithm(): string
    {
        return match ($this) {
            self::ALG_AES_256_GCM_HKDF_SHA512_COMMIT_KEY,
            self::ALG_AES_256_GCM_IV12_TAG16_NO_KDF,
            self::ALG_AES_256_CBC_IV16_NO_KDF => "AES",
        };
    }

    public function getDataKeyLengthBits(): string
    {
        return match ($this) {
            self::ALG_AES_256_GCM_HKDF_SHA512_COMMIT_KEY,
            self::ALG_AES_256_GCM_IV12_TAG16_NO_KDF,
            self::ALG_AES_256_CBC_IV16_NO_KDF => "256",
        };
    }

    public function getCipherName(): string
    {
        return match ($this) {
            self::ALG_AES_256_GCM_HKDF_SHA512_COMMIT_KEY,
            self::ALG_AES_256_GCM_IV12_TAG16_NO_KDF => "gcm",
            self::ALG_AES_256_CBC_IV16_NO_KDF => "cbc",
        };
    }

    public function getCipherBlockSizeBits(): int
    {
        return match ($this) {
            self::ALG_AES_256_GCM_HKDF_SHA512_COMMIT_KEY,
            self::ALG_AES_256_GCM_IV12_TAG16_NO_KDF,
            self::ALG_AES_256_CBC_IV16_NO_KDF => 128,
        };
    }

    public function getCipherBlockSizeBytes(): int
    {
        return $this->getCipherBlockSizeBits() / 8;
    }

    public function getIvLengthBits(): int
    {
        return match ($this) {
            self::ALG_AES_256_GCM_HKDF_SHA512_COMMIT_KEY,
            self::ALG_AES_256_GCM_IV12_TAG16_NO_KDF => 96,
            self::ALG_AES_256_CBC_IV16_NO_KDF => 128,
        };
    }

    public function getIvLengthBytes(): int
    {
        return $this->getIvLengthBits() / 8;
    }

    public function getCipherTagLengthBits(): int
    {
        return match ($this) {
            self::ALG_AES_256_GCM_HKDF_SHA512_COMMIT_KEY,
            self::ALG_AES_256_GCM_IV12_TAG16_NO_KDF => 128,
            self::ALG_AES_256_CBC_IV16_NO_KDF => 0,
        };
    }

    public function getCipherTagLengthInBytes(): int
    {
        return $this->getCipherTagLengthBits() / 8;
    }

    public function getCipherMaxContentLengthBits(): int
    {
        return match ($this) {
            self::ALG_AES_256_GCM_HKDF_SHA512_COMMIT_KEY,
            self::ALG_AES_256_GCM_IV12_TAG16_NO_KDF => AlgorithmConstants::GCM_MAX_CONTENT_LENGTH_BITS,
            self::ALG_AES_256_CBC_IV16_NO_KDF => AlgorithmConstants::CBC_MAX_CONTENT_LENGTH_BYTES,
        };
    }

    public function getCipherMaxContentLengthBytes(): int
    {
        return $this->getCipherMaxContentLengthBits() / 8;
    }

    public function getHashingAlgorithm(): string
    {
        return match ($this) {
            self::ALG_AES_256_GCM_HKDF_SHA512_COMMIT_KEY => "sha512",
            default => "",
        };
    }

    public function getDerivationInputKeyLengthBits(): int
    {
        return match ($this) {
            self::ALG_AES_256_GCM_HKDF_SHA512_COMMIT_KEY => 256,
            default => 0,
        };
    }

    public function getDerivationInputKeyLengthBytes(): int
    {
        return $this->getDerivationInputKeyLengthBits() / 8;
    }

    public function getDerivationOutputKeyLengthBits(): int
    {
        return match ($this) {
            self::ALG_AES_256_GCM_HKDF_SHA512_COMMIT_KEY => 256,
            default => 0,
        };
    }

    public function getDerivationOutputKeyLengthBytes(): int
    {
        return $this->getDerivationOutputKeyLengthBits() / 8;
    }

    public function getCommitmentInputKeyLengthBits(): int
    {
        return match ($this) {
            self::ALG_AES_256_GCM_HKDF_SHA512_COMMIT_KEY => 256,
            default => 0,
        };
    }

    public function getCommitmentInputKeyLengthBytes(): int
    {
        return $this->getCommitmentInputKeyLengthBits() / 8;
    }

    public function getCommitmentOutputKeyLengthBits(): int
    {
        return match ($this) {
            self::ALG_AES_256_GCM_HKDF_SHA512_COMMIT_KEY => 224,
            default => 0,
        };
    }

    public function getCommitmentOutputKeyLengthBytes(): int
    {
        return $this->getCommitmentOutputKeyLengthBits() / 8;
    }

    public function getKeyCommitmentSaltLengthBits(): int
    {
        return match ($this) {
            self::ALG_AES_256_GCM_HKDF_SHA512_COMMIT_KEY => 224,
            default => 0,
        };
    }

    //= ../specification/s3-encryption/client.md#key-commitment
    //# The S3EC MUST validate the configured Encryption Algorithm against the provided key commitment policy.
    public static function validateCommitmentPolicyOnEncrypt(
        array $cipherOptions,
        string $keyCommitmentPolicy
    ): self {
        $cipherOptions['Cipher'] = strtolower($cipherOptions['Cipher']);
        //= ../specification/s3-encryption/client.md#encryption-algorithm
        //# The S3EC MUST validate that the configured encryption algorithm is not legacy.
        if (!S3EncryptionClientV3::isSupportedCipher($cipherOptions['Cipher'])) {
            //= ../specification/s3-encryption/client.md#encryption-algorithm
            //# If the configured encryption algorithm is legacy, then the S3EC MUST throw an exception.
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

        //= ../specification/s3-encryption/key-commitment.md#commitment-policy
        //# When the commitment policy is FORBID_ENCRYPT_ALLOW_DECRYPT, the S3EC MUST NOT encrypt using an algorithm suite which supports key commitment.
        if ($keyCommitmentPolicy === 'FORBID_ENCRYPT_ALLOW_DECRYPT') {
            return self::ALG_AES_256_GCM_IV12_TAG16_NO_KDF;
        } else {
            //= ../specification/s3-encryption/key-commitment.md#commitment-policy
            //# When the commitment policy is REQUIRE_ENCRYPT_ALLOW_DECRYPT, the S3EC MUST only encrypt using an algorithm suite which supports key commitment.

            //= ../specification/s3-encryption/key-commitment.md#commitment-policy
            //# When the commitment policy is REQUIRE_ENCRYPT_REQUIRE_DECRYPT, the S3EC MUST only encrypt using an algorithm suite which supports key commitment.
            return self::ALG_AES_256_GCM_HKDF_SHA512_COMMIT_KEY;
        }
    }
}
