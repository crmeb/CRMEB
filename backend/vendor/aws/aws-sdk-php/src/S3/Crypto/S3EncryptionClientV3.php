<?php
namespace Aws\S3\Crypto;

use Aws\Crypto\DecryptionTraitV3;
use Aws\Exception\CryptoException;
use Aws\HashingStream;
use Aws\MetricsBuilder;
use Aws\PhpHash;
use Aws\Result;
use Aws\Crypto\AbstractCryptoClientV3;
use Aws\Crypto\EncryptionTraitV3;
use Aws\Crypto\MetadataEnvelope;
use Aws\Crypto\AlgorithmSuite;
use Aws\Crypto\Cipher\CipherBuilderTrait;
use Aws\S3\S3Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7;

/**
 * Provides a wrapper for an S3Client that supplies functionality to encrypt
 * data on putObject[Async] calls and decrypt data on getObject[Async] calls.
 *
 * AWS strongly recommends the upgrade to the S3EncryptionClientV3 (over the
 * S3EncryptionClientV2 or S3EncryptionClient), as it offers updated 
 * data security best practices to our customers who upgrade.
 * S3EncryptionClientV3 contains breaking changes, so this
 * will require planning by engineering teams to migrate. New workflows should
 * just start with S3EncryptionClientV3.
 *
 *
 * Example write path:
 *
 * <code>
 * use Aws\Crypto\KmsMaterialsProviderV3;
 * use Aws\S3\Crypto\S3EncryptionClientV3;
 * use Aws\S3\S3Client;
 *
 * $encryptionClient = new S3EncryptionClientV3(
 *     new S3Client([
 *         'region' => 'us-west-2',
 *         'version' => 'latest'
 *     ])
 * );
 * $materialsProvider = new KmsMaterialsProviderV3(
 *     new KmsClient([
 *         'profile' => 'default',
 *         'region' => 'us-east-1',
 *         'version' => 'latest',
 *     ],
 *    'your-kms-key-id'
 * );
 *
 * $encryptionClient->putObject([
 *     '@MaterialsProvider' => $materialsProvider,
 *     '@CipherOptions' => [
 *         'Cipher' => 'gcm',
 *         'KeySize' => 256,
 *     ],
 *     '@CommitmentPolicy' => 'REQUIRE_ENCRYPT_REQUIRE_DECRYPT',
 *     '@KmsEncryptionContext' => ['foo' => 'bar'],
 *     'Bucket' => 'your-bucket',
 *     'Key' => 'your-key',
 *     'Body' => 'your-encrypted-data',
 * ]);
 * </code>
 *
 * Example read call (using objects from previous example):
 *
 * <code>
 * $encryptionClient->getObject([
 *     '@MaterialsProvider' => $materialsProvider,
 *     '@CipherOptions' => [
 *         'Cipher' => 'gcm',
 *         'KeySize' => 256,
 *     ],
 *     '@CommitmentPolicy' => 'REQUIRE_ENCRYPT_REQUIRE_DECRYPT',
 *     'Bucket' => 'your-bucket',
 *     'Key' => 'your-key',
 * ]);
 * </code>
 */
class S3EncryptionClientV3 extends AbstractCryptoClientV3
{
    use CipherBuilderTrait;
    use CryptoParamsTraitV3;
    use DecryptionTraitV3;
    use EncryptionTraitV3;
    use UserAgentTrait;

    const CRYPTO_VERSION = '3.0';

    private S3Client $client;
    private ?string $instructionFileSuffix;
    private int $legacyWarningCount;

    /**
     * @param S3Client $client The S3Client to be used for true uploading and
     *                         retrieving objects from S3 when using the
     *                         encryption client.
     * @param string|null $instructionFileSuffix Suffix for a client wide
     *                                           default when using instruction
     *                                           files for metadata storage.
     */
    public function __construct(
        //= ../specification/s3-encryption/client.md#wrapped-s3-client-s
        //= type=implication
        //# The S3EC MUST support the option to provide an SDK S3 client instance during its initialization.
        S3Client $client,
        //= ../specification/s3-encryption/client.md#aws-sdk-compatibility
        //= type=implication
        //# The S3EC MUST provide a different set of configuration options than the conventional S3 client.

        //= ../specification/s3-encryption/client.md#instruction-file-configuration
        //= type=implication
        //# In this case, the Instruction File Configuration SHOULD be optional,
        //# such that its default configuration is used when none is provided.
        ?string $instructionFileSuffix = null
    ) {
        //= ../specification/s3-encryption/client.md#aws-sdk-compatibility
        //= type=implication
        //# The S3EC MUST adhere to the same interface for API operations as the conventional AWS SDK S3 client.
        $this->appendUserAgent($client, 'feat/s3-encrypt-php/' . self::CRYPTO_VERSION);
        //= ../specification/s3-encryption/client.md#wrapped-s3-client-s
        //# The S3EC MUST NOT support use of S3EC as the provided S3 client during its initialization;
        //# it MUST throw an exception in this case.
        if ($client instanceof S3EncryptionClientV3) {
            throw new CryptoException("Client configuration error."
                . " An S3 Encryption Client is not a valid S3 client for an S3 Encryption Client.");   
        }
        $this->client = $client;
        //= ../specification/s3-encryption/client.md#instruction-file-configuration
        //= type=implication
        //# The S3EC MAY support the option to provide Instruction File Configuration during its initialization.

        //= ../specification/s3-encryption/client.md#instruction-file-configuration
        //= type=implication
        //# If the S3EC in a given language supports Instruction Files,
        //# then it MUST accept Instruction File Configuration during its initialization.
        $this->instructionFileSuffix = $instructionFileSuffix;
        $this->legacyWarningCount = 0;
        MetricsBuilder::appendMetricsCaptureMiddleware(
            $this->client->getHandlerList(),
            MetricsBuilder::S3_CRYPTO_V3
        );

        if (!extension_loaded('openssl')) {
            throw new CryptoException("Unable to load `openssl` extension.");
        }
    }

    //= ../specification/s3-encryption/data-format/metadata-strategy.md#instruction-file
    //# Instruction File writes MUST NOT be enabled by default.
    private static function getDefaultStrategy(): HeadersMetadataStrategy
    {
        return new HeadersMetadataStrategy();
    }

    /**
     * Encrypts the data in the 'Body' field of $args and promises to upload it
     * to the specified location on S3.
     *
     * @param array $args Arguments for encrypting an object and uploading it
     *                    to S3 via PutObject.
     *
     * The required configuration arguments are as follows:
     *
     * - @MaterialsProvider: (MaterialsProviderV3) Provides Cek, Iv, and Cek
     *   encrypting/decrypting for encryption metadata.
     * - @CommitmentPolicy: (string) Must be set to 'FORBID_ENCRYPT_ALLOW_DECRYPT',
     *         'REQUIRE_ENCRYPT_ALLOW_DECRYPT', or 'REQUIRE_ENCRYPT_REQUIRE_DECRYPT'.
     *      - 'FORBID_ENCRYPT_ALLOW_DECRYPT' indicates that the client is configured
     *         to write messages without key commitment and read messages encrypted 
     *         with key commitment or without key commitment.
     *      - 'REQUIRE_ENCRYPT_ALLOW_DECRYPT' indicates that the client is configured
     *         to write messages with key commitment and read messages encrypted 
     *         with key commitment or without key commitment.
     *      - 'REQUIRE_ENCRYPT_REQUIRE_DECRYPT' indicates that the client is configured
     *         to write messages with key commitment and read messages encrypted 
     *         with key commitment.
     * - @CipherOptions: (array) Cipher options for encrypting data. Only the
     *   Cipher option is required. Accepts the following:
     *       - Cipher: (string) gcm
     *            See also: AbstractCryptoClientV3::$supportedCiphers
     *       - KeySize: (int) 256
     *            See also: MaterialsProvider::$supportedKeySizes
     *       - Aad: (string) Additional authentication data. This option is
     *            passed directly to OpenSSL when using gcm. Note if you pass in
     *            Aad, the PHP SDK will be able to decrypt the resulting object,
     *            but other AWS SDKs may not be able to do so.
     * - @KmsEncryptionContext: (array) Only required if using
     *   KmsMaterialsProviderV3. An associative array of key-value
     *   pairs to be added to the encryption context for KMS key encryption. An
     *   empty array may be passed if no additional context is desired.
     *
     * The optional configuration arguments are as follows:
     *
     * - @MetadataStrategy: (MetadataStrategy|string|null) Strategy for storing
     *   MetadataEnvelope information. Defaults to using a
     *   HeadersMetadataStrategy. Can either be a class implementing
     *   MetadataStrategy, a class name of a predefined strategy, or empty/null
     *   to default.
     * - @InstructionFileSuffix: (string|null) Suffix used when writing to an
     *   instruction file if using an InstructionFileMetadataHandler.
     *
     * @return PromiseInterface
     *
     * @throws \InvalidArgumentException Thrown when arguments above are not
     *                                   passed or are passed incorrectly.
     */
    public function putObjectAsync(array $args): PromiseInterface
    {
        //= ../specification/s3-encryption/client.md#cryptographic-materials
        //= type=implication
        //# The S3EC MUST accept either one CMM or one Keyring instance upon initialization.
        $provider = $this->getMaterialsProvider($args);
        unset($args['@MaterialsProvider']);

        //= ../specification/s3-encryption/client.md#key-commitment
        //= type=implication
        //# The S3EC MUST support configuration of the [Key Commitment policy](./key-commitment.md) during its initialization.
        $keyCommitmentPolicy = $this->getKeyCommitmentPolicy($args);
        unset($args['@CommitmentPolicy']);

        //= ../specification/s3-encryption/data-format/metadata-strategy.md#instruction-file
        //# Instruction File writes MUST be optionally configured during client creation or on each PutObject request.
        $instructionFileSuffix = $this->getInstructionFileSuffix($args);
        unset($args['@InstructionFileSuffix']);

        $strategy = $this->getMetadataStrategy($args, $instructionFileSuffix);
        unset($args['@MetadataStrategy']);

        //= ../specification/s3-encryption/client.md#encryption-algorithm
        //= type=implication
        //# The S3EC MUST support configuration of the encryption algorithm (or algorithm suite) during its initialization.
        $options = array_change_key_case($args);
        $cipherOptions = array_intersect_key(
            $options['@cipheroptions'],
            self::$allowedOptions
        );
        if (empty($cipherOptions['Cipher'])) {
            throw new \InvalidArgumentException('An encryption cipher must be'
                . ' specified in @CipherOptions["Cipher"].');
        }

        $algorithmSuite = AlgorithmSuite::validateCommitmentPolicyOnEncrypt(
            $cipherOptions,
            $keyCommitmentPolicy
        );

        $envelope = new MetadataEnvelope();

        $bodyStream = Psr7\Utils::streamFor($args['Body']);
        // User-owned resource which should be detached instead of closed
        // during garbage-collection
        if (is_resource($args['Body'])) {
            $bodyStream = \Aws\detach_on_close_stream($bodyStream);
        }

        return Promise\Create::promiseFor(
            //= ../specification/s3-encryption/client.md#required-api-operations
            //= type=implication
            //# - PutObject MUST encrypt its input data before it is uploaded to S3.
            $this->encrypt(
                $bodyStream,
                $algorithmSuite,
                $options,
                $provider,
                $envelope
            )
        )->then(
                function ($encryptedBodyStream) use ($args) {
                    $hash = new PhpHash('sha256');
                    $hashingEncryptedBodyStream = new HashingStream(
                        $encryptedBodyStream,
                        $hash,
                        self::getContentShaDecorator($args)
                    );
                    
                    return [$hashingEncryptedBodyStream, $args];
                }
            )->then(
                function ($putObjectContents) use ($strategy, $envelope) {
                    list($bodyStream, $args) = $putObjectContents;
                    if ($strategy === null) {
                        $strategy = self::getDefaultStrategy();
                    }
                    //= ../specification/s3-encryption/data-format/metadata-strategy.md#object-metadata
                    //# By default, the S3EC MUST store content metadata in the S3 Object Metadata.
                    $updatedArgs = $strategy->save($envelope, $args);
                    $updatedArgs['Body'] = $bodyStream;
                    
                    return $updatedArgs;
                }
            )->then(
                function ($args) {
                    unset($args['@CipherOptions']);
                    
                    return $this->client->putObjectAsync($args);
                }
            );
    }

    private static function getContentShaDecorator(&$args): \Closure
    {
        return function ($hash) use (&$args) {
            $args['ContentSHA256'] = bin2hex($hash);
        };
    }

    /**
     * Encrypts the data in the 'Body' field of $args and uploads it to the
     * specified location on S3.
     *
     * @param array $args Arguments for encrypting an object and uploading it
     *                    to S3 via PutObject.
     *
     * The required configuration arguments are as follows:
     *
     * - @MaterialsProvider: (MaterialsProvider) Provides Cek, Iv, and Cek
     *   encrypting/decrypting for encryption metadata.
     * - @CommitmentPolicy: (string) Must be set to 'FORBID_ENCRYPT_ALLOW_DECRYPT',
     *         'REQUIRE_ENCRYPT_ALLOW_DECRYPT', or 'REQUIRE_ENCRYPT_REQUIRE_DECRYPT'.
     *      - 'FORBID_ENCRYPT_ALLOW_DECRYPT' indicates that the client is configured
     *         to write messages without key commitment and read messages encrypted 
     *         with key commitment or without key commitment.
     *      - 'REQUIRE_ENCRYPT_ALLOW_DECRYPT' indicates that the client is configured
     *         to write messages with key commitment and read messages encrypted 
     *         with key commitment or without key commitment.
     *      - 'REQUIRE_ENCRYPT_REQUIRE_DECRYPT' indicates that the client is configured
     *         to write messages with key commitment and read messages encrypted 
     *         with key commitment.
     * - @CipherOptions: (array) Cipher options for encrypting data. A Cipher
     *   is required. Accepts the following options:
     *       - Cipher: (string) gcm
     *            See also: AbstractCryptoClientV3::$supportedCiphers
     *       - KeySize: (int) 128|256
     *            See also: MaterialsProvider::$supportedKeySizes
     *       - Aad: (string) Additional authentication data. This option is
     *            passed directly to OpenSSL when using gcm. Note if you pass in
     *            Aad, the PHP SDK will be able to decrypt the resulting object,
     *            but other AWS SDKs may not be able to do so.
     * - @KmsEncryptionContext: (array) Only required if using
     *   KmsMaterialsProviderV3. An associative array of key-value
     *   pairs to be added to the encryption context for KMS key encryption. An
     *   empty array may be passed if no additional context is desired.
     *
     * The optional configuration arguments are as follows:
     *
     * - @MetadataStrategy: (MetadataStrategy|string|null) Strategy for storing
     *   MetadataEnvelope information. Defaults to using a
     *   HeadersMetadataStrategy. Can either be a class implementing
     *   MetadataStrategy, a class name of a predefined strategy, or empty/null
     *   to default.
     * - @InstructionFileSuffix: (string|null) Suffix used when writing to an
     *   instruction file if an using an InstructionFileMetadataHandler was
     *   determined.
     *
     * @return \Aws\Result PutObject call result with the details of uploading
     *                     the encrypted file.
     *
     * @throws \InvalidArgumentException Thrown when arguments above are not
     *                                   passed or are passed incorrectly.
     */
    public function putObject(array $args): Result
    {
        //= ../specification/s3-encryption/client.md#required-api-operations
        //= type=implication
        //# - PutObject MUST be implemented by the S3EC.
        return $this->putObjectAsync($args)->wait();
    }

    /**
     * Promises to retrieve an object from S3 and decrypt the data in the
     * 'Body' field.
     *
     * @param array $args Arguments for retrieving an object from S3 via
     *                    GetObject and decrypting it.
     *
     * The required configuration argument is as follows:
     *
     * - @MaterialsProvider: (MaterialsProviderInterface) Provides Cek, Iv, and Cek
     *   encrypting/decrypting for decryption metadata. May have data loaded
     *   from the MetadataEnvelope upon decryption.
     * - @CommitmentPolicy: (string) Must be set to 'FORBID_ENCRYPT_ALLOW_DECRYPT',
     *         'REQUIRE_ENCRYPT_ALLOW_DECRYPT', or 'REQUIRE_ENCRYPT_REQUIRE_DECRYPT'.
     *      - 'FORBID_ENCRYPT_ALLOW_DECRYPT' indicates that the client is configured
     *         to write messages without key commitment and read messages encrypted 
     *         with key commitment or without key commitment.
     *      - 'REQUIRE_ENCRYPT_ALLOW_DECRYPT' indicates that the client is configured
     *         to write messages with key commitment and read messages encrypted 
     *         with key commitment or without key commitment.
     *      - 'REQUIRE_ENCRYPT_REQUIRE_DECRYPT' indicates that the client is configured
     *         to write messages with key commitment and read messages encrypted 
     *         with key commitment.
     * - @SecurityProfile: (string) Must be set to 'V3' or 'V3_AND_LEGACY'.
     *      - 'V3' indicates that only objects encrypted with S3EncryptionClientV3
     *        content encryption and key wrap schemas are able to be decrypted.
     *      - 'V3_AND_LEGACY' indicates that objects encrypted with both
     *        S3EncryptionClientV3 and older legacy encryption clients are able
     *        to be decrypted.
     *
     * The optional configuration arguments are as follows:
     *
     * - SaveAs: (string) The path to a file on disk to save the decrypted
     *   object data. This will be handled by file_put_contents instead of the
     *   Guzzle sink.
     *
     * - @MetadataStrategy: (MetadataStrategy|string|null) Strategy for reading
     *   MetadataEnvelope information. Defaults to determining based on object
     *   response headers. Can either be a class implementing MetadataStrategy,
     *   a class name of a predefined strategy, or empty/null to default.
     * - @InstructionFileSuffix: (string) Suffix used when looking for an
     *   instruction file if an InstructionFileMetadataHandler is being used.
     * - @CipherOptions: (array) Cipher options for decrypting data. A Cipher
     *   is required. Accepts the following options:
     *       - Aad: (string) Additional authentication data. This option is
     *            passed directly to OpenSSL when using gcm. It is ignored when
     *            using cbc.
     * - @KmsAllowDecryptWithAnyCmk: (bool) This allows decryption with
     *   KMS materials for any KMS key ID, instead of needing the KMS key ID to
     *   be specified and provided to the decrypt operation. Ignored for non-KMS
     *   materials providers. Defaults to false.
     *
     * @return PromiseInterface
     *
     * @throws \InvalidArgumentException Thrown when required arguments are not
     *                                   passed or are passed incorrectly.
     */
    public function getObjectAsync(array $args): PromiseInterface
    {
        //= ../specification/s3-encryption/client.md#cryptographic-materials
        //= type=implication
        //# The S3EC MUST accept either one CMM or one Keyring instance upon initialization.
        $provider = $this->getMaterialsProvider($args);
        unset($args['@MaterialsProvider']);

        //= ../specification/s3-encryption/client.md#key-commitment
        //= type=implication
        //# The S3EC MUST support configuration of the [Key Commitment policy](./key-commitment.md) during its initialization.
        $keyCommitmentPolicy = $this->getKeyCommitmentPolicy($args);
        unset($args['@CommitmentPolicy']);

        $instructionFileSuffix = $this->getInstructionFileSuffix($args);
        unset($args['@InstructionFileSuffix']);

        $strategy = $this->getMetadataStrategy($args, $instructionFileSuffix);
        unset($args['@MetadataStrategy']);

        //= ../specification/s3-encryption/client.md#enable-legacy-wrapping-algorithms
        //= type=implication
        //# The S3EC MUST support the option to enable or disable legacy wrapping algorithms.
        if (!isset($args['@SecurityProfile'])
            || !in_array($args['@SecurityProfile'], self::SUPPORTED_SECURITY_PROFILES)
        ) {
            throw new CryptoException("@SecurityProfile is required and must be"
                . " set to 'V3' or 'V3_AND_LEGACY'");
        }

        // Only throw this legacy warning once per client
        if (in_array($args['@SecurityProfile'], self::LEGACY_SECURITY_PROFILES)
            && $this->legacyWarningCount < 1
        ) {
            $this->legacyWarningCount++;
            trigger_error(
                "This S3 Encryption Client operation is configured to"
                . " read encrypted data with legacy encryption modes. If you"
                . " don't have objects encrypted with these legacy modes,"
                . " you should disable support for them to enhance security. ",
                E_USER_WARNING
            );
        }

        $saveAs = null;
        if (!empty($args['SaveAs'])) {
            $saveAs = $args['SaveAs'];
        }

        $promise = $this->client->getObjectAsync($args)
            ->then(
                function ($result) use (
                    $provider,
                    $instructionFileSuffix,
                    $strategy,
                    $keyCommitmentPolicy,
                    $args
                ) {
                    if ($strategy === null) {
                        $strategy = $this->determineGetObjectStrategy(
                            $result,
                            $instructionFileSuffix
                        );
                    }

                    $envelope = $strategy->load($args + [
                        'Metadata' => $result['Metadata']
                    ]);
                    //= ../specification/s3-encryption/client.md#required-api-operations
                    //= type=implication
                    //# - GetObject MUST decrypt data received from the S3 server and return it as plaintext.
                    $result['Body'] = $this->decrypt(
                        $result['Body'],
                        $provider,
                        $envelope,
                        $keyCommitmentPolicy,
                        $args
                    );
                    
                    return $result;
                }
            )->then(
                function ($result) use ($saveAs) {
                    if (!empty($saveAs)) {
                        file_put_contents(
                            $saveAs,
                            (string)$result['Body'],
                            LOCK_EX
                        );
                    }
                    
                    return $result;
                }
            );

        return $promise;
    }

    /**
     * Retrieves an object from S3 and decrypts the data in the 'Body' field.
     *
     * @param array $args Arguments for retrieving an object from S3 via
     *                    GetObject and decrypting it.
     *
     * The required configuration argument is as follows:
     *
     * - @MaterialsProvider: (MaterialsProviderInterface) Provides Cek, Iv, and Cek
     *   encrypting/decrypting for decryption metadata. May have data loaded
     *   from the MetadataEnvelope upon decryption.
     * - @CommitmentPolicy: (string) Must be set to 'FORBID_ENCRYPT_ALLOW_DECRYPT',
     *         'REQUIRE_ENCRYPT_ALLOW_DECRYPT', or 'REQUIRE_ENCRYPT_REQUIRE_DECRYPT'.
     *      - 'FORBID_ENCRYPT_ALLOW_DECRYPT' indicates that the client is configured
     *         to write messages without key commitment and read messages encrypted 
     *         with key commitment or without key commitment.
     *      - 'REQUIRE_ENCRYPT_ALLOW_DECRYPT' indicates that the client is configured
     *         to write messages with key commitment and read messages encrypted 
     *         with key commitment or without key commitment.
     *      - 'REQUIRE_ENCRYPT_REQUIRE_DECRYPT' indicates that the client is configured
     *         to write messages with key commitment and read messages encrypted 
     *         with key commitment.
     * - @SecurityProfile: (string) Must be set to 'V3' or 'V3_AND_LEGACY'.
     *      - 'V3' indicates that only objects encrypted with S3EncryptionClientV3
     *        content encryption and key wrap schemas are able to be decrypted.
     *      - 'V3_AND_LEGACY' indicates that objects encrypted with both
     *        S3EncryptionClientV3 and older legacy encryption clients are able
     *        to be decrypted.
     *
     * The optional configuration arguments are as follows:
     *
     * - SaveAs: (string) The path to a file on disk to save the decrypted
     *   object data. This will be handled by file_put_contents instead of the
     *   Guzzle sink.
     * - @InstructionFileSuffix: (string|null) Suffix used when looking for an
     *   instruction file if an InstructionFileMetadataHandler was detected.
     * - @CipherOptions: (array) Cipher options for encrypting data. A Cipher
     *   is required. Accepts the following options:
     *       - Aad: (string) Additional authentication data. This option is
     *            passed directly to OpenSSL when using gcm. It is ignored when
     *            using cbc.
     * - @KmsAllowDecryptWithAnyCmk: (bool) This allows decryption with
     *   KMS materials for any KMS key ID, instead of needing the KMS key ID to
     *   be specified and provided to the decrypt operation. Ignored for non-KMS
     *   materials providers. Defaults to false.
     *
     * @return \Aws\Result GetObject call result with the 'Body' field
     *                     wrapped in a decryption stream with its metadata
     *                     information.
     *
     * @throws \InvalidArgumentException Thrown when arguments above are not
     *                                   passed or are passed incorrectly.
     */
    public function getObject(array $args): Result
    {
        //= ../specification/s3-encryption/client.md#required-api-operations
        //= type=implication
        //# - GetObject MUST be implemented by the S3EC.
        return $this->getObjectAsync($args)->wait();
    }
}
