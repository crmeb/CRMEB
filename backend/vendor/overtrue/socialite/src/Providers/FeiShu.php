<?php

namespace Overtrue\Socialite\Providers;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Overtrue\Socialite\Contracts;
use Overtrue\Socialite\Exceptions;
use Overtrue\Socialite\User;

/**
 * @see https://open.feishu.cn/document/uQjL04CN/ucDOz4yN4MjL3gzM
 */
class FeiShu extends Base
{
    public const NAME = 'feishu';

    private const APP_TICKET = 'app_ticket';

    protected string $baseUrl = 'https://open.feishu.cn/open-apis';

    protected string $expiresInKey = 'refresh_expires_in';

    protected bool $isInternalApp = false;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->isInternalApp = ($this->config->get('app_mode') ?? $this->config->get('mode')) == 'internal';
    }

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase($this->baseUrl.'/authen/v1/index');
    }

    #[ArrayShape([Contracts\RFC6749_ABNF_REDIRECT_URI => 'null|string', Contracts\ABNF_APP_ID => 'null|string'])]
    protected function getCodeFields(): array
    {
        return [
            Contracts\RFC6749_ABNF_REDIRECT_URI => $this->redirectUrl,
            Contracts\ABNF_APP_ID => $this->getClientId(),
        ];
    }

    protected function getTokenUrl(): string
    {
        return $this->baseUrl.'/authen/v1/access_token';
    }

    public function tokenFromCode(string $code): array
    {
        return $this->normalizeAccessTokenResponse($this->getTokenFromCode($code));
    }

    /**
     * @throws Exceptions\AuthorizeFailedException
     */
    protected function getTokenFromCode(string $code): array
    {
        $this->configAppAccessToken();
        $responseInstance = $this->getHttpClient()->post($this->getTokenUrl(), [
            'json' => [
                'app_access_token' => $this->config->get('app_access_token'),
                Contracts\RFC6749_ABNF_CODE => $code,
                Contracts\RFC6749_ABNF_GRANT_TYPE => Contracts\RFC6749_ABNF_AUTHORATION_CODE,
            ],
        ]);
        $response = $this->fromJsonBody($responseInstance);

        if (empty($response['data'] ?? null)) {
            throw new Exceptions\AuthorizeFailedException('Invalid token response', $response);
        }

        return $this->normalizeAccessTokenResponse($response['data']);
    }

    protected function getRefreshTokenUrl(): string
    {
        return $this->baseUrl.'/authen/v1/refresh_access_token';
    }

    /**
     * @see https://open.feishu.cn/document/uAjLw4CM/ukTMukTMukTM/reference/authen-v1/authen/refresh_access_token
     */
    public function refreshToken(string $refreshToken): array
    {
        $this->configAppAccessToken();
        $responseInstance = $this->getHttpClient()->post($this->getRefreshTokenUrl(), [
            'json' => [
                'app_access_token' => $this->config->get('app_access_token'),
                Contracts\RFC6749_ABNF_REFRESH_TOKEN => $refreshToken,
                Contracts\RFC6749_ABNF_GRANT_TYPE => Contracts\RFC6749_ABNF_REFRESH_TOKEN,
            ],
        ]);
        $response = $this->fromJsonBody($responseInstance);

        if (empty($response['data'] ?? null)) {
            throw new Exceptions\AuthorizeFailedException('Invalid token response', $response);
        }

        return $this->normalizeAccessTokenResponse($response['data']);
    }

    /**
     * @throws Exceptions\BadRequestException
     */
    protected function getUserByToken(string $token): array
    {
        $responseInstance = $this->getHttpClient()->get($this->baseUrl.'/authen/v1/user_info', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ],
            'query' => \array_filter(
                [
                    'user_access_token' => $token,
                ]
            ),
        ]);

        $response = $this->fromJsonBody($responseInstance);

        if (empty($response['data'] ?? null)) {
            throw new Exceptions\BadRequestException((string) $responseInstance->getBody());
        }

        return $response['data'];
    }

    #[Pure]
    protected function mapUserToObject(array $user): Contracts\UserInterface
    {
        return new User([
            Contracts\ABNF_ID => $user['user_id'] ?? null,
            Contracts\ABNF_NAME => $user[Contracts\ABNF_NAME] ?? null,
            Contracts\ABNF_NICKNAME => $user[Contracts\ABNF_NAME] ?? null,
            Contracts\ABNF_AVATAR => $user['avatar_url'] ?? null,
            Contracts\ABNF_EMAIL => $user[Contracts\ABNF_EMAIL] ?? null,
        ]);
    }

    public function withInternalAppMode(): self
    {
        $this->isInternalApp = true;

        return $this;
    }

    public function withDefaultMode(): self
    {
        $this->isInternalApp = false;

        return $this;
    }

    /**
     * set self::APP_TICKET in config attribute
     */
    public function withAppTicket(string $appTicket): self
    {
        $this->config->set(self::APP_TICKET, $appTicket);

        return $this;
    }

    /**
     * set app_access_token directly in config (skip automatic fetching)
     */
    public function withAppAccessToken(string $appAccessToken): self
    {
        $this->config->set('app_access_token', $appAccessToken);

        return $this;
    }

    /**
     * 设置 app_access_token 到 config 设置中
     * 应用维度授权凭证，开放平台可据此识别调用方的应用身份
     * 分内建和自建
     *
     * @throws Exceptions\FeiShu\InvalidTicketException
     * @throws Exceptions\InvalidTokenException
     */
    protected function configAppAccessToken(): self
    {
        if ($this->config->has('app_access_token')) {
            return $this;
        }

        $url = $this->baseUrl.'/auth/v3/app_access_token/';
        $params = [
            'json' => [
                Contracts\ABNF_APP_ID => $this->config->get(Contracts\RFC6749_ABNF_CLIENT_ID),
                Contracts\ABNF_APP_SECRET => $this->config->get(Contracts\RFC6749_ABNF_CLIENT_SECRET),
                self::APP_TICKET => $this->config->get(self::APP_TICKET),
            ],
        ];

        if ($this->isInternalApp) {
            $url = $this->baseUrl.'/auth/v3/app_access_token/internal/';
            $params = [
                'json' => [
                    Contracts\ABNF_APP_ID => $this->config->get(Contracts\RFC6749_ABNF_CLIENT_ID),
                    Contracts\ABNF_APP_SECRET => $this->config->get(Contracts\RFC6749_ABNF_CLIENT_SECRET),
                ],
            ];
        }

        if (! $this->isInternalApp && ! $this->config->has(self::APP_TICKET)) {
            throw new Exceptions\FeiShu\InvalidTicketException('You are using default mode, please config \'app_ticket\' first');
        }

        $responseInstance = $this->getHttpClient()->post($url, $params);
        $response = $this->fromJsonBody($responseInstance);

        if (empty($response['app_access_token'] ?? null)) {
            throw new Exceptions\InvalidTokenException('Invalid \'app_access_token\' response', (string) $responseInstance->getBody());
        }

        $this->config->set('app_access_token', $response['app_access_token']);

        return $this;
    }

    /**
     * 设置 tenant_access_token 到 config 属性中
     * 应用的企业授权凭证，开放平台据此识别调用方的应用身份和企业身份
     * 分内建和自建
     *
     * @throws Exceptions\BadRequestException
     * @throws Exceptions\AuthorizeFailedException
     */
    protected function configTenantAccessToken(): self
    {
        $url = $this->baseUrl.'/auth/v3/tenant_access_token/';
        $params = [
            'json' => [
                Contracts\ABNF_APP_ID => $this->config->get(Contracts\RFC6749_ABNF_CLIENT_ID),
                Contracts\ABNF_APP_SECRET => $this->config->get(Contracts\RFC6749_ABNF_CLIENT_SECRET),
                self::APP_TICKET => $this->config->get(self::APP_TICKET),
            ],
        ];

        if ($this->isInternalApp) {
            $url = $this->baseUrl.'/auth/v3/tenant_access_token/internal/';
            $params = [
                'json' => [
                    Contracts\ABNF_APP_ID => $this->config->get(Contracts\RFC6749_ABNF_CLIENT_ID),
                    Contracts\ABNF_APP_SECRET => $this->config->get(Contracts\RFC6749_ABNF_CLIENT_SECRET),
                ],
            ];
        }

        if (! $this->isInternalApp && ! $this->config->has(self::APP_TICKET)) {
            throw new Exceptions\BadRequestException('You are using default mode, please config \'app_ticket\' first');
        }

        $response = $this->getHttpClient()->post($url, $params);
        $response = $this->fromJsonBody($response);
        if (empty($response['tenant_access_token'])) {
            throw new Exceptions\AuthorizeFailedException('Invalid tenant_access_token response', $response);
        }

        $this->config->set('tenant_access_token', $response['tenant_access_token']);

        return $this;
    }
}
