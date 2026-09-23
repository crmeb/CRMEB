<?php

namespace Overtrue\Socialite\Providers;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Overtrue\Socialite\Contracts;
use Overtrue\Socialite\User;

/**
 * @see https://developer.baidu.com/wiki/index.php?title=docs/oauth [OAuth 2.0 授权机制说明]
 */
class Baidu extends Base
{
    public const NAME = 'baidu';

    protected string $baseUrl = 'https://openapi.baidu.com';

    protected string $version = '2.0';

    protected array $scopes = ['basic'];

    protected string $display = 'popup';

    public function withDisplay(string $display): self
    {
        $this->display = $display;

        return $this;
    }

    public function withScopes(array $scopes): self
    {
        $this->scopes = $scopes;

        return $this;
    }

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase($this->baseUrl.'/oauth/'.$this->version.'/authorize');
    }

    protected function getCodeFields(): array
    {
        return [
            Contracts\RFC6749_ABNF_RESPONSE_TYPE => Contracts\RFC6749_ABNF_CODE,
            Contracts\RFC6749_ABNF_CLIENT_ID => $this->getClientId(),
            Contracts\RFC6749_ABNF_REDIRECT_URI => $this->redirectUrl,
            Contracts\RFC6749_ABNF_SCOPE => $this->formatScopes($this->scopes, $this->scopeSeparator),
            'display' => $this->display,
        ] + $this->parameters;
    }

    protected function getTokenUrl(): string
    {
        return $this->baseUrl.'/oauth/'.$this->version.'/token';
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

    protected function getUserByToken(string $token): array
    {
        $response = $this->getHttpClient()->get(
            $this->baseUrl.'/rest/'.$this->version.'/passport/users/getInfo',
            [
                'query' => [
                    Contracts\RFC6749_ABNF_ACCESS_TOKEN => $token,
                ],
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]
        );

        return $this->fromJsonBody($response);
    }

    #[Pure]
    protected function mapUserToObject(array $user): Contracts\UserInterface
    {
        return new User([
            Contracts\ABNF_ID => $user['userid'] ?? null,
            Contracts\ABNF_NICKNAME => $user['realname'] ?? null,
            Contracts\ABNF_NAME => $user['username'] ?? null,
            Contracts\ABNF_EMAIL => '',
            Contracts\ABNF_AVATAR => $user['portrait'] ? 'http://tb.himg.baidu.com/sys/portraitn/item/'.$user['portrait'] : null,
        ]);
    }
}
