<?php

namespace Overtrue\Socialite\Providers;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Overtrue\Socialite\Contracts;
use Overtrue\Socialite\Exceptions;
use Overtrue\Socialite\User;

/**
 * @see https://opendocs.alipay.com/open/289/105656
 */
class Alipay extends Base
{
    public const NAME = 'alipay';

    protected string $baseUrl = 'https://openapi.alipay.com/gateway.do';

    protected string $authUrl = 'https://openauth.alipay.com/oauth2/publicAppAuthorize.htm';

    protected array $scopes = ['auth_user'];

    protected string $apiVersion = '1.0';

    protected string $signType = 'RSA2';

    protected string $postCharset = 'UTF-8';

    protected string $format = 'json';

    protected bool $sandbox = false;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->sandbox = (bool) $this->config->get('sandbox', false);
        if ($this->sandbox) {
            $this->baseUrl = 'https://openapi.alipaydev.com/gateway.do';
            $this->authUrl = 'https://openauth.alipaydev.com/oauth2/publicAppAuthorize.htm';
        }
    }

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase($this->authUrl);
    }

    protected function getTokenUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * @throws Exceptions\BadRequestException
     */
    protected function getUserByToken(string $token): array
    {
        $params = $this->getPublicFields('alipay.user.info.share');
        $params += ['auth_token' => $token];
        $params['sign'] = $this->generateSign($params);

        $responseInstance = $this->getHttpClient()->post(
            $this->baseUrl,
            [
                'form_params' => $params,
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded;charset=utf-8',
                ],
            ]
        );

        $response = $this->fromJsonBody($responseInstance);

        if (! empty($response['error_response'] ?? null) || empty($response['alipay_user_info_share_response'] ?? [])) {
            throw new Exceptions\BadRequestException((string) $responseInstance->getBody());
        }

        return $response['alipay_user_info_share_response'];
    }

    #[Pure]
    protected function mapUserToObject(array $user): Contracts\UserInterface
    {
        return new User([
            Contracts\ABNF_ID => $user['user_id'] ?? null,
            Contracts\ABNF_NAME => $user['nick_name'] ?? null,
            Contracts\ABNF_AVATAR => $user[Contracts\ABNF_AVATAR] ?? null,
            Contracts\ABNF_EMAIL => $user[Contracts\ABNF_EMAIL] ?? null,
        ]);
    }

    /**
     * @throws Exceptions\BadRequestException
     */
    public function tokenFromCode(string $code): array
    {
        $responseInstance = $this->getHttpClient()->post(
            $this->getTokenUrl(),
            [
                'form_params' => $this->getTokenFields($code),
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded;charset=utf-8',
                ],
            ]
        );
        $response = $this->fromJsonBody($responseInstance);

        if (! empty($response['error_response'])) {
            throw new Exceptions\BadRequestException((string) $responseInstance->getBody());
        }

        if (empty($response['alipay_system_oauth_token_response'])) {
            throw new Exceptions\AuthorizeFailedException('Authorization failed: missing alipay_system_oauth_token_response in response', $response);
        }

        return $this->normalizeAccessTokenResponse($response['alipay_system_oauth_token_response']);
    }

    /**
     * @throws Exceptions\InvalidArgumentException
     */
    protected function getCodeFields(): array
    {
        if (empty($this->redirectUrl)) {
            throw new Exceptions\InvalidArgumentException('Please set the correct redirect URL refer which was on the Alipay Official Admin pannel.');
        }

        $fields = \array_merge(
            [
                Contracts\ABNF_APP_ID => $this->getConfig()->get(Contracts\RFC6749_ABNF_CLIENT_ID) ?? $this->getConfig()->get(Contracts\ABNF_APP_ID),
                Contracts\RFC6749_ABNF_SCOPE => $this->formatScopes($this->scopes, $this->scopeSeparator),
                Contracts\RFC6749_ABNF_REDIRECT_URI => $this->redirectUrl,
            ],
            $this->parameters
        );

        return $fields;
    }

    #[ArrayShape([
        Contracts\RFC6749_ABNF_CLIENT_ID => 'null|string',
        Contracts\RFC6749_ABNF_CLIENT_SECRET => 'null|string',
        Contracts\RFC6749_ABNF_CODE => 'string',
        Contracts\RFC6749_ABNF_REDIRECT_URI => 'null|string',
        Contracts\RFC6749_ABNF_GRANT_TYPE => 'string',
    ])]
    protected function getTokenFields(string $code): array
    {
        $params = $this->getPublicFields('alipay.system.oauth.token');
        $params += [
            Contracts\RFC6749_ABNF_CODE => $code,
            Contracts\RFC6749_ABNF_GRANT_TYPE => Contracts\RFC6749_ABNF_AUTHORATION_CODE,
        ];
        $params['sign'] = $this->generateSign($params);

        return $params;
    }

    #[ArrayShape([
        Contracts\ABNF_APP_ID => 'string',
        'format' => 'string',
        'charset' => 'string',
        'sign_type' => 'string',
        'method' => 'string',
        'timestamp' => 'string',
        'version' => 'string',
    ])]
    public function getPublicFields(string $method): array
    {
        return [
            Contracts\ABNF_APP_ID => $this->getConfig()->get(Contracts\RFC6749_ABNF_CLIENT_ID) ?? $this->getConfig()->get(Contracts\ABNF_APP_ID),
            'format' => $this->format,
            'charset' => $this->postCharset,
            'sign_type' => $this->signType,
            'method' => $method,
            'timestamp' => (new \DateTime('now', new \DateTimeZone('Asia/Shanghai')))->format('Y-m-d H:i:s'),
            'version' => $this->apiVersion,
        ];
    }

    /**
     * @see https://opendocs.alipay.com/open/289/105656
     */
    protected function generateSign(array $params): string
    {
        \ksort($params);

        return $this->signWithSHA256RSA($this->buildParams($params), $this->getConfig()->get('rsa_private_key'));
    }

    /**
     * @throws Exceptions\InvalidArgumentException
     */
    protected function signWithSHA256RSA(string $signContent, string $key): string
    {
        if (empty($key)) {
            throw new Exceptions\InvalidArgumentException('no RSA private key set.');
        }

        $key = "-----BEGIN RSA PRIVATE KEY-----\n".
            \chunk_split($key, 64, "\n").
            '-----END RSA PRIVATE KEY-----';

        \openssl_sign($signContent, $signValue, $key, \OPENSSL_ALGO_SHA256);

        return \base64_encode($signValue);
    }

    public static function buildParams(array $params, bool $urlencode = false, array $except = ['sign']): string
    {
        $param_str = '';
        foreach ($params as $k => $v) {
            if (\in_array($k, $except)) {
                continue;
            }
            $param_str .= $k.'=';
            $param_str .= $urlencode ? \rawurlencode($v) : $v;
            $param_str .= '&';
        }

        return \rtrim($param_str, '&');
    }
}
