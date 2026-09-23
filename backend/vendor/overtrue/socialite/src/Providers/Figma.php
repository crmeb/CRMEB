<?php

namespace Overtrue\Socialite\Providers;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Overtrue\Socialite\Contracts;
use Overtrue\Socialite\User;

/**
 * @see https://www.figma.com/developers/api#oauth2
 */
class Figma extends Base
{
    public const NAME = 'figma';

    protected string $scopeSeparator = '';

    protected array $scopes = ['file_read'];

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase('https://www.figma.com/oauth');
    }

    protected function getTokenUrl(): string
    {
        return 'https://www.figma.com/api/oauth/token';
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

    protected function getCodeFields(): array
    {
        return parent::getCodeFields() + [Contracts\RFC6749_ABNF_STATE => \md5(\uniqid('state_', true))];
    }

    protected function getUserByToken(string $token, ?array $query = []): array
    {
        $response = $this->getHttpClient()->get('https://api.figma.com/v1/me', [
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
            Contracts\ABNF_NICKNAME => $user['handle'] ?? null,
            Contracts\ABNF_NAME => $user['handle'] ?? null,
            Contracts\ABNF_EMAIL => $user[Contracts\ABNF_EMAIL] ?? null,
            Contracts\ABNF_AVATAR => $user['img_url'] ?? null,
        ]);
    }
}
