<?php

namespace Overtrue\Socialite\Providers;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Overtrue\Socialite\Contracts;
use Overtrue\Socialite\Exceptions;
use Overtrue\Socialite\User;

/**
 * @see http://open.weibo.com/wiki/%E6%8E%88%E6%9D%83%E6%9C%BA%E5%88%B6%E8%AF%B4%E6%98%8E [OAuth 2.0 授权机制说明]
 */
class Weibo extends Base
{
    public const NAME = 'weibo';

    protected string $baseUrl = 'https://api.weibo.com';

    protected array $scopes = [Contracts\ABNF_EMAIL];

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase($this->baseUrl.'/oauth2/authorize');
    }

    protected function getTokenUrl(): string
    {
        return $this->baseUrl.'/2/oauth2/access_token';
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
        return parent::getTokenFields($code) + [
            Contracts\RFC6749_ABNF_GRANT_TYPE => Contracts\RFC6749_ABNF_AUTHORATION_CODE,
        ];
    }

    /**
     * @throws Exceptions\InvalidTokenException
     */
    protected function getUserByToken(string $token): array
    {
        $uid = $this->getTokenPayload($token)['uid'] ?? null;

        if (empty($uid)) {
            throw new Exceptions\InvalidTokenException('Invalid token.', $token);
        }

        $response = $this->getHttpClient()->get($this->baseUrl.'/2/users/show.json', [
            'query' => [
                'uid' => $uid,
                Contracts\RFC6749_ABNF_ACCESS_TOKEN => $token,
            ],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        return $this->fromJsonBody($response);
    }

    /**
     * @throws Exceptions\InvalidTokenException
     */
    protected function getTokenPayload(string $token): array
    {
        $response = $this->getHttpClient()->post($this->baseUrl.'/oauth2/get_token_info', [
            'query' => [
                Contracts\RFC6749_ABNF_ACCESS_TOKEN => $token,
            ],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        $response = $this->fromJsonBody($response);

        if (empty($response['uid'] ?? null)) {
            throw new Exceptions\InvalidTokenException(\sprintf('Invalid token %s', $token), $token);
        }

        return $response;
    }

    #[Pure]
    protected function mapUserToObject(array $user): Contracts\UserInterface
    {
        return new User([
            Contracts\ABNF_ID => $user[Contracts\ABNF_ID] ?? null,
            Contracts\ABNF_NICKNAME => $user['screen_name'] ?? null,
            Contracts\ABNF_NAME => $user[Contracts\ABNF_NAME] ?? null,
            Contracts\ABNF_EMAIL => $user[Contracts\ABNF_EMAIL] ?? null,
            Contracts\ABNF_AVATAR => $user['avatar_large'] ?? null,
        ]);
    }
}
