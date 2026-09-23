<?php
namespace Aws\Crypto;

class AlgorithmConstants
{
    /**
     * The maximum number of 16-byte blocks that can be encrypted with a
     * GCM cipher. Note the maximum bit-length of the plaintext is (2^39 - 256),
     * which translates to a maximum byte-length of (2^36 - 32), which in turn
     * translates to a maximum block-length of (2^32 - 2).
     *
     * Reference: NIST Special Publication 800-38D.
     * @link http://csrc.nist.gov/publications/nistpubs/800-38D/SP-800-38D.pdf
     */
    public const GCM_MAX_CONTENT_LENGTH_BITS = (1 << 39) - 256;

    /**
     * The Maximum length of the content that can be encrypted in CBC mode.
     */
    public const CBC_MAX_CONTENT_LENGTH_BYTES = 1 << 55;

    /**
     * The maximum number of bytes that can be securely encrypted per a single key using AES/CTR.
     */
    public const CTR_MAX_CONTENT_LENGTH_BYTES = -1;
}
