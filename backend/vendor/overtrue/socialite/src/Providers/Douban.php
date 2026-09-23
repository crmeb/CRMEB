<?php

namespace Overtrue\Socialite\Providers;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Overtrue\Socialite\Contracts;
use Overtrue\Socialite\User;

/**
 * @see http://developers.douban.com/wiki/?title=oauth2 [使用 OAuth 2.0 访问豆瓣 API]
 */
class Douban extends Base
{
    public const NAME = 'douban';

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase('https://www.douban.com/service/auth2/auth');
    }

    protected function getTokenUrl(): string
    {
        return 'https://www.douban.com/service/auth2/token';
    }

    protected function getUserByToken(string $token, ?array $query = []): array
    {
        $response = $this->getHttpClient()->get('https://api.douban.com/v2/user/~me', [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        return $this->fromJsonBody($response);
    }

    #[Pure]
    protected function mapUserToObject(array $user): Contracts\UserInterface
    {
        return new User([
            Contracts\ABNF_ID => $user[Contracts\ABNF_ID] ?? null,
            Contracts\ABNF_NICKNAME => $user[Contracts\ABNF_NAME] ?? null,
            Contracts\ABNF_NAME => $user[Contracts\ABNF_NAME] ?? null,
            Contracts\ABNF_AVATAR => $user[Contracts\ABNF_AVATAR] ?? null,
            Contracts\ABNF_EMAIL => null,
        ]);
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
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'form_params' => $this->getTokenFields($code),
        ]);

        return $this->normalizeAccessTokenResponse($response->getBody());
    }
}
