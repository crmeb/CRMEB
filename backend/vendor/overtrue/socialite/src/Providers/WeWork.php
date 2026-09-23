<?php

namespace Overtrue\Socialite\Providers;

use JetBrains\PhpStorm\Pure;
use Overtrue\Socialite\Contracts;
use Overtrue\Socialite\Exceptions;
use Overtrue\Socialite\User;

/**
 * @link https://developer.work.weixin.qq.com/document/path/91022
 */
class WeWork extends Base
{
    public const NAME = 'wework';

    protected bool $detailed = false;

    protected ?int $agentId = null;

    protected ?string $apiAccessToken;

    protected bool $asQrcode = false;

    protected string $baseUrl = 'https://qyapi.weixin.qq.com';

    public function __construct(array $config)
    {
        parent::__construct($config);

        if ($this->getConfig()->has('base_url')) {
            $this->baseUrl = $this->getConfig()->get('base_url');
        }

        if ($this->getConfig()->has('agent_id')) {
            $this->agentId = $this->getConfig()->get('agent_id');
        }
    }

    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    public function userFromCode(string $code): Contracts\UserInterface
    {
        $token = $this->getApiAccessToken();
        $user = $this->getUser($token, $code);

        if ($this->detailed) {
            if (empty($user['UserId'])) {
                throw new Exceptions\AuthorizeFailedException('Authorization failed: missing UserId in user response', $user);
            }
            $userTicket = $user['user_ticket'] ?? '';
            $user = $this->getUserById($user['UserId']);
            if ($userTicket) {
                $user += $this->getUserDetail($userTicket);
            }
        }

        return $this->mapUserToObject($user)->setProvider($this)->setRaw($user);
    }

    public function withAgentId(int $agentId): self
    {
        $this->agentId = $agentId;

        return $this;
    }

    public function detailed(): self
    {
        $this->detailed = true;

        return $this;
    }

    public function asQrcode(): self
    {
        $this->asQrcode = true;

        return $this;
    }

    public function withApiAccessToken(string $apiAccessToken): self
    {
        $this->apiAccessToken = $apiAccessToken;

        return $this;
    }

    public function getAuthUrl(): string
    {
        $scopes = $this->formatScopes($this->scopes, $this->scopeSeparator);
        $queries = array_filter([
            'appid' => $this->getClientId(),
            'agentid' => $this->agentId,
            Contracts\RFC6749_ABNF_REDIRECT_URI => $this->redirectUrl,
            Contracts\RFC6749_ABNF_RESPONSE_TYPE => Contracts\RFC6749_ABNF_CODE,
            Contracts\RFC6749_ABNF_SCOPE => $scopes,
            Contracts\RFC6749_ABNF_STATE => $this->state,
        ]);

        if (! $this->agentId && (str_contains($scopes, 'snsapi_privateinfo') || $this->asQrcode)) {
            throw new Exceptions\InvalidArgumentException("agent_id is require when qrcode mode or scopes is 'snsapi_privateinfo'");
        }

        if ($this->asQrcode) {
            unset($queries[Contracts\RFC6749_ABNF_SCOPE]);
            unset($queries[Contracts\RFC6749_ABNF_RESPONSE_TYPE]);

            $queries['login_type'] = 'CorpApp';

            return \sprintf('https://login.work.weixin.qq.com/wwlogin/sso/login?%s', http_build_query($queries));
        }

        return \sprintf('https://open.weixin.qq.com/connect/oauth2/authorize?%s#wechat_redirect', \http_build_query($queries));
    }

    /**
     * @throws Exceptions\MethodDoesNotSupportException
     */
    protected function getUserByToken(string $token): array
    {
        throw new Exceptions\MethodDoesNotSupportException('WeWork doesn\'t support access_token mode');
    }

    protected function getApiAccessToken(): string
    {
        return $this->apiAccessToken ?? $this->apiAccessToken = $this->requestApiAccessToken();
    }

    protected function getUser(string $token, string $code): array
    {
        $responseInstance = $this->getHttpClient()->get(
            $this->baseUrl.'/cgi-bin/user/getuserinfo',
            [
                'query' => \array_filter(
                    [
                        Contracts\RFC6749_ABNF_ACCESS_TOKEN => $token,
                        Contracts\RFC6749_ABNF_CODE => $code,
                    ]
                ),
            ]
        );

        $response = $this->fromJsonBody($responseInstance);

        if (($response['errcode'] ?? 1) > 0 || (empty($response['UserId']) && empty($response['OpenId']))) {
            throw new Exceptions\AuthorizeFailedException((string) $responseInstance->getBody(), $response);
        } elseif (empty($response['UserId'])) {
            $this->detailed = false;
        }

        return $response;
    }

    /**
     * @throws Exceptions\AuthorizeFailedException
     */
    protected function getUserById(string $userId): array
    {
        $responseInstance = $this->getHttpClient()->post($this->baseUrl.'/cgi-bin/user/get', [
            'query' => [
                Contracts\RFC6749_ABNF_ACCESS_TOKEN => $this->getApiAccessToken(),
                'userid' => $userId,
            ],
        ]);

        $response = $this->fromJsonBody($responseInstance);

        if (($response['errcode'] ?? 1) > 0 || empty($response['userid'])) {
            throw new Exceptions\AuthorizeFailedException((string) $responseInstance->getBody(), $response);
        }

        return $response;
    }

    /**
     * @throws Exceptions\AuthorizeFailedException
     */
    protected function getUserDetail(string $userTicket): array
    {
        $responseInstance = $this->getHttpClient()->post($this->baseUrl.'/cgi-bin/auth/getuserdetail', [
            'query' => [
                Contracts\RFC6749_ABNF_ACCESS_TOKEN => $this->getApiAccessToken(),
            ],
            'json' => [
                'user_ticket' => $userTicket,
            ],
        ]);

        $response = $this->fromJsonBody($responseInstance);

        if (($response['errcode'] ?? 1) > 0) {
            throw new Exceptions\AuthorizeFailedException((string) $responseInstance->getBody(), $response);
        }

        return $response;
    }

    #[Pure]
    protected function mapUserToObject(array $user): Contracts\UserInterface
    {
        return new User($this->detailed ? [
            Contracts\ABNF_ID => $user['userid'] ?? null,
            Contracts\ABNF_NAME => $user[Contracts\ABNF_NAME] ?? null,
            Contracts\ABNF_AVATAR => $user[Contracts\ABNF_AVATAR] ?? null,
            Contracts\ABNF_EMAIL => $user[Contracts\ABNF_EMAIL] ?? null,
        ] : [
            Contracts\ABNF_ID => $user['UserId'] ?? null ?: $user['OpenId'] ?? null,
        ]);
    }

    /**
     * @throws Exceptions\AuthorizeFailedException
     */
    protected function requestApiAccessToken(): string
    {
        $responseInstance = $this->getHttpClient()->get($this->baseUrl.'/cgi-bin/gettoken', [
            'query' => \array_filter(
                [
                    'corpid' => $this->config->get('corp_id')
                        ?? $this->config->get('corpid')
                        ?? $this->config->get(Contracts\RFC6749_ABNF_CLIENT_ID),
                    'corpsecret' => $this->config->get('corp_secret')
                        ?? $this->config->get('corpsecret')
                        ?? $this->config->get(Contracts\RFC6749_ABNF_CLIENT_SECRET),
                ]
            ),
        ]);

        $response = $this->fromJsonBody($responseInstance);

        if (($response['errcode'] ?? 1) > 0) {
            throw new Exceptions\AuthorizeFailedException((string) $responseInstance->getBody(), $response);
        }

        if (empty($response[Contracts\RFC6749_ABNF_ACCESS_TOKEN])) {
            throw new Exceptions\AuthorizeFailedException('Authorization failed: missing access_token in response', $response);
        }

        return $response[Contracts\RFC6749_ABNF_ACCESS_TOKEN];
    }

    protected function getTokenUrl(): string
    {
        return '';
    }
}
