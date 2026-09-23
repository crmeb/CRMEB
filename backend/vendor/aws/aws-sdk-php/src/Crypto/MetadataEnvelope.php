<?php
namespace Aws\Crypto;

use Aws\HasDataTrait;
use \ArrayAccess;
use \IteratorAggregate;
use \InvalidArgumentException;
use \JsonSerializable;

/**
 * Stores encryption metadata for reading and writing.
 *
 * @internal
 */
class MetadataEnvelope implements ArrayAccess, IteratorAggregate, JsonSerializable
{
    use HasDataTrait;

    //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
    //# The "x-amz-" prefix denotes that the metadata is owned by an Amazon product
    //# and MUST be prepended to all S3EC metadata mapkeys.

    //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
    //# - The mapkey "x-amz-key-v2" MUST be present for V2 format objects.
    const CONTENT_KEY_V2_HEADER = 'x-amz-key-v2';
    //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
    //= type=implication
    //# - This mapkey ("x-amz-3") SHOULD be represented by a constant named "ENCRYPTED_DATA_KEY_V3" or similar in the implementation code.
    const ENCRYPTED_DATA_KEY_V3 = 'x-amz-3';
    //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
    //# - The mapkey "x-amz-iv" MUST be present for V1 format objects.
    const IV_HEADER = 'x-amz-iv';
    //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
    //# - The mapkey "x-amz-matdesc" MUST be present for V1 format objects.
    //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
    //# - The mapkey "x-amz-matdesc" MUST be present for V2 format objects.
    //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
    //# - The mapkey "x-amz-iv" MUST be present for V2 format objects.
    const MATERIALS_DESCRIPTION_HEADER = 'x-amz-matdesc';
    //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
    //= type=implication
    //# - This mapkey ("x-amz-m") SHOULD be represented by a constant named "MAT_DESC_V3" or similar in the implementation code.
    const MAT_DESC_V3 = 'x-amz-m';
    //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
    //# - The mapkey "x-amz-wrap-alg" MUST be present for V2 format objects.
    const KEY_WRAP_ALGORITHM_HEADER = 'x-amz-wrap-alg';
    //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
    //= type=implication
    //# - This mapkey ("x-amz-w") SHOULD be represented by a constant named "ENCRYPTED_DATA_KEY_ALGORITHM_V3" or similar in the implementation code.
    const ENCRYPTED_DATA_KEY_ALGORITHM_V3 = 'x-amz-w';
    //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
    //# - The mapkey "x-amz-cek-alg" MUST be present for V2 format objects.
    const CONTENT_CRYPTO_SCHEME_HEADER = 'x-amz-cek-alg';
    //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
    //= type=implication
    //# - This mapkey ("x-amz-c") SHOULD be represented by a constant named "CONTENT_CIPHER_V3" or similar in the implementation code.
    const CONTENT_CIPHER_V3 = 'x-amz-c';
    //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
    //# - The mapkey "x-amz-tag-len" MUST be present for V2 format objects.
    const CRYPTO_TAG_LENGTH_HEADER = 'x-amz-tag-len';
    //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
    //# - The mapkey "x-amz-unencrypted-content-length" SHOULD be present for V1 format objects.
    const UNENCRYPTED_CONTENT_LENGTH_HEADER = 'x-amz-unencrypted-content-length';
    //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
    //= type=implication
    //# - This mapkey ("x-amz-t") SHOULD be represented by a constant named "ENCRYPTION_CONTEXT_V3" or similar in the implementation code.
    const ENCRYPTION_CONTEXT_V3 = 'x-amz-t';
    //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
    //= type=implication
    //# - This mapkey ("x-amz-d") SHOULD be represented by a constant named "KEY_COMMITMENT_V3" or similar in the implementation code.
    const KEY_COMMITMENT_V3 = 'x-amz-d';
    //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
    //= type=implication
    //# - This mapkey ("x-amz-i") SHOULD be represented by a constant named "MESSAGE_ID_V3" or similar in the implementation code.
    const MESSAGE_ID_V3 = 'x-amz-i';

    private static $constants = [];

    public static function getConstantValues()
    {
        if (empty(self::$constants)) {
            $reflection = new \ReflectionClass(static::class);
            //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
            //# The "x-amz-meta-" prefix is automatically added by the S3 server and MUST NOT be included in implementation code.
            foreach (array_values($reflection->getConstants()) as $constant) {
                self::$constants[$constant] = true;
            }
        }

        return array_keys(self::$constants);
    }

    /**
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($name, $value)
    {
        $constants = self::getConstantValues();
        //= ../specification/s3-encryption/data-format/content-metadata.md#determining-s3ec-object-status
        //# In general, if there is any deviation from the above format, with the exception of additional unrelated mapkeys, then the S3EC SHOULD throw an exception.
        if (is_null($name) || !in_array($name, $constants)) {
            throw new InvalidArgumentException('MetadataEnvelope fields must'
                . ' must match a predefined offset; use the header constants.');
        }

        $this->data[$name] = $value;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->data;
    }
    
    public static function isV2Envelope(MetadataEnvelope $envelope): bool
    {
        if (!isset($envelope[MetadataEnvelope::CONTENT_KEY_V2_HEADER])
            || !isset($envelope[MetadataEnvelope::MATERIALS_DESCRIPTION_HEADER])
            || !isset($envelope[MetadataEnvelope::IV_HEADER])
            || !isset($envelope[MetadataEnvelope::KEY_WRAP_ALGORITHM_HEADER])
            || !isset($envelope[MetadataEnvelope::CONTENT_CRYPTO_SCHEME_HEADER])
            || !isset($envelope[MetadataEnvelope::CRYPTO_TAG_LENGTH_HEADER])
        ) {
            return false;
        }
        return true;
    }
    
    public static function isV1Envelope(MetadataEnvelope $envelope): bool
    {
        if (!isset($envelope[MetadataEnvelope::CONTENT_KEY_V2_HEADER])
            //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
            //# - The mapkey "x-amz-matdesc" MUST be present for V1 format objects.
            || !isset($envelope[MetadataEnvelope::MATERIALS_DESCRIPTION_HEADER])
            //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
            //# - The mapkey "x-amz-iv" MUST be present for V1 format objects.
            || !isset($envelope[MetadataEnvelope::IV_HEADER])
            || !isset($envelope[MetadataEnvelope::KEY_WRAP_ALGORITHM_HEADER])
            || !isset($envelope[MetadataEnvelope::CONTENT_CRYPTO_SCHEME_HEADER])
            //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
            //# - The mapkey "x-amz-unencrypted-content-length" SHOULD be present for V1 format objects.
            || !isset($envelope[MetadataEnvelope::UNENCRYPTED_CONTENT_LENGTH_HEADER])
        ) {
            return false;
        }
        return true;
    }

    public static function isV3Envelope(MetadataEnvelope $envelope): bool
    {

        //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
        //= type=implication
        //# - The mapkey "x-amz-3" MUST be present for V3 format objects.
        if (!isset($envelope[MetadataEnvelope::ENCRYPTED_DATA_KEY_V3])
            //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
            //= type=implication
            //# - The mapkey "x-amz-c" MUST be present for V3 format objects.
            || !isset($envelope[MetadataEnvelope::CONTENT_CIPHER_V3])
            //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
            //= type=implication
            //# - The mapkey "x-amz-d" MUST be present for V3 format objects.
            || !isset($envelope[MetadataEnvelope::KEY_COMMITMENT_V3])
            //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
            //= type=implication
            //# - The mapkey "x-amz-i" MUST be present for V3 format objects.
            || !isset($envelope[MetadataEnvelope::MESSAGE_ID_V3])
            //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
            //= type=implication
            //# - The mapkey "x-amz-w" MUST be present for V3 format objects.
            || !isset($envelope[MetadataEnvelope::ENCRYPTED_DATA_KEY_ALGORITHM_V3])
            //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
            //= type=implication
            //# - The mapkey "x-amz-t" SHOULD be present for V3 format objects that use KMS Encryption Context.
            || !isset($envelope[MetadataEnvelope::ENCRYPTION_CONTEXT_V3])
        ) {
            return false;
        }
        
        return true;
    }

    public static function getV2Fields(): array
    {
        return [
            MetadataEnvelope::CONTENT_KEY_V2_HEADER,
            MetadataEnvelope::MATERIALS_DESCRIPTION_HEADER,
            MetadataEnvelope::IV_HEADER,
            MetadataEnvelope::KEY_WRAP_ALGORITHM_HEADER,
            MetadataEnvelope::CONTENT_CRYPTO_SCHEME_HEADER,
            MetadataEnvelope::CRYPTO_TAG_LENGTH_HEADER
        ];
    }

    public static function getV3Fields(): array
    {
        return [
            MetadataEnvelope::ENCRYPTED_DATA_KEY_V3,
            MetadataEnvelope::CONTENT_CIPHER_V3,
            MetadataEnvelope::KEY_COMMITMENT_V3,
            MetadataEnvelope::MESSAGE_ID_V3,
            MetadataEnvelope::ENCRYPTED_DATA_KEY_ALGORITHM_V3,
            MetadataEnvelope::ENCRYPTION_CONTEXT_V3
        ];
    }
}
