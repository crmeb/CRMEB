<?php

namespace Overtrue\Socialite\Providers;

use JetBrains\PhpStorm\Pure;
use Overtrue\Socialite\Contracts;
use Overtrue\Socialite\User;

/**
 * @see https://developers.facebook.com/docs/graph-api [Facebook - Graph API]
 */
class Facebook extends Base
{
    public const NAME = 'facebook';

    protected string $graphUrl = 'https://graph.facebook.com';

    protected string $version = 'v3.3';

    protected array $fields = ['first_name', 'last_name', 'email', 'gender', 'verified', 'picture'];

    protected array $scopes = [];

    protected bool $popup = false;

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase('https://www.facebook.com/'.$this->version.'/dialog/oauth');
    }

    protected function getTokenUrl(): string
    {
        return $this->graphUrl.'/oauth/access_token';
    }

    public function tokenFromCode(string $code): array
    {
        $response = $this->getHttpClient()->get($this->getTokenUrl(), [
            'query' => $this->getTokenFields($code),
        ]);

        return $this->normalizeAccessTokenResponse($response->getBody());
    }

    protected function getUserByToken(string $token, ?array $query = []): array
    {
        $appSecretProof = \hash_hmac('sha256', $token, $this->getConfig()->get(Contracts\RFC6749_ABNF_CLIENT_SECRET));

        $response = $this->getHttpClient()->get($this->graphUrl.'/'.$this->version.'/me', [
            'query' => [
                Contracts\RFC6749_ABNF_ACCESS_TOKEN => $token,
                'appsecret_proof' => $appSecretProof,
                'fields' => $this->formatScopes($this->fields, $this->scopeSeparator),
            ],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        return $this->fromJsonBody($response);
    }

    #[Pure]
    protected function mapUserToObject(array $user): Contracts\UserInterface
    {
        $userId = $user[Contracts\ABNF_ID] ?? null;
        $avatarUrl = $this->graphUrl.'/'.$this->version.'/'.$userId.'/picture';

        $firstName = $user['first_name'] ?? null;
        $lastName = $user['last_name'] ?? null;

        return new User([
            Contracts\ABNF_ID => $user[Contracts\ABNF_ID] ?? null,
            Contracts\ABNF_NICKNAME => null,
            Contracts\ABNF_NAME => $firstName.' '.$lastName,
            Contracts\ABNF_EMAIL => $user[Contracts\ABNF_EMAIL] ?? null,
            Contracts\ABNF_AVATAR => $userId ? $avatarUrl.'?type=normal' : null,
            'avatar_original' => $userId ? $avatarUrl.'?width=1920' : null,
        ]);
    }

    protected function getCodeFields(): array
    {
        $fields = parent::getCodeFields();

        if ($this->popup) {
            $fields['display'] = 'popup';
        }

        return $fields;
    }

    public function fields(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    public function asPopup(): self
    {
        $this->popup = true;

        return $this;
    }
}
