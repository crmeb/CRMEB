<?php
namespace Aws\Signature;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Random\RandomException;

final class DpopSignature
{
    public const ALLOW_LISTED_SERVICES = ['signin' => true];
    private const EXT_OPENSSL = 'openssl';
    private const CURVE_NAME = 'prime256v1';
    private const HEADER_DPOP = 'DPop';
    private const HEADER_TYP = 'dpop+jwt';
    private const HEADER_ALG = 'ES256';
    private const HEADER_JWK_KTY = 'EC';
    private const HEADER_JWK_CRV = 'P-256';
    private const PAYLOAD_HTM = 'POST';
    private const JWK_SCHEMA = [
        'kty' => self::HEADER_JWK_KTY,
        'crv' => self::HEADER_JWK_CRV,
    ];
    private const HEADER_SCHEMA = [
        'typ' => self::HEADER_TYP,
        'alg' => self::HEADER_ALG,
        'jwk' => self::JWK_SCHEMA
    ];
    private const PAYLOAD_SCHEMA = [
        'htm' => self::PAYLOAD_HTM,
    ];

    /**
     * Creates a new DpopSignature instance for the specified service
     *
     * @param string $serviceName The name of the AWS service (must be in the allow list)
     *
     * @throws \RuntimeException If the OpenSSL extension is not loaded
     * @throws \InvalidArgumentException If the service is not in the allow list
     */
    public function __construct(string $serviceName)
    {
        if (!extension_loaded(self::EXT_OPENSSL)) {
            throw new \RuntimeException(
                'the `openssl` extension is required to generate DPop signatures. '
                . 'Please install or enable the `openssl` extension.'
            );
        }

        if (!isset(self::ALLOW_LISTED_SERVICES[$serviceName])) {
            throw new \InvalidArgumentException(
                "The '{$serviceName}' service does not support DPop signatures. "
                . 'Please configure a signature version this service supports.'
            );
        }
    }

    /**
     * Signs an HTTP request with a DPoP header
     *
     * @param RequestInterface $request The HTTP request to sign
     * @param \OpenSSLAsymmetricKey $key The private key for signing
     *
     * @return RequestInterface The request with the DPoP header added
     * @throws \RuntimeException|\Exception If signature generation fails
     */
    public function signRequest(
        RequestInterface $request,
        \OpenSSLAsymmetricKey $key,
    ) {
        $dpopHeaderValue = $this->generateDpopProof($key, $request->getUri());

        return $request->withHeader(self::HEADER_DPOP, $dpopHeaderValue);
    }

    /**
     * Generates the DPoP JWT header value for the request
     *
     * @param \OpenSSLAsymmetricKey $key The private key for signing
     * @param UriInterface $uri The URI of the request
     *
     * @return string The complete DPoP JWT token
     * @throws \RuntimeException|\Exception If signature generation fails
     */
    private function generateDpopProof(
        \OpenSSLAsymmetricKey $key,
        UriInterface $uri
    ): string
    {
        $keyDetails = openssl_pkey_get_details($key);
        if (($keyDetails['ec']['curve_name'] ?? '') !== self::CURVE_NAME) {
            throw new \InvalidArgumentException(
                'DPoP signature keys must use P-256 curve. '
                . 'Please check your configuration and try again.'
            );
        }

        ['x' => $x, 'y' => $y] = $keyDetails['ec'];
        $header = $this->buildDpopHeader($x, $y);
        $payload = $this->buildDpopPayload((string) $uri);

        $message = $this->base64url_encode(json_encode($header))
            . '.' . $this->base64url_encode(json_encode($payload));
        $signature = '';
        if (!openssl_sign($message, $signature, $key, OPENSSL_ALGO_SHA256)) {
            $error = openssl_error_string();

            throw new \RuntimeException(
                'Failed to generate signature.' . ($error ? ": $error" : '.')
            );
        }

        $signature = $this->derToRaw($signature);

        return $message . '.' . $this->base64url_encode($signature);
    }

    /**
     * Builds the DPoP JWT header with the public key coordinates
     *
     * @param string $x The x-coordinate of the EC public key
     * @param string $y The y-coordinate of the EC public key
     *
     * @return array The complete DPoP header with JWK
     */
    private function buildDpopHeader(string $x, string $y): array
    {
        return array_merge_recursive(self::HEADER_SCHEMA, [
            'jwk' => [
                'x' => $this->base64url_encode($x),
                'y' => $this->base64url_encode($y)
            ]
        ]);
    }

    /**
     * Builds the DPoP JWT payload with request-specific claims
     *
     * @param string $uri The target URI for the HTTP request
     *
     * @return array The complete DPoP payload with jti, htm, htu, and iat claims
     * @throws RandomException
     */
    private function buildDpopPayload(string $uri): array
    {
        return array_merge(self::PAYLOAD_SCHEMA, [
            'jti' => $this->uuidv4(),
            'htu' => $uri,
            'iat' => time()
        ]);
    }

    /**
     * Generates a version 4 UUID
     *
     * @return string A UUID v4 string
     * @throws RandomException
     */
    private function uuidv4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Encodes data to Base64URL format (RFC 4648)
     *
     * @param string $data The data to encode
     *
     * @return string Base64URL encoded string (no padding, URL-safe characters)
     */
    private function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Convert DER-encoded ECDSA signature to raw R||S format (64 bytes for ES256)
     *
     * @param string $derSignature DER-encoded signature from openssl_sign
     *
     * @return string Raw signature (64 bytes: 32 bytes R + 32 bytes S)
     * @throws \Exception If the DER signature is invalid
     */
    private function derToRaw(string $derSignature): string
    {
        $hex = bin2hex($derSignature);
        $pos = 0;
        
        // Parse SEQUENCE tag (0x30)
        if (substr($hex, $pos, 2) !== '30') {
            throw new \Exception('Invalid DER signature format: missing SEQUENCE tag');
        }
        $pos += 2;
        
        // Parse SEQUENCE length
        $seqLen = hexdec(substr($hex, $pos, 2));
        $pos += 2;
        
        // Parse first INTEGER tag (0x02) for R
        if (substr($hex, $pos, 2) !== '02') {
            throw new \Exception('Invalid DER signature format: missing R INTEGER tag');
        }
        $pos += 2;
        
        // Parse R length
        $rLen = hexdec(substr($hex, $pos, 2));
        $pos += 2;
        
        // Extract R value
        $r = substr($hex, $pos, $rLen * 2);
        if (strlen($r) !== $rLen * 2) {
            throw new \Exception('Invalid DER signature: R length mismatch');
        }
        $pos += $rLen * 2;
        
        // Parse second INTEGER tag (0x02) for S  
        if (substr($hex, $pos, 2) !== '02') {
            throw new \Exception('Invalid DER signature format: missing S INTEGER tag');
        }
        $pos += 2;
        
        // Parse S length
        $sLen = hexdec(substr($hex, $pos, 2));
        $pos += 2;
        
        // Extract S value
        $s = substr($hex, $pos, $sLen * 2);
        if (strlen($s) !== $sLen * 2) {
            throw new \Exception('Invalid DER signature: S length mismatch');
        }
        
        // Remove leading zeros (if any) and pad to 32 bytes
        $r = str_pad(ltrim($r, '0'), 64, '0', STR_PAD_LEFT);
        $s = str_pad(ltrim($s, '0'), 64, '0', STR_PAD_LEFT);
        
        // Ensure exactly 32 bytes each
        if (strlen($r) !== 64 || strlen($s) !== 64) {
            throw new \Exception('Invalid signature component length');
        }
        
        return hex2bin($r . $s);
    }
}
