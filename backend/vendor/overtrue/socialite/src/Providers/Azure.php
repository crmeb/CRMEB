<?php

namespace Overtrue\Socialite\Providers;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Overtrue\Socialite\Contracts;
use Overtrue\Socialite\User;

class Azure extends Base
{
    public const NAME = 'azure';

    protected array $scopes = ['User.Read'];

    protected string $scopeSeparator = ' ';

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase($this->getBaseUrl().'/oauth2/v2.0/authorize');
    }

    protected function getBaseUrl(): string
    {
        return 'https://login.microsoftonline.com/'.$this->config['tenant'];
    }

    protected function getTokenUrl(): string
    {
        return $this->getBaseUrl().'/oauth2/v2.0/token';
    }

    protected function getUserByToken(string $token, ?array $query = []): array
    {
        $response = $this->getHttpClient()->get(
            'https://graph.microsoft.com/v1.0/me',
            ['headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ],
            ]
        );

        return $this->fromJsonBody($response);
    }

    #[Pure]
    protected function mapUserToObject(array $user): Contracts\UserInterface
    {
        return new User([
            Contracts\ABNF_ID => $user[Contracts\ABNF_ID] ?? null,
            Contracts\ABNF_NICKNAME => null,
            Contracts\ABNF_NAME => $user['displayName'] ?? null,
            Contracts\ABNF_EMAIL => $user['userPrincipalName'] ?? null,
            Contracts\ABNF_AVATAR => null,
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
        return parent::getTokenFields($code) + [
            Contracts\RFC6749_ABNF_GRANT_TYPE => Contracts\RFC6749_ABNF_AUTHORATION_CODE,
        ];
    }
}
