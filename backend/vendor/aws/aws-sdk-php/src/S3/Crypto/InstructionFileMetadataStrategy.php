<?php
namespace Aws\S3\Crypto;

use \Aws\Crypto\MetadataStrategyInterface;
use \Aws\Crypto\MetadataEnvelope;
use Aws\Exception\CryptoException;
use \Aws\S3\S3Client;

/**
 * Stores and reads encryption MetadataEnvelope information in a file on Amazon
 * S3.
 *
 * A file with the contents of a MetadataEnvelope will be created or read from
 * alongside the base file on Amazon S3. The provided client will be used for
 * reading or writing this object. A specified suffix (default of '.instruction'
 * will be applied to each of the operations involved with the instruction file.
 *
 * If there is a failure after an instruction file has been uploaded, it will
 * not be automatically deleted.
 */
class InstructionFileMetadataStrategy implements MetadataStrategyInterface
{
    const DEFAULT_FILE_SUFFIX = '.instruction';

    private $client;
    private $suffix;

    /**
     * @param S3Client $client Client for use in uploading the instruction file.
     * @param string|null $suffix Optional override suffix for instruction file
     *                            object keys.
     */
    public function __construct(S3Client $client, $suffix = null)
    {
        $this->suffix = empty($suffix)
            ? self::DEFAULT_FILE_SUFFIX
            : $suffix;
        $this->client = $client;
    }

    /**
     * Places the information in the MetadataEnvelope to a location on S3.
     *
     * @param MetadataEnvelope $envelope Encryption data to save according to
     *                                   the strategy.
     * @param array $args Starting arguments for PutObject, used for saving
     *                    extra the instruction file.
     *
     * @return array Updated arguments for PutObject.
     */
    public function save(MetadataEnvelope $envelope, array $args)
    {
        //= ../specification/s3-encryption/data-format/metadata-strategy.md#instruction-file
        //# The S3EC MUST support writing some or all (depending on format) content metadata to an Instruction File.

        //= ../specification/s3-encryption/data-format/metadata-strategy.md#v1-v2-instruction-files
        //# In the V1/V2 message format, all of the content metadata MUST be stored in the Instruction File.
        
        //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
        //= type=implication
        //# In the V3 format, the mapkeys "x-amz-c", "x-amz-d", and "x-amz-i" MUST be stored exclusively in the Object Metadata.
        if (MetadataEnvelope::isV3Envelope($envelope)) {
            //= ../specification/s3-encryption/data-format/metadata-strategy.md#v3-instruction-files
            //# - The V3 message format MUST store the mapkey "x-amz-c" and its value in the Object Metadata when writing with an Instruction File.
            $args['Metadata'][MetadataEnvelope::CONTENT_CIPHER_V3] = $envelope[MetadataEnvelope::CONTENT_CIPHER_V3];
            //= ../specification/s3-encryption/data-format/metadata-strategy.md#v3-instruction-files
            //# - The V3 message format MUST store the mapkey "x-amz-d" and its value in the Object Metadata when writing with an Instruction File.
            $args['Metadata'][MetadataEnvelope::KEY_COMMITMENT_V3] = $envelope[MetadataEnvelope::KEY_COMMITMENT_V3];
            //= ../specification/s3-encryption/data-format/metadata-strategy.md#v3-instruction-files
            //# - The V3 message format MUST store the mapkey "x-amz-i" and its value in the Object Metadata when writing with an Instruction File.
            $args['Metadata'][MetadataEnvelope::MESSAGE_ID_V3] = $envelope[MetadataEnvelope::MESSAGE_ID_V3];
            
            //= ../specification/s3-encryption/data-format/metadata-strategy.md#v3-instruction-files
            //# - The V3 message format MUST NOT store the mapkey "x-amz-c" and its value in the Instruction File.
            unset($envelope[MetadataEnvelope::CONTENT_CIPHER_V3]);
            //= ../specification/s3-encryption/data-format/metadata-strategy.md#v3-instruction-files
            //# - The V3 message format MUST NOT store the mapkey "x-amz-d" and its value in the Instruction File.
            unset($envelope[MetadataEnvelope::KEY_COMMITMENT_V3]);
            //= ../specification/s3-encryption/data-format/metadata-strategy.md#v3-instruction-files
            //# - The V3 message format MUST NOT store the mapkey "x-amz-i" and its value in the Instruction File.
            unset($envelope[MetadataEnvelope::MESSAGE_ID_V3]);
            if (
                //= ../specification/s3-encryption/data-format/metadata-strategy.md#v3-instruction-files
                //# - The V3 message format MUST store the mapkey "x-amz-3" and its value in the Instruction File.
                !isset($envelope[MetadataEnvelope::ENCRYPTED_DATA_KEY_V3])
                //= ../specification/s3-encryption/data-format/metadata-strategy.md#v3-instruction-files
                //# - The V3 message format MUST store the mapkey "x-amz-w" and its value in the Instruction File.
                || !isset($envelope[MetadataEnvelope::ENCRYPTED_DATA_KEY_ALGORITHM_V3])
                //= ../specification/s3-encryption/data-format/metadata-strategy.md#v3-instruction-files
                //# - The V3 message format MUST store the mapkey "x-amz-t" and its value (when present in the content metadata) in the Instruction File.
                || !isset($envelope[MetadataEnvelope::ENCRYPTION_CONTEXT_V3])
            ) {
                throw new \InvalidArgumentException('Invalid V3 Envelope');
            }
        }
        
        //= ../specification/s3-encryption/data-format/metadata-strategy.md#instruction-file
        //# The serialized JSON string MUST be the only contents of the Instruction File.
        $this->client->putObject([
            'Bucket' => $args['Bucket'],
            'Key' => $args['Key'] . $this->suffix,
            //= ../specification/s3-encryption/data-format/metadata-strategy.md#instruction-file
            //= type=implication
            //# The content metadata stored in the Instruction File MUST be serialized to a JSON string.
            'Body' => json_encode($envelope)
        ]);

        return $args;
    }

    /**
     * Uses the strategy's client to retrieve the instruction file from S3 and generates
     * a MetadataEnvelope from its contents.
     *
     * @param array $args Arguments from Command and Result that contains
     *                    S3 Object information, relevant headers, and command
     *                    configuration.
     *
     * @return MetadataEnvelope
     */
    public function load(array $args)
    {
        $result = $this->client->getObject([
            'Bucket' => $args['Bucket'],
            'Key' => $args['Key'] . $this->suffix
        ]);

        $metadataHeaders = json_decode($result['Body'], true);
        $envelope = new MetadataEnvelope();
        $constantValues = MetadataEnvelope::getConstantValues();

        foreach ($constantValues as $constant) {
            if (isset($metadataHeaders[$constant])) {
                // check for a duplicate
                if (empty($envelope[$constant])) {
                    $envelope[$constant] = $metadataHeaders[$constant];
                } else {
                    throw new CryptoException("Duplicate keys are not allowed"
                        . " in Instruction Files. "
                    );
                }
            }
        }

        // check if we are reading a V3 object
        // if it is a V3 object some data is stored in the object metadata and some
        // as in the instruction file
        //= ../specification/s3-encryption/data-format/content-metadata.md#content-metadata-mapkeys
        //# In the V3 format, the mapkeys "x-amz-c", "x-amz-d", and "x-amz-i" 
        //# MUST be stored exclusively in the Object Metadata
        if (!empty($envelope[MetadataEnvelope::ENCRYPTED_DATA_KEY_V3])) {
            // before loading the rest of the v3 metadata, we must check that:
            //  1. the following values are not already present in the envelope, if they are
            //      the instruction file is not correct.
            if (!empty($envelope[MetadataEnvelope::CONTENT_CIPHER_V3])
                || !empty($envelope[MetadataEnvelope::KEY_COMMITMENT_V3])
                || !empty($envelope[MetadataEnvelope::MESSAGE_ID_V3]) 
            ) {
                throw new CryptoException("One or more reserved keys found in"
                    . " Instruction file when they should not be present.");
            }
            // this data is stored in the original object's metadata
            // V3 added x-amz-c, x-amz-d, x-amz-i, x-amz-3, x-amz-w, x-amz-m, x-amz-t
            // x-amz-c, x-amz-d, x-amz-i are strictly stored on the object metadata
            // the rest are stored in the instruction file
            $envelope[MetadataEnvelope::CONTENT_CIPHER_V3] = $args['Metadata'][MetadataEnvelope::CONTENT_CIPHER_V3];
            $envelope[MetadataEnvelope::KEY_COMMITMENT_V3] = $args['Metadata'][MetadataEnvelope::KEY_COMMITMENT_V3];
            $envelope[MetadataEnvelope::MESSAGE_ID_V3] = $args['Metadata'][MetadataEnvelope::MESSAGE_ID_V3];

            if (!MetadataEnvelope::isV3Envelope($envelope)) {
                throw new CryptoException("Expected a V3 envelope but was unable to"
                    . " constuct one.");
            }
            return $envelope;
        }
        // if we are not reading a v3 object then it must be a v2 object
        if (!MetadataEnvelope::isV2Envelope($envelope)
            && !MetadataEnvelope::isV1Envelope($envelope)) {
            throw new CryptoException("Malformed metadata envelope.");
        }

        return $envelope;
    }
}
