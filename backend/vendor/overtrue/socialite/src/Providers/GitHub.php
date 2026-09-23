<?php

namespace Overtrue\Socialite\Providers;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Overtrue\Socialite\Contracts;
use Overtrue\Socialite\User;

class GitHub extends Base
{
    public const NAME = 'github';

    protected array $scopes = ['user:email'];

    protected string $scopeSeparator = ' ';

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase('https://github.com/login/oauth/authorize');
    }

    protected function getTokenUrl(): string
    {
        return 'https://github.com/login/oauth/access_token';
    }

    protected function getUserByToken(string $token): array
    {
        $userUrl = 'https://api.github.com/user';

        $response = $this->getHttpClient()->get(
            $userUrl,
            $this->createAuthorizationHeaders($token)
        );

        $user = $this->fromJsonBody($response);

        if (\in_array('user:email', $this->scopes) || str_contains($this->scopes[0] ?? '', 'user:email')) {
            $user[Contracts\ABNF_EMAIL] = $this->getEmailByToken($token);
        }

        return $user;
    }

    protected function getEmailByToken(string $token): string
    {
        $emailsUrl = 'https://api.github.com/user/emails';

        try {
            $response = $this->getHttpClient()->get(
                $emailsUrl,
                $this->createAuthorizationHeaders($token)
            );
        } catch (\Throwable $e) {
            return '';
        }

        foreach ($this->fromJsonBody($response) as $email) {
            if ($email['primary'] && $email['verified']) {
                return $email[Contracts\ABNF_EMAIL];
            }
        }

        return '';
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

    #[ArrayShape(['headers' => 'array'])]
    protected function createAuthorizationHeaders(string $token): array
    {
        return [
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => \sprintf('token %s', $token),
            ],
        ];
    }
}
