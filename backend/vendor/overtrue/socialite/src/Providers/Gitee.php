<?php

namespace Overtrue\Socialite\Providers;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Overtrue\Socialite\Contracts;
use Overtrue\Socialite\User;

class Gitee extends Base
{
    public const NAME = 'gitee';

    protected array $scopes = ['user_info'];

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase('https://gitee.com/oauth/authorize');
    }

    protected function getTokenUrl(): string
    {
        return 'https://gitee.com/oauth/token';
    }

    protected function getUserByToken(string $token): array
    {
        $userUrl = 'https://gitee.com/api/v5/user';
        $response = $this->getHttpClient()->get($userUrl, [
            'query' => [
                Contracts\RFC6749_ABNF_ACCESS_TOKEN => $token,
            ],
        ]);

        return $this->fromJsonBody($response);
    }

    #[Pure]
    protected function mapUserToObject(array $user): Contracts\UserInterface
    {
        return new User([
            Contracts\ABNF_ID => $user[Contracts\ABNF_ID] ?? null,
            Contracts\ABNF_NICKNAME => $user['login'] ?? null,
            Contracts\ABNF_NAME => $user[Contracts\ABNF_NAME] ?? null,
            Contracts\ABNF_EMAIL => $user[Contracts\ABNF_EMAIL] ?? null,
            Contracts\ABNF_AVATAR => $user['avatar_url'] ?? null,
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
        return [
            Contracts\RFC6749_ABNF_CLIENT_ID => $this->getClientId(),
            Contracts\RFC6749_ABNF_CLIENT_SECRET => $this->getClientSecret(),
            Contracts\RFC6749_ABNF_CODE => $code,
            Contracts\RFC6749_ABNF_REDIRECT_URI => $this->redirectUrl,
            Contracts\RFC6749_ABNF_GRANT_TYPE => Contracts\RFC6749_ABNF_AUTHORATION_CODE,
        ];
    }
}
