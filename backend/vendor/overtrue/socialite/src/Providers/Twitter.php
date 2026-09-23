<?php

namespace Overtrue\Socialite\Providers;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Overtrue\Socialite\Contracts;
use Overtrue\Socialite\User;

/**
 * @see https://developer.twitter.com/en/docs/authentication/oauth-2-0/authorization-code
 */
class Twitter extends Base
{
    public const NAME = 'twitter';

    protected array $scopes = ['tweet.read', 'users.read'];

    protected string $scopeSeparator = ' ';

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase('https://twitter.com/i/oauth2/authorize');
    }

    protected function getTokenUrl(): string
    {
        return 'https://api.twitter.com/2/oauth2/token';
    }

    public function tokenFromCode(string $code): array
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'form_params' => $this->getTokenFields($code),
            'auth' => [$this->getClientId(), $this->getClientSecret()],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        return $this->normalizeAccessTokenResponse($response->getBody());
    }

    #[ArrayShape([
        Contracts\RFC6749_ABNF_CODE => 'string',
        Contracts\RFC6749_ABNF_REDIRECT_URI => 'null|string',
        Contracts\RFC6749_ABNF_GRANT_TYPE => 'string',
    ])]
    protected function getTokenFields(string $code): array
    {
        $fields = parent::getTokenFields($code);

        unset(
            $fields[Contracts\RFC6749_ABNF_CLIENT_ID],
            $fields[Contracts\RFC6749_ABNF_CLIENT_SECRET]
        );

        $fields[Contracts\RFC6749_ABNF_GRANT_TYPE] = Contracts\RFC6749_ABNF_AUTHORATION_CODE;

        return $fields;
    }

    protected function getUserByToken(string $token): array
    {
        $response = $this->getHttpClient()->get('https://api.twitter.com/2/users/me', [
            'query' => [
                'user.fields' => 'id,name,username,profile_image_url,description',
            ],
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        return $this->fromJsonBody($response)['data'] ?? [];
    }

    #[Pure]
    protected function mapUserToObject(array $user): Contracts\UserInterface
    {
        return new User([
            Contracts\ABNF_ID => $user['id'] ?? null,
            Contracts\ABNF_NICKNAME => $user['username'] ?? null,
            Contracts\ABNF_NAME => $user['name'] ?? null,
            Contracts\ABNF_EMAIL => $user[Contracts\ABNF_EMAIL] ?? null,
            Contracts\ABNF_AVATAR => $user['profile_image_url'] ?? null,
        ]);
    }
}
