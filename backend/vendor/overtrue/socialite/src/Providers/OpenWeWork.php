<?php

namespace Overtrue\Socialite\Providers;

use GuzzleHttp\Exception\GuzzleException;
use JetBrains\PhpStorm\Pure;
use Overtrue\Socialite\Contracts;
use Overtrue\Socialite\Exceptions;
use Overtrue\Socialite\Exceptions\AuthorizeFailedException;
use Overtrue\Socialite\User;

/**
 * @link https://open.work.weixin.qq.com/api/doc/90001/90143/91120
 */
class OpenWeWork extends Base
{
    public const NAME = 'open-wework';

    protected bool $detailed = false;

    protected bool $asQrcode = false;

    protected string $userType = 'member';

    protected string $lang = 'zh';

    protected ?string $suiteTicket = null;

    protected ?int $agentId = null;

    protected ?string $suiteAccessToken = null;

    protected string $baseUrl = 'https://qyapi.weixin.qq.com';

    public function __construct(array $config)
    {
        parent::__construct($config);

        if ($this->getConfig()->has('base_url')) {
            $this->baseUrl = $this->getConfig()->get('base_url');
        }
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

    public function withUserType(string $userType): self
    {
        $this->userType = $userType;

        return $this;
    }

    public function withLang(string $lang): self
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * @throws GuzzleException
     * @throws AuthorizeFailedException
     */
    public function userFromCode(string $code): Contracts\UserInterface
    {
        $user = $this->getUser($this->getSuiteAccessToken(), $code);

        if ($this->detailed) {
            if (empty($user['user_ticket'])) {
                throw new AuthorizeFailedException('Authorization failed: missing user_ticket in response', $user);
            }
            $user = \array_merge($user, $this->getUserByTicket($user['user_ticket']));
        }

        return $this->mapUserToObject($user)->setProvider($this)->setRaw($user);
    }

    public function withSuiteTicket(string $suiteTicket): self
    {
        $this->suiteTicket = $suiteTicket;

        return $this;
    }

    public function withSuiteAccessToken(string $suiteAccessToken): self
    {
        $this->suiteAccessToken = $suiteAccessToken;

        return $this;
    }

    /**
     * @throws Exceptions\InvalidArgumentException
     */
    public function getAuthUrl(): string
    {
        $queries = \array_filter([
            'appid' => $this->getClientId(),
            Contracts\RFC6749_ABNF_REDIRECT_URI => $this->redirectUrl,
            Contracts\RFC6749_ABNF_RESPONSE_TYPE => Contracts\RFC6749_ABNF_CODE,
            Contracts\RFC6749_ABNF_SCOPE => $this->formatScopes($this->scopes, $this->scopeSeparator),
            Contracts\RFC6749_ABNF_STATE => $this->state,
            'agentid' => $this->agentId,
        ]);

        if ($this->asQrcode) {
            $queries = array_filter([
                'appid' => $queries['appid'] ?? $this->getClientId(),
                'redirect_uri' => $queries['redirect_uri'] ?? $this->redirectUrl,
                'usertype' => $this->userType,
                'lang' => $this->lang,
                'state' => $this->state,
            ]);

            return \sprintf('https://open.work.weixin.qq.com/wwopen/sso/3rd_qrConnect?%s', http_build_query($queries));
        }

        return \sprintf('https://open.weixin.qq.com/connect/oauth2/authorize?%s#wechat_redirect', \http_build_query($queries));
    }

    /**
     * @throws Exceptions\MethodDoesNotSupportException
     */
    protected function getUserByToken(string $token): array
    {
        throw new Exceptions\MethodDoesNotSupportException('Open WeWork doesn\'t support access_token mode');
    }

    protected function getSuiteAccessToken(): string
    {
        return $this->suiteAccessToken ?? $this->suiteAccessToken = $this->requestSuiteAccessToken();
    }

    /**
     * @throws AuthorizeFailedException|GuzzleException
     */
    protected function getUser(string $token, string $code): array
    {
        $responseInstance = $this->getHttpClient()->get(
            $this->baseUrl.'/cgi-bin/service/getuserinfo3rd',
            [
                'query' => \array_filter(
                    [
                        'suite_access_token' => $token,
                        Contracts\RFC6749_ABNF_CODE => $code,
                    ]
                ),
            ]
        );

        $response = $this->fromJsonBody($responseInstance);

        if (($response['errcode'] ?? 1) > 0 || (empty($response['UserId']) && empty($response['openid']))) {
            throw new AuthorizeFailedException((string) $responseInstance->getBody(), $response);
        } elseif (empty($response['user_ticket'])) {
            $this->detailed = false;
        }

        return $response;
    }

    /**
     * @throws AuthorizeFailedException
     * @throws GuzzleException
     */
    protected function getUserByTicket(string $userTicket): array
    {
        $responseInstance = $this->getHttpClient()->post(
            $this->baseUrl.'/cgi-bin/service/auth/getuserdetail3rd',
            [
                'query' => [
                    'suite_access_token' => $this->getSuiteAccessToken(),
                ],
                'json' => [
                    'user_ticket' => $userTicket,
                ],
            ],
        );

        $response = $this->fromJsonBody($responseInstance);

        if (($response['errcode'] ?? 1) > 0 || empty($response['userid'])) {
            throw new AuthorizeFailedException((string) $responseInstance->getBody(), $response);
        }

        return $response;
    }

    #[Pure]
    protected function mapUserToObject(array $user): Contracts\UserInterface
    {
        return new User($this->detailed ? [
            Contracts\ABNF_ID => $user['userid'] ?? $user['UserId'] ?? null,
            Contracts\ABNF_NAME => $user[Contracts\ABNF_NAME] ?? null,
            Contracts\ABNF_AVATAR => $user[Contracts\ABNF_AVATAR] ?? null,
            'gender' => $user['gender'] ?? null,
            'corpid' => $user['corpid'] ?? $user['CorpId'] ?? null,
            'open_userid' => $user['open_userid'] ?? null,
            'qr_code' => $user['qr_code'] ?? null,
        ] : [
            Contracts\ABNF_ID => $user['userid'] ?? $user['UserId'] ?? $user['OpenId'] ?? $user['openid'] ?? null,
            'corpid' => $user['CorpId'] ?? null,
            'open_userid' => $user['open_userid'] ?? null,
        ]);
    }

    /**
     * @throws AuthorizeFailedException
     * @throws GuzzleException
     */
    protected function requestSuiteAccessToken(): string
    {
        $responseInstance = $this->getHttpClient()->post(
            $this->baseUrl.'/cgi-bin/service/get_suite_token',
            [
                'json' => [
                    'suite_id' => $this->config->get('suite_id') ?? $this->config->get('client_id'),
                    'suite_secret' => $this->config->get('suite_secret') ?? $this->config->get('client_secret'),
                    'suite_ticket' => $this->suiteTicket,
                ],
            ]
        );

        $response = $this->fromJsonBody($responseInstance);

        if (isset($response['errcode']) && $response['errcode'] > 0) {
            throw new AuthorizeFailedException((string) $responseInstance->getBody(), $response);
        }

        return $response['suite_access_token'];
    }

    protected function getTokenUrl(): string
    {
        return '';
    }
}
