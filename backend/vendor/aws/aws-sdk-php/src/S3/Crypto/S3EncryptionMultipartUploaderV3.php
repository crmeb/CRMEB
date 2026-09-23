<?php
namespace Aws\S3\Crypto;

use Aws\Crypto\AbstractCryptoClientV3;
use Aws\Crypto\EncryptionTraitV3;
use Aws\Crypto\AlgorithmSuite;
use Aws\Crypto\MaterialsProviderInterfaceV3;
use Aws\Crypto\MetadataEnvelope;
use Aws\Crypto\Cipher\CipherBuilderTrait;
use Aws\Exception\CryptoException;
use Aws\S3\MultipartUploader;
use Aws\S3\S3ClientInterface;
use GuzzleHttp\Promise;

//= ../specification/s3-encryption/client.md#optional-api-operations
//= type=implication
//# - CreateMultipartUpload MAY be implemented by the S3EC.
/**
 * Encapsulates the execution of a multipart upload of an encrypted object to S3.
 *
 */
class S3EncryptionMultipartUploaderV3 extends MultipartUploader
{
    use CipherBuilderTrait;
    use CryptoParamsTraitV3;
    use EncryptionTraitV3;
    use UserAgentTrait;

    CONST CRYPTO_VERSION = '3.0';

    // MaterialsProvider to be used with this client
    private MaterialsProviderInterfaceV3 $provider;
    // Commitment Policy to use with this client
    private string $keyCommitmentPolicy;
    // Algorithm Suite to use with this client
    private AlgorithmSuite $algorithmSuite;
    // Suffix to use when using a InstructionFileMetadata strategy
    private ?string $instructionFileSuffix;
    // Metadata Strategy to use when uploading objects
    private $strategy;
    
    /**
     * Returns if the passed cipher name is supported for encryption by the SDK.
     *
     * @param string $cipherName The name of a cipher to verify is registered.
     *
     * @return bool If the cipher passed is in our supported list.
     */
    public static function isSupportedCipher($cipherName)
    {
        return in_array($cipherName, AbstractCryptoClientV3::$supportedCiphers);
    }

    /**
     * Creates a multipart upload for an S3 object after encrypting it.
     *
     * The required configuration options are as follows:
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
     * - @CipherOptions: (array) Cipher options for encrypting data. A Cipher
     *   is required. Accepts the following options:
     *       - Cipher: (string) gcm
     *            See also: AbstractCryptoClientV3::$supportedCiphers
     *       - KeySize: (int) 256
     *            See also: MaterialsProviderV3::$supportedKeySizes
     *       - Aad: (string) Additional authentication data. This option is
     *            passed directly to OpenSSL when using gcm.
     * - @KmsEncryptionContext: (array) Only required if using
     *   KmsMaterialsProviderV3. An associative array of key-value
     *   pairs to be added to the encryption context for KMS key encryption. An
     *   empty array may be passed if no additional context is desired.
     * - bucket: (string) Name of the bucket to which the object is
     *   being uploaded.
     * - key: (string) Key to use for the object being uploaded.
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
     * - acl: (string) ACL to set on the object being upload. Objects are
     *   private by default.
     * - before_complete: (callable) Callback to invoke before the
     *   `CompleteMultipartUpload` operation. The callback should have a
     *   function signature like `function (Aws\Command $command) {...}`.
     * - before_initiate: (callable) Callback to invoke before the
     *   `CreateMultipartUpload` operation. The callback should have a function
     *   signature like `function (Aws\Command $command) {...}`.
     * - before_upload: (callable) Callback to invoke before any `UploadPart`
     *   operations. The callback should have a function signature like
     *   `function (Aws\Command $command) {...}`.
     * - concurrency: (int, default=int(5)) Maximum number of concurrent
     *   `UploadPart` operations allowed during the multipart upload.
     * - params: (array) An array of key/value parameters that will be applied
     *   to each of the sub-commands run by the uploader as a base.
     *   Auto-calculated options will override these parameters. If you need
     *   more granularity over parameters to each sub-command, use the before_*
     *   options detailed above to update the commands directly.
     * - part_size: (int, default=int(5242880)) Part size, in bytes, to use when
     *   doing a multipart upload. This must between 5 MB and 5 GB, inclusive.
     * - state: (Aws\Multipart\UploadState) An object that represents the state
     *   of the multipart upload and that is used to resume a previous upload.
     *   When this option is provided, the `bucket`, `key`, and `part_size`
     *   options are ignored.
     *
     * @param S3ClientInterface $client Client used for the upload.
     * @param mixed             $source Source of the data to upload.
     * @param array             $config Configuration used to perform the upload.
     */
    public function __construct(
        S3ClientInterface $client,
        $source,
        array $config = []
    ) {
        $this->appendUserAgent($client, 'feat/s3-encrypt/' . self::CRYPTO_VERSION);
        $this->client = $client;
        $config['params'] = [];
        if (!empty($config['bucket'])) {
            $config['params']['Bucket'] = $config['bucket'];
        }
        if (!empty($config['key'])) {
            $config['params']['Key'] = $config['key'];
        }

        $this->provider = $this->getMaterialsProvider($config);
        unset($config['@MaterialsProvider']);
        
        $this->keyCommitmentPolicy = $this->getKeyCommitmentPolicy($config);
        unset($config['@CommitmentPolicy']);

        $this->instructionFileSuffix = $this->getInstructionFileSuffix($config);
        unset($config['@InstructionFileSuffix']);
        $this->strategy = $this->getMetadataStrategy(
            $config,
            $this->instructionFileSuffix
        );
        if ($this->strategy === null) {
            $this->strategy = self::getDefaultStrategy();
        }
        unset($config['@MetadataStrategy']);
        
        $options = array_change_key_case($config);
        $cipherOptions = array_intersect_key(
            $options['@cipheroptions'],
            self::$allowedOptions
        );
        if (empty($cipherOptions['Cipher'])) {
            throw new \InvalidArgumentException('An encryption cipher must be'
                . ' specified in @CipherOptions["Cipher"].');
        }

        $this->algorithmSuite = AlgorithmSuite::validateCommitmentPolicyOnEncrypt(
            $cipherOptions,
            $this->keyCommitmentPolicy
        );
        
        //= ../specification/s3-encryption/client.md#optional-api-operations
        //= type=implication
        //# - If implemented, CreateMultipartUpload MUST initiate a multipart upload.
        $config['prepare_data_source'] = $this->getEncryptingDataPreparer();

        parent::__construct($client, $source, $config);
        
        if (!extension_loaded('openssl')) {
            throw new CryptoException("Unable to load `openssl` extension.");
        }
    }

    private static function getDefaultStrategy()
    {
        return new HeadersMetadataStrategy();
    }

    private function getEncryptingDataPreparer()
    {
        return function() {
            // Defer encryption work until promise is executed
            $envelope = new MetadataEnvelope();

            list($this->source, $params) = Promise\Create::promiseFor($this->encrypt(
                $this->source,
                $this->algorithmSuite,
                $this->config ?: [],
                $this->provider,
                $envelope
            ))->then(
                function ($bodyStream) use ($envelope) {
                    $params = $this->strategy->save(
                        $envelope,
                        $this->config['params']
                    );
                    return [$bodyStream, $params];
                }
            )->wait();

            $this->source->rewind();
            $this->config['params'] = $params;
        };
    }
}
