<?php

namespace Overtrue\Socialite\Providers;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Overtrue\Socialite\Contracts;
use Overtrue\Socialite\User;

/**
 * @see https://developers.line.biz/en/docs/line-login/integrate-line-login/ [Integrating LINE Login with your web app]
 */
class Line extends Base
{
    public const NAME = 'line';

    protected string $baseUrl = 'https://api.line.me/oauth2/';

    protected string $version = 'v2.1';

    protected array $scopes = ['profile'];

    protected function getAuthUrl(): string
    {
        $this->state = $this->state ?: \md5(\uniqid(Contracts\RFC6749_ABNF_STATE, true));

        return $this->buildAuthUrlFromBase('https://access.line.me/oauth2/'.$this->version.'/authorize');
    }

    protected function getTokenUrl(): string
    {
        return $this->baseUrl.$this->version.'/token';
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

    protected function getUserByToken(string $token): array
    {
        $response = $this->getHttpClient()->get(
            'https://api.line.me/v2/profile',
            [
                'headers' => [
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
            Contracts\ABNF_ID => $user['userId'] ?? null,
            Contracts\ABNF_NAME => $user['displayName'] ?? null,
            Contracts\ABNF_NICKNAME => $user['displayName'] ?? null,
            Contracts\ABNF_AVATAR => $user['pictureUrl'] ?? null,
            Contracts\ABNF_EMAIL => null,
        ]);
    }
}
