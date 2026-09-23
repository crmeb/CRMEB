<?php

namespace Overtrue\Socialite\Providers;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Overtrue\Socialite\Contracts;
use Overtrue\Socialite\Exceptions;
use Overtrue\Socialite\User;

/**
 * @see http://open.douyin.com/platform
 * @see https://open.douyin.com/platform/doc/OpenAPI-overview
 */
class DouYin extends Base
{
    public const NAME = 'douyin';

    protected string $baseUrl = 'https://open.douyin.com';

    protected array $scopes = ['user_info'];

    protected ?string $openId;

    protected function getAuthUrl(): string
    {
        return $this->buildAuthUrlFromBase($this->baseUrl.'/platform/oauth/connect/');
    }

    #[ArrayShape([
        'client_key' => 'null|string',
        Contracts\RFC6749_ABNF_REDIRECT_URI => 'null|string',
        Contracts\RFC6749_ABNF_SCOPE => 'string',
        Contracts\RFC6749_ABNF_RESPONSE_TYPE => 'string',
    ])]
    public function getCodeFields(): array
    {
        return [
            'client_key' => $this->getClientId(),
            Contracts\RFC6749_ABNF_REDIRECT_URI => $this->redirectUrl,
            Contracts\RFC6749_ABNF_SCOPE => $this->formatScopes($this->scopes, $this->scopeSeparator),
            Contracts\RFC6749_ABNF_RESPONSE_TYPE => Contracts\RFC6749_ABNF_CODE,
        ];
    }

    protected function getTokenUrl(): string
    {
        return $this->baseUrl.'/oauth/access_token/';
    }

    /**
     * @throws Exceptions\AuthorizeFailedException
     */
    public function tokenFromCode(string $code): array
    {
        $response = $this->getHttpClient()->get(
            $this->getTokenUrl(),
            [
                'query' => $this->getTokenFields($code),
            ]
        );

        $body = $this->fromJsonBody($response);

        if (empty($body['data'] ?? null) || ($body['data']['error_code'] ?? -1) != 0) {
            throw new Exceptions\AuthorizeFailedException('Invalid token response', $body);
        }

        if (empty($body['data'][Contracts\ABNF_OPEN_ID] ?? null)) {
            throw new Exceptions\AuthorizeFailedException('Authorization failed: missing open_id in token response', $body);
        }

        $this->withOpenId($body['data'][Contracts\ABNF_OPEN_ID]);

        return $this->normalizeAccessTokenResponse($body['data']);
    }

    #[ArrayShape([
        'client_key' => 'null|string',
        Contracts\RFC6749_ABNF_CLIENT_SECRET => 'null|string',
        Contracts\RFC6749_ABNF_CODE => 'string',
        Contracts\RFC6749_ABNF_GRANT_TYPE => 'string',
    ])]
    protected function getTokenFields(string $code): array
    {
        return [
            'client_key' => $this->getClientId(),
            Contracts\RFC6749_ABNF_CLIENT_SECRET => $this->getClientSecret(),
            Contracts\RFC6749_ABNF_CODE => $code,
            Contracts\RFC6749_ABNF_GRANT_TYPE => Contracts\RFC6749_ABNF_AUTHORATION_CODE,
        ];
    }

    /**
     * @throws Exceptions\InvalidArgumentException
     */
    protected function getUserByToken(string $token): array
    {
        $userUrl = $this->baseUrl.'/oauth/userinfo/';

        if (empty($this->openId)) {
            throw new Exceptions\InvalidArgumentException('please set the `open_id` before issue the API request.');
        }

        $response = $this->getHttpClient()->get(
            $userUrl,
            [
                'query' => [
                    Contracts\RFC6749_ABNF_ACCESS_TOKEN => $token,
                    Contracts\ABNF_OPEN_ID => $this->openId,
                ],
            ]
        );

        $body = $this->fromJsonBody($response);

        return $body['data'] ?? [];
    }

    #[Pure]
    protected function mapUserToObject(array $user): Contracts\UserInterface
    {
        return new User([
            Contracts\ABNF_ID => $user[Contracts\ABNF_OPEN_ID] ?? null,
            Contracts\ABNF_NAME => $user[Contracts\ABNF_NICKNAME] ?? null,
            Contracts\ABNF_NICKNAME => $user[Contracts\ABNF_NICKNAME] ?? null,
            Contracts\ABNF_AVATAR => $user[Contracts\ABNF_AVATAR] ?? null,
            Contracts\ABNF_EMAIL => $user[Contracts\ABNF_EMAIL] ?? null,
        ]);
    }

    public function withOpenId(string $openId): self
    {
        $this->openId = $openId;

        return $this;
    }
}
