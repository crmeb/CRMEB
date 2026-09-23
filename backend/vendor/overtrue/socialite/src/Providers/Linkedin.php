<?php

namespace Overtrue\Socialite\Providers;

use JetBrains\PhpStorm\ArrayShape;
use Overtrue\Socialite\Contracts;
use Overtrue\Socialite\User;

/**
 * @see https://developer.linkedin.com/docs/oauth2 [Authenticating with OAuth 2.0]
 */
class Linkedin extends Base
{
    public const NAME = 'linkedin';

    protected array $scopes = ['r_liteprofile', 'r_emailaddress'];

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase('https://www.linkedin.com/oauth/v2/authorization');
    }

    protected function getTokenUrl(): string
    {
        return 'https://www.linkedin.com/oauth/v2/accessToken';
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
        $basicProfile = $this->getBasicProfile($token);
        $emailAddress = $this->getEmailAddress($token);

        return \array_merge($basicProfile, $emailAddress);
    }

    protected function getBasicProfile(string $token): array
    {
        $url = 'https://api.linkedin.com/v2/me?projection=(id,firstName,lastName,profilePicture(displayImage~:playableStreams))';

        $response = $this->getHttpClient()->get($url, [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
                'X-RestLi-Protocol-Version' => '2.0.0',
            ],
        ]);

        return $this->fromJsonBody($response);
    }

    protected function getEmailAddress(string $token): array
    {
        $url = 'https://api.linkedin.com/v2/emailAddress?q=members&projection=(elements*(handle~))';

        $response = $this->getHttpClient()->get($url, [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
                'X-RestLi-Protocol-Version' => '2.0.0',
            ],
        ]);

        return $this->fromJsonBody($response)['elements'][0]['handle~'] ?? [];
    }

    protected function mapUserToObject(array $user): Contracts\UserInterface
    {
        $preferredLocale = ($user['firstName']['preferredLocale']['language'] ?? null).'_'.
            ($user['firstName']['preferredLocale']['country'] ?? null);
        $firstName = $user['firstName']['localized'][$preferredLocale] ?? null;
        $lastName = $user['lastName']['localized'][$preferredLocale] ?? null;
        $name = $firstName.' '.$lastName;

        $images = $user['profilePicture']['displayImage~']['elements'] ?? [];
        $avatars = \array_filter($images, static fn ($image) => ($image['data']['com.linkedin.digitalmedia.mediaartifact.StillImage']['storageSize']['width'] ?? 0) === 100);
        $avatar = \array_shift($avatars);
        $originalAvatars = \array_filter($images, static fn ($image) => ($image['data']['com.linkedin.digitalmedia.mediaartifact.StillImage']['storageSize']['width'] ?? 0) === 800);
        $originalAvatar = \array_shift($originalAvatars);

        return new User([
            Contracts\ABNF_ID => $user[Contracts\ABNF_ID] ?? null,
            Contracts\ABNF_NICKNAME => $name,
            Contracts\ABNF_NAME => $name,
            Contracts\ABNF_EMAIL => $user['emailAddress'] ?? null,
            Contracts\ABNF_AVATAR => $avatar['identifiers']['0']['identifier'] ?? null,
            'avatar_original' => $originalAvatar['identifiers']['0']['identifier'] ?? null,
        ]);
    }
}
