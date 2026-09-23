<?php

namespace Overtrue\Socialite\Providers;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Overtrue\Socialite\Contracts;
use Overtrue\Socialite\User;

/**
 * @see https://developers.google.com/identity/protocols/OpenIDConnect [OpenID Connect]
 */
class Google extends Base
{
    public const NAME = 'google';

    protected string $scopeSeparator = ' ';

    protected array $scopes = [
        'https://www.googleapis.com/auth/userinfo.email',
        'https://www.googleapis.com/auth/userinfo.profile',
    ];

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase('https://accounts.google.com/o/oauth2/v2/auth');
    }

    protected function getTokenUrl(): string
    {
        return 'https://www.googleapis.com/oauth2/v4/token';
    }

    public function tokenFromCode(string $code): array
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'form_params' => $this->getTokenFields($code),
        ]);

        return $this->normalizeAccessTokenResponse($response->getBody());
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

    protected function getUserByToken(string $token, ?array $query = []): array
    {
        $response = $this->getHttpClient()->get('https://www.googleapis.com/userinfo/v2/me', [
            'headers' => [
                'Accept' => 'application/json',
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
            'username' => $user[Contracts\ABNF_EMAIL] ?? null,
            Contracts\ABNF_NICKNAME => $user[Contracts\ABNF_NAME] ?? null,
            Contracts\ABNF_NAME => $user[Contracts\ABNF_NAME] ?? null,
            Contracts\ABNF_EMAIL => $user[Contracts\ABNF_EMAIL] ?? null,
            Contracts\ABNF_AVATAR => $user['picture'] ?? null,
        ]);
    }
}
