<?php
namespace Aws\CloudFront;

/**
 * @internal
 */
class Signer
{
    private $keyPairId;
    private $pkHandle;
    private $algorithm;

    public const DEFAULT_ALGORITHM = 'SHA1';

    /**
     * Supported signing algorithms, keyed by their canonical (normalized)
     * name. Values are unused sentinels — presence via isset() is the check.
     */
    public const SUPPORTED_ALGORITHMS = [
        'SHA1'   => true,
        'SHA256' => true,
    ];

    /**
     * Mapping of OpenSSL algorithm integer constants to their canonical
     * string name. Used to normalize callers who pass e.g. OPENSSL_ALGO_SHA256
     * into the string form stored in {@see self::$algorithm}.
     */
    private const OPENSSL_ALGORITHM_NAMES = [
        OPENSSL_ALGO_SHA1   => 'SHA1',
        OPENSSL_ALGO_SHA256 => 'SHA256',
    ];

    /**
     * A signer for creating the signature values used in CloudFront signed URLs
     * and signed cookies.
     *
     * @param string     $keyPairId  ID of the key pair.
     * @param string     $privateKey Path to the private key used for signing,
     *                               or a PEM-encoded key string.
     * @param string     $passphrase Passphrase to private key file, if one exists.
     * @param int|string $algorithm  Signing hash algorithm. Accepts either an
     *                               OpenSSL constant (OPENSSL_ALGO_SHA1,
     *                               OPENSSL_ALGO_SHA256) or the canonical name
     *                               string ("SHA1", "SHA256"). Defaults to SHA1.
     *
     * @throws \RuntimeException if the openssl extension is missing.
     * @throws \InvalidArgumentException if the private key cannot be found,
     *                                   the key type is not supported by
     *                                   CloudFront (RSA or ECDSA P-256), or
     *                                   the requested algorithm is not supported.
     */
    public function __construct(
        $keyPairId,
        $privateKey,
        $passphrase = "",
        string|int $algorithm = self::DEFAULT_ALGORITHM
    ) {
        if (!extension_loaded('openssl')) {
            //@codeCoverageIgnoreStart
            throw new \RuntimeException('The openssl extension is required to '
                . 'sign CloudFront urls.');
            //@codeCoverageIgnoreEnd
        }

        $this->keyPairId = $keyPairId;

        // Normalize an OpenSSL integer constant to its canonical string form,
        // then uppercase for consistent comparison. After this, $algorithm is
        // always the canonical string (matching DEFAULT_ALGORITHM's storage).
        if (is_int($algorithm) && isset(self::OPENSSL_ALGORITHM_NAMES[$algorithm])) {
            $algorithm = self::OPENSSL_ALGORITHM_NAMES[$algorithm];
        }
        $algorithm = strtoupper((string) $algorithm);
        if (!isset(self::SUPPORTED_ALGORITHMS[$algorithm])) {
            throw new \InvalidArgumentException(
                "Unsupported signature algorithm: {$algorithm}. Supported algorithms are: "
                . implode(', ', array_keys(self::SUPPORTED_ALGORITHMS)) . '.'
            );
        }

        $this->algorithm = $algorithm;

        if (!$this->pkHandle = openssl_pkey_get_private($privateKey, $passphrase)) {
            if (!file_exists($privateKey)) {
                throw new \InvalidArgumentException("PK file not found: $privateKey");
            }

            $this->pkHandle = openssl_pkey_get_private("file://$privateKey", $passphrase);
            if (!$this->pkHandle) {
                $errorMessages = [];
                while(($newMessage = openssl_error_string()) !== false){
                    $errorMessages[] = $newMessage;
                }
                throw new \InvalidArgumentException(implode("\n",$errorMessages));
            }
        }

        $this->validateKeyType($this->pkHandle);
    }

    /**
     * Ensures the loaded key is one CloudFront can verify: RSA of any modulus
     * size, or ECDSA on the P-256 curve (prime256v1 / secp256r1). Anything
     * else (DSA, EdDSA, EC on a non-P-256 curve, etc.) is rejected with an
     * actionable error message rather than surfacing as an opaque openssl_sign
     * failure at signing time.
     *
     * @param \OpenSSLAsymmetricKey|resource $pkHandle
     *
     * @throws \InvalidArgumentException on unsupported key material.
     */
    private function validateKeyType($pkHandle): void
    {
        $details = openssl_pkey_get_details($pkHandle);
        if ($details === false) {
            throw new \InvalidArgumentException(
                'Unable to read the details of the provided private key.'
            );
        }

        $type = $details['type'] ?? null;
        if ($type === OPENSSL_KEYTYPE_RSA) {
            return;
        }

        if (defined('OPENSSL_KEYTYPE_EC') && $type === OPENSSL_KEYTYPE_EC) {
            $curve = $details['ec']['curve_name'] ?? 'unknown';
            // OpenSSL reports the P-256 curve as either "prime256v1" (its
            // canonical name) or "secp256r1" (SEC 2 alias); accept both.
            if ($curve === 'prime256v1' || $curve === 'secp256r1') {
                return;
            }

            throw new \InvalidArgumentException(
                "Unsupported CloudFront key type: ECDSA on curve '{$curve}'. "
                . 'CloudFront requires ECDSA keys to be on the P-256 curve '
                . '(prime256v1 / secp256r1).'
            );
        }

        throw new \InvalidArgumentException(
            'Unsupported CloudFront key type. CloudFront requires an RSA or '
            . 'ECDSA P-256 (prime256v1) private key.'
        );
    }

    /**
     * Create the values used to construct signed URLs and cookies.
     *
     * @param string              $resource     The CloudFront resource to which
     *                                          this signature will grant access.
     *                                          Not used when a custom policy is
     *                                          provided.
     * @param string|integer|null $expires      UTC Unix timestamp used when
     *                                          signing with a canned policy.
     *                                          Not required when passing a
     *                                          custom $policy.
     * @param string              $policy       JSON policy. Use this option when
     *                                          creating a signature for a custom
     *                                          policy.
     *
     * @return array The values needed to construct a signed URL or cookie
     * @throws \InvalidArgumentException  when not provided either a policy or a
     *                                    resource and a expires
     * @throws \RuntimeException when generated signature is empty
     *
     * @link http://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/private-content-signed-cookies.html
     */
    public function getSignature($resource = null, $expires = null, $policy = null)
    {
        $signatureHash = [];
        if ($policy) {
            $policy = preg_replace('/\s/s', '', $policy);
            self::validatePolicy($policy);
            $signatureHash['Policy'] = $this->encode($policy);
        } elseif ($resource && $expires) {
            self::validateResourceUrl($resource);
            $expires = (int) $expires; // Handle epoch passed as string
            $policy = $this->createCannedPolicy($resource, $expires);
            $signatureHash['Expires'] = $expires;
        } else {
            throw new \InvalidArgumentException('Either a policy or a resource'
                . ' and an expiration time must be provided.');
        }

        $signatureHash['Signature'] = $this->encode($this->sign($policy));
        $signatureHash['Key-Pair-Id'] = $this->keyPairId;

        if ($this->algorithm !== self::DEFAULT_ALGORITHM) {
            $signatureHash['Hash-Algorithm'] = $this->algorithm;
        }

        return $signatureHash;
    }

    private function createCannedPolicy($resource, $expiration)
    {
        return json_encode([
            'Statement' => [
                [
                    'Resource' => $resource,
                    'Condition' => [
                        'DateLessThan' => ['AWS:EpochTime' => $expiration],
                    ],
                ],
            ],
        ], JSON_UNESCAPED_SLASHES);
    }

    private function sign($policy)
    {
        $signature = '';
        
        if(!openssl_sign($policy, $signature, $this->pkHandle, $this->algorithm)) {
            $errorMessages = [];
            while(($newMessage = openssl_error_string()) !== false) {
                $errorMessages[] = $newMessage;
            }
            
            $exceptionMessage = "An error has occurred when signing the policy";
            if (count($errorMessages) > 0) {
                $exceptionMessage = implode("\n", $errorMessages);
            }

            throw new \RuntimeException($exceptionMessage);
        }

        return $signature;
    }

    private function encode($policy)
    {
        return strtr(base64_encode($policy), '+=/', '-_~');
    }

    /**
     * Validates a customer provided json document.
     *
     * @param string $jsonPolicy
     *
     * @return void
     */
    private static function validatePolicy(string $jsonPolicy): void
    {
        $policy = json_decode($jsonPolicy, true);
        foreach ($policy['Statement'] ?? [] as $statement) {
            if (isset($statement['Resource'])) {
                self::validateResourceUrl($statement['Resource']);
            }
        }
    }

    /**
     * @param string $url
     *
     * @return void
     */
    private static function validateResourceUrl(string $url): void
    {
        if (preg_match('/["\\\\\x00-\x1F]/', $url)) {
            throw new \InvalidArgumentException(
                'URL contains invalid characters: ", \\, or control characters'
            );
        }
    }
}
