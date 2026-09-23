<?php

namespace Overtrue\Socialite\Providers;

use Overtrue\Socialite\Contracts;
use Overtrue\Socialite\Exceptions;
use Overtrue\Socialite\User;

/**
 * @see https://developer.apple.com/documentation/sign_in_with_apple/sign_in_with_apple_rest_api
 */
class Apple extends Base
{
    public const NAME = 'apple';

    protected string $scopeSeparator = ' ';

    protected array $scopes = ['name', 'email'];

    protected string $jwksUrl = 'https://appleid.apple.com/auth/keys';

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase('https://appleid.apple.com/auth/authorize');
    }

    protected function getCodeFields(): array
    {
        return \array_merge(parent::getCodeFields(), [
            'response_mode' => 'form_post',
        ]);
    }

    protected function getTokenUrl(): string
    {
        return 'https://appleid.apple.com/auth/token';
    }

    public function getClientSecret(): ?string
    {
        if ($secret = $this->config->get(Contracts\RFC6749_ABNF_CLIENT_SECRET)) {
            return $secret;
        }

        return $this->generateClientSecret();
    }

    protected function generateClientSecret(): string
    {
        $teamId = $this->config->get('team_id');
        $keyId = $this->config->get('key_id');
        $privateKey = $this->config->get('private_key');

        if (empty($teamId) || empty($keyId) || empty($privateKey)) {
            throw new Exceptions\InvalidArgumentException(
                'Missing required Apple config: team_id, key_id, and private_key are required when client_secret is not provided.'
            );
        }

        $header = $this->base64UrlEncode((string) \json_encode(['kid' => $keyId, 'alg' => 'ES256']));
        $now = \time();
        $payload = $this->base64UrlEncode((string) \json_encode([
            'iss' => $teamId,
            'iat' => $now,
            'exp' => $now + 86400 * 180,
            'aud' => 'https://appleid.apple.com',
            'sub' => $this->getClientId(),
        ]));

        $data = $header.'.'.$payload;

        $key = \openssl_pkey_get_private($privateKey);

        if ($key === false) {
            throw new Exceptions\InvalidArgumentException('Unable to load Apple private key.');
        }

        if (! \openssl_sign($data, $derSignature, $key, OPENSSL_ALGO_SHA256)) {
            throw new Exceptions\InvalidArgumentException('Unable to sign Apple client secret.');
        }

        $rawSignature = $this->convertDerToP1363($derSignature, 32);

        return $data.'.'.$this->base64UrlEncode($rawSignature);
    }

    public function tokenFromCode(string $code): array
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'form_params' => $this->getTokenFields($code),
        ]);

        return $this->normalizeAccessTokenResponse($response->getBody());
    }

    protected function getTokenFields(string $code): array
    {
        return [
            Contracts\RFC6749_ABNF_CLIENT_ID => $this->getClientId(),
            Contracts\RFC6749_ABNF_CLIENT_SECRET => $this->getClientSecret(),
            Contracts\RFC6749_ABNF_CODE => $code,
            Contracts\RFC6749_ABNF_REDIRECT_URI => $this->redirectUrl,
            Contracts\RFC6749_ABNF_GRANT_TYPE => Contracts\RFC6749_ABNF_AUTHORATION_CODE,
        ];
    }

    public function userFromCode(string $code): Contracts\UserInterface
    {
        $tokenResponse = $this->tokenFromCode($code);
        $idToken = $tokenResponse['id_token'] ?? null;

        if (empty($idToken)) {
            throw new Exceptions\AuthorizeFailedException('Missing id_token in token response', $tokenResponse);
        }

        $user = $this->getUserByToken($idToken);

        return $this->mapUserToObject($user)
            ->setProvider($this)
            ->setRaw($user)
            ->setAccessToken($tokenResponse[Contracts\RFC6749_ABNF_ACCESS_TOKEN] ?? '')
            ->setRefreshToken($tokenResponse[Contracts\RFC6749_ABNF_REFRESH_TOKEN] ?? null)
            ->setExpiresIn($tokenResponse[Contracts\RFC6749_ABNF_EXPIRES_IN] ?? null)
            ->setTokenResponse($tokenResponse);
    }

    /**
     * Decode and verify the id_token (JWT) to get user claims.
     *
     * Verifies the token's signature against Apple's published JWKS,
     * and validates the iss, aud, and exp claims.
     *
     * @see https://developer.apple.com/documentation/sign_in_with_apple/sign_in_with_apple_rest_api/verifying_a_user
     */
    protected function getUserByToken(string $token): array
    {
        $parts = \explode('.', $token);

        if (\count($parts) !== 3) {
            throw new Exceptions\InvalidTokenException('Invalid id_token format.', $token);
        }

        $header = \json_decode($this->base64UrlDecode($parts[0]), true);
        $claims = \json_decode($this->base64UrlDecode($parts[1]), true);

        if (! \is_array($header) || ! \is_array($claims)) {
            throw new Exceptions\InvalidTokenException('Failed to decode id_token.', $token);
        }

        $this->verifyIdTokenSignature($parts, $header);
        $this->verifyIdTokenClaims($claims);

        return $claims;
    }

    /**
     * Verify the id_token JWT signature against Apple's JWKS.
     */
    protected function verifyIdTokenSignature(array $parts, array $header): void
    {
        $kid = $header['kid'] ?? null;
        $alg = $header['alg'] ?? null;

        if (empty($kid) || $alg !== 'RS256') {
            throw new Exceptions\InvalidTokenException(
                'Unsupported id_token algorithm or missing kid.',
                \implode('.', $parts)
            );
        }

        $publicKey = $this->getApplePublicKey($kid);
        $data = $parts[0].'.'.$parts[1];
        $signature = $this->base64UrlDecode($parts[2]);

        $valid = \openssl_verify($data, $signature, $publicKey, OPENSSL_ALGO_SHA256);

        if ($valid !== 1) {
            throw new Exceptions\InvalidTokenException(
                'Invalid id_token signature.',
                \implode('.', $parts)
            );
        }
    }

    /**
     * Verify the standard claims of the id_token.
     */
    protected function verifyIdTokenClaims(array $claims): void
    {
        if (($claims['iss'] ?? null) !== 'https://appleid.apple.com') {
            throw new Exceptions\InvalidTokenException(
                'Invalid id_token issuer.',
                (string) \json_encode($claims)
            );
        }

        if (($claims['aud'] ?? null) !== $this->getClientId()) {
            throw new Exceptions\InvalidTokenException(
                'Invalid id_token audience.',
                (string) \json_encode($claims)
            );
        }

        if (($claims['exp'] ?? 0) < \time()) {
            throw new Exceptions\InvalidTokenException(
                'The id_token has expired.',
                (string) \json_encode($claims)
            );
        }
    }

    /**
     * Fetch Apple's public key for the given kid from JWKS.
     */
    protected function getApplePublicKey(string $kid): \OpenSSLAsymmetricKey
    {
        $response = $this->getHttpClient()->get($this->jwksUrl);
        $jwks = $this->fromJsonBody($response);
        $keys = $jwks['keys'] ?? [];

        foreach ($keys as $key) {
            if (($key['kid'] ?? null) === $kid && ($key['kty'] ?? null) === 'RSA') {
                $n = $this->base64UrlDecode($key['n']);
                $e = $this->base64UrlDecode($key['e']);

                $publicKey = $this->buildRsaPublicKey($n, $e);

                $pkey = \openssl_pkey_get_public($publicKey);

                if ($pkey === false) {
                    throw new Exceptions\InvalidTokenException('Failed to parse Apple public key.', $kid);
                }

                return $pkey;
            }
        }

        throw new Exceptions\InvalidTokenException('Apple public key not found for kid: '.$kid, $kid);
    }

    /**
     * Build a PEM-formatted RSA public key from raw n and e values.
     */
    protected function buildRsaPublicKey(string $n, string $e): string
    {
        $modulus = \pack('Ca*a*', 0x02, $this->encodeAsn1Length(\strlen($n) + 1)."\x00", $n);
        $exponent = \pack('Ca*a*', 0x02, $this->encodeAsn1Length(\strlen($e)), $e);

        $keySequence = $modulus.$exponent;
        $keySequence = \pack('Ca*a*', 0x30, $this->encodeAsn1Length(\strlen($keySequence)), $keySequence);

        $bitString = "\x00".$keySequence;
        $bitString = \pack('Ca*a*', 0x03, $this->encodeAsn1Length(\strlen($bitString)), $bitString);

        // RSA OID: 1.2.840.113549.1.1.1
        $algorithmIdentifier = \pack('H*', '300d06092a864886f70d0101010500');

        $publicKeyInfo = $algorithmIdentifier.$bitString;
        $publicKeyInfo = \pack('Ca*a*', 0x30, $this->encodeAsn1Length(\strlen($publicKeyInfo)), $publicKeyInfo);

        return "-----BEGIN PUBLIC KEY-----\n"
            .\chunk_split(\base64_encode($publicKeyInfo), 64, "\n")
            .'-----END PUBLIC KEY-----';
    }

    protected function mapUserToObject(array $user): Contracts\UserInterface
    {
        return new User([
            Contracts\ABNF_ID => $user['sub'] ?? null,
            Contracts\ABNF_NAME => $user[Contracts\ABNF_NAME] ?? null,
            Contracts\ABNF_NICKNAME => null,
            Contracts\ABNF_EMAIL => $user[Contracts\ABNF_EMAIL] ?? null,
            Contracts\ABNF_AVATAR => null,
        ]);
    }

    /**
     * Convert an ASN.1/DER-encoded ECDSA signature to raw R||S format (IEEE P1363).
     */
    protected function convertDerToP1363(string $der, int $partLength): string
    {
        $offset = 2;
        $dataLength = \strlen($der);

        if ($dataLength < 8 || \ord($der[0]) !== 0x30) {
            throw new Exceptions\InvalidArgumentException('Invalid DER signature.');
        }

        // Read R
        if (\ord($der[$offset]) !== 0x02) {
            throw new Exceptions\InvalidArgumentException('Invalid DER signature: expected INTEGER for R.');
        }
        $offset++;
        $rLength = \ord($der[$offset]);
        $offset++;
        $r = \substr($der, $offset, $rLength);
        $offset += $rLength;

        // Read S
        if ($offset >= $dataLength || \ord($der[$offset]) !== 0x02) {
            throw new Exceptions\InvalidArgumentException('Invalid DER signature: expected INTEGER for S.');
        }
        $offset++;
        $sLength = \ord($der[$offset]);
        $offset++;
        $s = \substr($der, $offset, $sLength);

        // Strip leading zero padding and pad to partLength
        $r = \str_pad(\ltrim($r, "\x00"), $partLength, "\x00", STR_PAD_LEFT);
        $s = \str_pad(\ltrim($s, "\x00"), $partLength, "\x00", STR_PAD_LEFT);

        return $r.$s;
    }

    private function base64UrlEncode(string $data): string
    {
        return \rtrim(\strtr(\base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        return \base64_decode(\strtr($data, '-_', '+/'));
    }

    private function encodeAsn1Length(int $length): string
    {
        if ($length < 0x80) {
            return \chr($length);
        }

        $temp = \ltrim(\pack('N', $length), "\x00");

        return \chr(0x80 | \strlen($temp)).$temp;
    }
}
