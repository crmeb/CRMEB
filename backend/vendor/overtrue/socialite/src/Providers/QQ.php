<?php

namespace Overtrue\Socialite\Providers;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Utils;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Overtrue\Socialite\Contracts;
use Overtrue\Socialite\Exceptions\AuthorizeFailedException;
use Overtrue\Socialite\User;

/**
 * @see http://wiki.connect.qq.com/oauth2-0%E7%AE%80%E4%BB%8B [QQ - OAuth 2.0 登录QQ]
 */
class QQ extends Base
{
    public const NAME = 'qq';

    protected string $baseUrl = 'https://graph.qq.com';

    protected array $scopes = ['get_user_info'];

    protected bool $withUnionId = false;

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase($this->baseUrl.'/oauth2.0/authorize');
    }

    protected function getTokenUrl(): string
    {
        return $this->baseUrl.'/oauth2.0/token';
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
        return parent::getTokenFields($code) + [Contracts\RFC6749_ABNF_GRANT_TYPE => Contracts\RFC6749_ABNF_AUTHORATION_CODE];
    }

    public function tokenFromCode(string $code): array
    {
        $response = $this->getHttpClient()->get($this->getTokenUrl(), [
            'query' => $this->getTokenFields($code),
        ]);

        \parse_str((string) $response->getBody(), $token);

        return $this->normalizeAccessTokenResponse($token);
    }

    public function withUnionId(): self
    {
        $this->withUnionId = true;

        return $this;
    }

    /**
     * @throws GuzzleException
     * @throws AuthorizeFailedException
     */
    protected function getUserByToken(string $token): array
    {
        $response = $this->getHttpClient()->get($this->baseUrl.'/oauth2.0/me', [
            'query' => [
                Contracts\RFC6749_ABNF_ACCESS_TOKEN => $token,
                'fmt' => 'json',
            ] + ($this->withUnionId ? ['unionid' => 1] : []),
        ]);

        $me = $this->fromJsonBody($response);

        if (empty($me['openid'])) {
            throw new AuthorizeFailedException('Authorization failed: missing openid in token response', $me);
        }

        $response = $this->getHttpClient()->get($this->baseUrl.'/user/get_user_info', [
            'query' => [
                Contracts\RFC6749_ABNF_ACCESS_TOKEN => $token,
                'fmt' => 'json',
                'openid' => $me['openid'],
                'oauth_consumer_key' => $this->getClientId(),
            ],
        ]);

        $user = $this->fromJsonBody($response);

        if (! array_key_exists('ret', $user) || $user['ret'] !== 0) {
            throw new AuthorizeFailedException('Authorize Failed: '.Utils::jsonEncode($user, \JSON_UNESCAPED_UNICODE), $user);
        }

        return $user + [
            'unionid' => $me['unionid'] ?? null,
            'openid' => $me['openid'],
        ];
    }

    #[Pure]
    protected function mapUserToObject(array $user): Contracts\UserInterface
    {
        return new User([
            Contracts\ABNF_ID => $user['openid'] ?? null,
            Contracts\ABNF_NAME => $user['nickname'] ?? null,
            Contracts\ABNF_NICKNAME => $user['nickname'] ?? null,
            Contracts\ABNF_EMAIL => $user['email'] ?? null,
            Contracts\ABNF_AVATAR => $user['figureurl_qq_2'] ?? null,
        ]);
    }
}
